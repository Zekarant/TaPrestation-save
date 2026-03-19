<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DeliveryDriver;
use App\Models\DriverPricing;
use App\Models\FoodOrder;
use App\Models\Prestataire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

use App\Support\TableExistenceCache;
class DriverController extends Controller
{
    private const INTERNAL_ACCESS_MAX_ATTEMPTS = 6;
    private const INTERNAL_ACCESS_DECAY_SECONDS = 120;

    // ──────────────────────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────────────────────

    protected function getDriver()
    {
        $driver = null;
        if (Auth::check()) {
            $driver = DeliveryDriver::where('user_id', Auth::id())->first();
        }

        if ($driver) {
            return $driver;
        }

        $internalDriverId = (int) session('internal_driver_id', 0);
        if ($internalDriverId <= 0) {
            return null;
        }

        $query = DeliveryDriver::where('id', $internalDriverId);
        if (Schema::hasColumn('delivery_drivers', 'is_internal')) {
            $query->where('is_internal', true);
        }
        if (Schema::hasColumn('delivery_drivers', 'is_active')) {
            $query->where('is_active', true);
        }

        return $query->first();
    }

    protected function requireDriver()
    {
        $driver = $this->getDriver();
        if (!$driver) {
            return redirect()->route('driver.register')->with('error', 'Inscription requise.');
        }
        return $driver;
    }

    protected function isInternalForPrestataire(DeliveryDriver $driver): bool
    {
        return !empty($driver->employer_prestataire_id) && (bool) ($driver->is_internal ?? false);
    }

    /**
     * Internal drivers (linked to a prestataire) are map-first and assignment-only.
     */
    protected function isInternalAssignedOnlyDriver(DeliveryDriver $driver): bool
    {
        return $this->isInternalForPrestataire($driver);
    }

    protected function hasExternalStripeAccess(DeliveryDriver $driver): bool
    {
        return !empty($driver->stripe_account_id) && (bool) ($driver->stripe_onboarding_complete ?? false);
    }

    protected function redirectMissingStripeForExternal(DeliveryDriver $driver)
    {
        if ($this->isInternalAssignedOnlyDriver($driver)) {
            return null;
        }

        $user = Auth::user();
        $prestataire = $user?->prestataire ?? ($user ? Prestataire::where('user_id', $user->id)->first() : null);
        $isPrestataireUser = $user
            ? (method_exists($user, 'isPrestataire') ? (bool) $user->isPrestataire() : ((string) ($user->role ?? '') === 'prestataire'))
            : false;
        $prestataireStripeReady = $prestataire && !empty($prestataire->stripe_account_id);

        if ($this->hasExternalStripeAccess($driver) || $prestataireStripeReady) {
            return null;
        }

        if ($isPrestataireUser && $prestataire && empty($prestataire->stripe_account_id)) {
            return redirect()->route('prestataire.payments.connect')
                ->with('error', 'Compte Stripe requis pour utiliser le mode livreur externe.');
        }

        return redirect()->route('driver.stripe.setup')
            ->with('error', 'Activez Stripe pour accéder au mode livreur externe.');
    }

    protected function resolveInternalDriverByCode(string $code): ?DeliveryDriver
    {
        if (!TableExistenceCache::has('delivery_drivers') || !Schema::hasColumn('delivery_drivers', 'metadata')) {
            return null;
        }

        $normalizedCode = strtoupper(trim($code));
        if ($normalizedCode === '') {
            return null;
        }
        $codeHash = hash('sha256', $normalizedCode);

        $query = DeliveryDriver::query()
            ->whereNotNull('employer_prestataire_id');

        if (Schema::hasColumn('delivery_drivers', 'is_internal')) {
            $query->where('is_internal', true);
        }
        if (Schema::hasColumn('delivery_drivers', 'is_active')) {
            $query->where('is_active', true);
        }

        $driver = (clone $query)
            ->where('metadata->internal_access_code_hash', $codeHash)
            ->first();

        // Legacy fallback: ancien code stocké en clair.
        if (!$driver) {
            $driver = (clone $query)
                ->where('metadata->internal_access_code', $normalizedCode)
                ->first();
        }

        if (!$driver) {
            return null;
        }

        $this->migrateLegacyInternalAccessCode($driver, $normalizedCode);

        return $this->isInternalAccessEnabled($driver) ? $driver : null;
    }

    protected function isAllowedInternalRedirectPath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if (str_starts_with($path, '/driver')) {
            return true;
        }

        return $path === '/prestataire/food/food-orders/internal-map';
    }

    protected function internalAccessThrottleKey(Request $request): string
    {
        return 'driver-internal-access|' . $request->ip();
    }

    private function isInternalAccessEnabled(DeliveryDriver $driver): bool
    {
        $metadata = $this->decodeDriverMetadata($driver->metadata);

        $enabled = $metadata['internal_access_enabled'] ?? false;
        if (is_bool($enabled)) {
            return $enabled;
        }
        if (is_numeric($enabled)) {
            return ((int) $enabled) === 1;
        }
        if (is_string($enabled)) {
            return in_array(strtolower(trim($enabled)), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    private function decodeDriverMetadata($metadata): array
    {
        if (is_array($metadata)) {
            return $metadata;
        }

        if (is_string($metadata) && $metadata !== '') {
            $decoded = json_decode($metadata, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function migrateLegacyInternalAccessCode(DeliveryDriver $driver, string $normalizedCode): void
    {
        if (!Schema::hasColumn('delivery_drivers', 'metadata')) {
            return;
        }

        $metadata = $this->decodeDriverMetadata($driver->metadata);
        $legacyCode = trim((string) ($metadata['internal_access_code'] ?? ''));
        if ($legacyCode === '') {
            return;
        }

        if ($legacyCode !== $normalizedCode) {
            return;
        }

        $metadata['internal_access_code_hash'] = hash('sha256', $normalizedCode);
        if (empty($metadata['internal_access_code_cipher'])) {
            $metadata['internal_access_code_cipher'] = Crypt::encryptString($normalizedCode);
        }
        unset($metadata['internal_access_code']);

        try {
            $driver->update(['metadata' => $metadata]);
        } catch (\Throwable $e) {
            Log::warning('Impossible de migrer le code interne legacy', [
                'driver_id' => $driver->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Stripe attend un format E.164 (ex: +33612345678).
     * Retourne null si le numéro ne peut pas être normalisé proprement.
     */
    protected function normalizePhoneForStripe(?string $phone, string $country = 'FR'): ?string
    {
        $raw = trim((string) $phone);
        if ($raw === '') {
            return null;
        }

        $clean = (string) preg_replace('/[^\d+]/', '', $raw);
        if ($clean === '') {
            return null;
        }

        if (str_starts_with($clean, '00')) {
            $clean = '+' . substr($clean, 2);
        }

        if (str_starts_with($clean, '+')) {
            $digits = '+' . preg_replace('/\D/', '', substr($clean, 1));
            return preg_match('/^\+\d{8,15}$/', $digits) ? $digits : null;
        }

        $digits = (string) preg_replace('/\D/', '', $clean);
        $country = strtoupper(trim((string) $country ?: 'FR'));

        if ($country === 'FR') {
            if (preg_match('/^33\d{9}$/', $digits)) {
                return '+' . $digits;
            }
            if (preg_match('/^0\d{9}$/', $digits)) {
                return '+33' . substr($digits, 1);
            }
            if (preg_match('/^\d{9}$/', $digits)) {
                return '+33' . $digits;
            }
            return null;
        }

        $dialMap = [
            'BE' => '32',
            'CH' => '41',
            'LU' => '352',
            'US' => '1',
            'CA' => '1',
            'GB' => '44',
            'DE' => '49',
            'ES' => '34',
            'IT' => '39',
        ];

        $dial = $dialMap[$country] ?? '';
        if ($dial !== '') {
            if (str_starts_with($digits, $dial) && preg_match('/^\d{8,15}$/', $digits)) {
                return '+' . $digits;
            }
            $local = ltrim($digits, '0');
            $candidate = '+' . $dial . $local;
            return preg_match('/^\+\d{8,15}$/', $candidate) ? $candidate : null;
        }

        $candidate = '+' . ltrim($digits, '0');
        return preg_match('/^\+\d{8,15}$/', $candidate) ? $candidate : null;
    }

    // ──────────────────────────────────────────────────────────────
    //  ACCES CODE INTERNE (SANS COMPTE PRESTATAIRE)
    // ──────────────────────────────────────────────────────────────

    public function internalAccessForm(Request $request)
    {
        $activeDriver = $this->getDriver();
        $redirectToRaw = trim((string) $request->query('redirect_to', '/prestataire/food/food-orders/internal-map'));
        $redirectPath = (string) parse_url($redirectToRaw, PHP_URL_PATH);
        $redirectTo = ($this->isAllowedInternalRedirectPath($redirectPath))
            ? $redirectPath
            : '/prestataire/food/food-orders/internal-map';
        return view('driver.internal-access', compact('activeDriver', 'redirectTo'));
    }

    public function internalAccessLogin(Request $request)
    {
        $request->validate([
            'code' => 'required|string|min:6|max:32',
            'redirect_to' => 'nullable|string|max:1000',
        ]);

        $throttleKey = $this->internalAccessThrottleKey($request);
        if (RateLimiter::tooManyAttempts($throttleKey, self::INTERNAL_ACCESS_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()
                ->with('error', "Trop de tentatives. Réessayez dans {$seconds} seconde(s).")
                ->withInput($request->except('code'));
        }

        $code = strtoupper(trim((string) $request->input('code')));

        $driver = $this->resolveInternalDriverByCode($code);

        if (!$driver) {
            RateLimiter::hit($throttleKey, self::INTERNAL_ACCESS_DECAY_SECONDS);
            return back()->with('error', 'Code invalide.')->withInput($request->except('code'));
        }

        if ($driver->isSuspended()) {
            RateLimiter::hit($throttleKey, self::INTERNAL_ACCESS_DECAY_SECONDS);
            return back()->with('error', 'Compte livreur suspendu.')->withInput($request->except('code'));
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();
        $request->session()->put('internal_driver_id', (int) $driver->id);
        $request->session()->put('internal_driver_logged_at', now()->toIso8601String());

        $redirectToRaw = trim((string) $request->input('redirect_to', ''));
        if ($redirectToRaw !== '') {
            $path = (string) parse_url($redirectToRaw, PHP_URL_PATH);
            if ($this->isAllowedInternalRedirectPath($path)) {
                return redirect()->to($path)
                    ->with('success', 'Accès livreur interne activé.');
            }
        }

        return redirect()->to('/prestataire/food/food-orders/internal-map')
            ->with('success', 'Accès livreur interne activé.');
    }

    public function internalLogout(Request $request)
    {
        $request->session()->forget(['internal_driver_id', 'internal_driver_logged_at']);
        $request->session()->regenerateToken();

        return redirect()->route('driver.internal.access')
            ->with('success', 'Déconnecté de l\'accès livreur interne.');
    }

    // ──────────────────────────────────────────────────────────────
    //  DASHBOARD
    // ──────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $driver = $this->getDriver();

        if (!$driver) {
            return redirect()->route('driver.register')
                ->with('error', 'Vous devez vous inscrire comme livreur.');
        }

        if ($this->isInternalAssignedOnlyDriver($driver)) {
            return redirect()->route('driver.map')
                ->with('info', 'Mode interne: utilisez la carte pour vos tournées assignées.');
        }

        try {
            $activeOrders = $driver->activeFoodOrders()
                ->with(['prestataire', 'client', 'items'])
                ->get();

            $allPendingOrders = DeliveryDriver::getPendingFoodOrders();
            if ($this->isInternalForPrestataire($driver)) {
                $allPendingOrders = $allPendingOrders
                    ->where('prestataire_id', (int) $driver->employer_prestataire_id)
                    ->values();
            }

            $pendingOrders = $allPendingOrders->filter(function ($order) use ($driver) {
                $check = $driver->canAcceptOrder($order->total ?? 0);
                return $check['allowed'];
            });

            $todayDelivered = $driver->foodOrders()
                ->whereDate('updated_at', today())
                ->where('delivery_status', FoodOrder::DELIVERY_STATUS_DELIVERED);

            $todayStats = [
                'completed'    => (clone $todayDelivered)->count(),
                'earnings'     => (float) (clone $todayDelivered)->sum('driver_commission'),
                'pending'      => $activeOrders->count(),
                'total_km'     => (float) (clone $todayDelivered)->sum('delivery_distance'),
                'avg_time'     => (int) (clone $todayDelivered)->avg('estimated_delivery_time'),
                'rating_today' => (float) ($driver->rating ?? 0),
            ];

            $streak          = $this->calculateStreak($driver);
            $surgeMultiplier = $this->getCurrentSurgeMultiplier();

        } catch (\Exception $e) {
            Log::error('Driver dashboard error: ' . $e->getMessage(), [
                'driver_id' => $driver->id,
                'trace'     => $e->getTraceAsString(),
            ]);
            $activeOrders    = collect();
            $pendingOrders   = collect();
            $todayStats      = ['completed' => 0, 'earnings' => 0, 'pending' => 0, 'total_km' => 0, 'avg_time' => 0, 'rating_today' => 0];
            $streak          = 0;
            $surgeMultiplier = 1.0;
        }

        return view('driver.dashboard', compact(
            'driver', 'activeOrders', 'pendingOrders', 'todayStats', 'streak', 'surgeMultiplier'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    //  CARTE GOOGLE MAPS
    // ──────────────────────────────────────────────────────────────

    public function routeMap()
    {
        $internalSessionDriverId = (int) session('internal_driver_id', 0);
        if ($internalSessionDriverId > 0) {
            // Séparation stricte: une session code interne ne doit jamais ouvrir la carte externe.
            return redirect()->route('prestataire.food-orders.internal-map');
        }

        $driver = $this->getDriver();
        if (!$driver) {
            $activeDriver = null;
            $redirectTo = route('driver.map');
            return view('driver.internal-access', compact('activeDriver', 'redirectTo'))
                ->with('info', 'Entrez votre code pour ouvrir la carte des tournées.');
        }

        $internalOnlyMode = $this->isInternalAssignedOnlyDriver($driver);
        if ($internalOnlyMode) {
            // Séparation stricte: les livreurs internes utilisent uniquement la carte interne dédiée.
            return redirect()->route('prestataire.food-orders.internal-map');
        }

        $stripeRedirect = $this->redirectMissingStripeForExternal($driver);
        if ($stripeRedirect) {
            return $stripeRedirect;
        }

        $activeOrders = $driver->activeFoodOrders()
            ->with(['prestataire', 'client', 'items'])
            ->get();

        $pendingNearby = collect();
        if (!$internalOnlyMode && $driver->current_lat && $driver->current_lng) {
            $pendingNearbyQuery = FoodOrder::where('delivery_type', FoodOrder::DELIVERY_DELIVERY)
                ->whereNull('driver_id')
                ->whereIn('status', [FoodOrder::STATUS_ACCEPTED, FoodOrder::STATUS_PREPARING, FoodOrder::STATUS_READY])
                ->where('delivery_status', FoodOrder::DELIVERY_STATUS_PENDING)
                ->with(['prestataire']);

            $pendingNearby = $pendingNearbyQuery
                ->get()
                ->filter(function ($order) use ($driver) {
                    $dist = $this->calculateDistance(
                        $driver->current_lat, $driver->current_lng,
                        $order->prestataire->latitude ?? 0, $order->prestataire->longitude ?? 0
                    );
                    $order->distance_from_driver = $dist;
                    return $dist <= config('delivery.matching.search_radius', 5);
                })
                ->sortBy('distance_from_driver')
                ->take(10);
        }

        $waypoints = [];
        foreach ($activeOrders as $order) {
            if ($order->delivery_status === FoodOrder::DELIVERY_STATUS_ASSIGNED) {
                $waypoints[] = [
                    'type'     => 'pickup',
                    'order_id' => $order->id,
                    'lat'      => (float) ($order->prestataire->latitude ?? 0),
                    'lng'      => (float) ($order->prestataire->longitude ?? 0),
                    'name'     => $order->prestataire->company_name ?? 'Restaurant',
                    'address'  => $order->prestataire->address ?? '',
                    'phone'    => $order->prestataire->phone ?? '',
                    'status'   => $order->delivery_status,
                ];
            }
            $waypoints[] = [
                'type'     => 'delivery',
                'order_id' => $order->id,
                'lat'      => (float) ($order->delivery_lat ?? 0),
                'lng'      => (float) ($order->delivery_lng ?? 0),
                'name'     => $order->client->name ?? 'Client',
                'address'  => $order->delivery_address ?? '',
                'phone'    => $order->client->phone ?? '',
                'status'   => $order->delivery_status,
            ];
        }

        // Today's stats for the map overlay
        $todayDelivered = $driver->foodOrders()
            ->whereDate('updated_at', today())
            ->where('delivery_status', FoodOrder::DELIVERY_STATUS_DELIVERED);

        $todayStats = [
            'completed' => (clone $todayDelivered)->count(),
            'earnings'  => (float) (clone $todayDelivered)->sum('driver_commission'),
            'total_km'  => (float) (clone $todayDelivered)->sum('delivery_distance'),
            'avg_time'  => (int) (clone $todayDelivered)->avg('estimated_delivery_time'),
        ];

        // Surge multiplier
        $surgeMultiplier = $this->getCurrentSurgeMultiplier();

        $googleMapsKey = config('services.google_maps.key', env('GOOGLE_MAPS_API_KEY', ''));

        return view('driver.map', compact(
            'driver', 'activeOrders', 'pendingNearby', 'waypoints',
            'googleMapsKey', 'todayStats', 'surgeMultiplier', 'internalOnlyMode'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    //  NAVIGATION GPS
    // ──────────────────────────────────────────────────────────────

    public function navigate(FoodOrder $foodOrder)
    {
        $driver = $this->getDriver();
        if (!$driver) return redirect()->route('driver.register');

        if ($foodOrder->driver_id !== $driver->id) {
            return redirect()->route('driver.dashboard')->with('error', 'Commande non assignée.');
        }

        if ($foodOrder->delivery_status === FoodOrder::DELIVERY_STATUS_ASSIGNED) {
            $waypoint = [
                'type'    => 'pickup',
                'lat'     => (float) ($foodOrder->prestataire->latitude ?? 0),
                'lng'     => (float) ($foodOrder->prestataire->longitude ?? 0),
                'address' => $foodOrder->prestataire->address ?? $foodOrder->prestataire->company_name ?? 'Restaurant',
                'name'    => $foodOrder->prestataire->company_name ?? 'Restaurant',
            ];
        } else {
            $waypoint = [
                'type'    => 'delivery',
                'lat'     => (float) ($foodOrder->delivery_lat ?? 0),
                'lng'     => (float) ($foodOrder->delivery_lng ?? 0),
                'address' => $foodOrder->delivery_address ?? 'Client',
                'name'    => $foodOrder->client->name ?? 'Client',
            ];
        }

        $googleMapsKey = config('services.google_maps.key', env('GOOGLE_MAPS_API_KEY', ''));

        return view('driver.navigate', compact('driver', 'foodOrder', 'waypoint', 'googleMapsKey'));
    }

    // ──────────────────────────────────────────────────────────────
    //  LIVRAISONS — LISTE + DÉTAIL
    // ──────────────────────────────────────────────────────────────

    public function myDeliveries(Request $request)
    {
        $driver = $this->getDriver();
        if (!$driver) return redirect()->route('driver.register');

        $status = $request->get('status', 'all');
        $period = $request->get('period', 'all');

        $query = $driver->foodOrders()->with(['prestataire', 'client', 'items']);

        if ($status !== 'all') $query->where('delivery_status', $status);

        if ($period === 'today')     $query->whereDate('created_at', today());
        elseif ($period === 'week')  $query->where('created_at', '>=', now()->subDays(7));
        elseif ($period === 'month') $query->where('created_at', '>=', now()->startOfMonth());

        $deliveries = $query->orderBy('created_at', 'desc')->paginate(20);

        $summaryQuery = $driver->foodOrders()->where('delivery_status', FoodOrder::DELIVERY_STATUS_DELIVERED);
        if ($period === 'today')     $summaryQuery->whereDate('updated_at', today());
        elseif ($period === 'week')  $summaryQuery->where('updated_at', '>=', now()->subDays(7));
        elseif ($period === 'month') $summaryQuery->where('updated_at', '>=', now()->startOfMonth());

        $summary = [
            'total'    => $summaryQuery->count(),
            'earnings' => (float) $summaryQuery->sum('driver_commission'),
            'distance' => (float) $summaryQuery->sum('delivery_distance'),
            'avg_time' => (int)   $summaryQuery->avg('estimated_delivery_time'),
        ];

        return view('driver.deliveries.index', compact('driver', 'deliveries', 'status', 'period', 'summary'));
    }

    public function showDelivery(FoodOrder $foodOrder)
    {
        $driver = $this->getDriver();
        if (!$driver) return redirect()->route('driver.register');

        if ($foodOrder->driver_id !== $driver->id) {
            abort(403, 'Accès non autorisé');
        }

        $foodOrder->load(['prestataire', 'client', 'items.foodProduct']);

        $timeline = $this->buildDeliveryTimeline($foodOrder);

        return view('driver.deliveries.show', compact('driver', 'foodOrder', 'timeline'));
    }

    // ──────────────────────────────────────────────────────────────
    //  ACTIONS LIVRAISON
    // ──────────────────────────────────────────────────────────────

    public function acceptDelivery(FoodOrder $foodOrder)
    {
        try {
            $driver = $this->getDriver();
            if (!$driver) return response()->json(['success' => false, 'message' => 'Non trouvé'], 403);

            if ($this->isInternalAssignedOnlyDriver($driver)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce compte interne ne peut prendre que les commandes assignées par le prestataire.',
                ], 403);
            }

            $canAccept = $driver->canAcceptOrder($foodOrder->total ?? 0);
            if (!$canAccept['allowed'])
                return response()->json(['success' => false, 'message' => $canAccept['reason']], 400);

            if (!$driver->isAvailableForDelivery())
                return response()->json(['success' => false, 'message' => 'Trop de livraisons en cours'], 400);

            if ($foodOrder->driver_id !== null)
                return response()->json(['success' => false, 'message' => 'Commande déjà acceptée'], 400);

            if ($foodOrder->delivery_type !== FoodOrder::DELIVERY_DELIVERY)
                return response()->json(['success' => false, 'message' => 'Pas une livraison'], 400);

            if ($this->isInternalForPrestataire($driver)
                && (int) $foodOrder->prestataire_id !== (int) $driver->employer_prestataire_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce compte livreur interne ne peut accepter que les commandes de son prestataire.',
                ], 403);
            }

            if (!in_array($foodOrder->status, [FoodOrder::STATUS_ACCEPTED, FoodOrder::STATUS_PREPARING, FoodOrder::STATUS_READY], true))
                return response()->json(['success' => false, 'message' => 'Commande pas encore prête'], 400);

            if (($foodOrder->payment_method ?? 'card') !== 'cash' && $foodOrder->payment_status === FoodOrder::PAYMENT_PENDING)
                return response()->json(['success' => false, 'message' => 'Attente paiement'], 400);

            $distance      = $this->calculateDistance(
                $foodOrder->prestataire->latitude ?? 0, $foodOrder->prestataire->longitude ?? 0,
                $foodOrder->delivery_lat ?? 0, $foodOrder->delivery_lng ?? 0
            );
            $estimatedTime = $this->estimateDeliveryTime($distance, $driver->vehicle_type);
            $commission    = $this->calculateCommission($foodOrder, $driver);

            $surge = $this->getCurrentSurgeMultiplier();
            if ($surge > 1.0) $commission = round($commission * $surge, 2);

            $foodOrder->update([
                'driver_id'               => $driver->id,
                'driver_accepted_at'      => now(),
                'delivery_status'         => FoodOrder::DELIVERY_STATUS_ASSIGNED,
                'delivery_distance'       => $distance,
                'estimated_delivery_time' => $estimatedTime,
                'driver_commission'       => $commission,
            ]);

            if ($foodOrder->payment_status === 'pending_capture' && $foodOrder->status === FoodOrder::STATUS_ACCEPTED) {
                $foodOrder->capturePayment();
            }

            try { $foodOrder->prestataire->user->notify(new \App\Notifications\DriverAcceptedDelivery($foodOrder, $driver)); } catch (\Exception $e) {
                \Log::warning('Failed to notify prestataire of accepted delivery for order #' . $foodOrder->id . ': ' . $e->getMessage());
            }
            try {
                $foodOrder->loadMissing('client');
                $foodOrder->client?->notify(new \App\Notifications\FoodOrderDriverAssigned($foodOrder));
            } catch (\Exception $e) {
                \Log::warning('Failed to notify client of driver assignment for order #' . $foodOrder->id . ': ' . $e->getMessage());
            }

            $maxConcurrentBeforeBusy = $this->isInternalForPrestataire($driver) ? 5 : 3;
            if ($driver->activeFoodOrders()->count() >= $maxConcurrentBeforeBusy) {
                $driver->setStatus(DeliveryDriver::STATUS_BUSY);
            }

            return response()->json([
                'success'    => true,
                'message'    => 'Livraison acceptée !',
                'commission' => $commission,
                'distance'   => $distance,
                'eta'        => $estimatedTime,
                'surge'      => $surge,
                'redirect'   => route('driver.deliveries.show', $foodOrder),
            ]);
        } catch (\Exception $e) {
            Log::error('acceptDelivery: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Impossible de traiter cette livraison pour le moment.',
            ], 500);
        }
    }

    public function pickUp(FoodOrder $foodOrder)
    {
        $driver = $this->getDriver();
        if (!$driver || $foodOrder->driver_id !== $driver->id)
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);

        $foodOrder->markAsPickedUp();

        try {
            $foodOrder->loadMissing(['client', 'prestataire.user']);
            $foodOrder->client?->notify(new \App\Notifications\FoodOrderPickedUp($foodOrder));
            $foodOrder->prestataire?->user?->notify(new \App\Notifications\FoodOrderPickedUpForPrestataire($foodOrder));
        } catch (\Exception $e) {
            \Log::warning('Failed to send pickup notifications for order #' . $foodOrder->id . ': ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Commande récupérée !']);
    }

    public function startDelivery(FoodOrder $foodOrder)
    {
        $driver = $this->getDriver();
        if (!$driver || $foodOrder->driver_id !== $driver->id)
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);

        $foodOrder->markAsInTransit();

        try {
            $foodOrder->loadMissing(['client', 'prestataire.user']);
            $foodOrder->client?->notify(new \App\Notifications\FoodOrderInTransit($foodOrder));
            $foodOrder->prestataire?->user?->notify(new \App\Notifications\FoodOrderInTransitForPrestataire($foodOrder));
        } catch (\Exception $e) {
            \Log::warning('Failed to send in-transit notifications for order #' . $foodOrder->id . ': ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'En route !']);
    }

    public function deliver(FoodOrder $foodOrder, Request $request)
    {
        try {
            $driver = $this->getDriver();
            if (!$driver || $foodOrder->driver_id !== $driver->id)
                return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);

            // Validation stricte par code pour les livraisons.
            if (($foodOrder->delivery_type ?? '') === FoodOrder::DELIVERY_DELIVERY) {
                if (empty($foodOrder->delivery_code)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Code de livraison introuvable. Demandez une régénération.',
                    ], 422);
                }
                if (!$request->filled('delivery_code')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Code client requis pour valider la livraison.',
                    ], 422);
                }
                if ((string) $request->input('delivery_code') !== (string) $foodOrder->delivery_code) {
                    return response()->json(['success' => false, 'message' => 'Code incorrect'], 400);
                }
            }

            if (!$foodOrder->code_verified_at) {
                try { $foodOrder->processPayouts(); }
                catch (\Exception $e) {
                    Log::error("processPayouts #{$foodOrder->id}: " . $e->getMessage());
                    $foodOrder->update([
                        'delivery_status' => FoodOrder::DELIVERY_STATUS_DELIVERED,
                        'status' => FoodOrder::STATUS_DELIVERED,
                        'delivered_at' => now(), 'code_verified_at' => now(),
                    ]);
                }
            } else {
                $foodOrder->update([
                    'delivery_status' => FoodOrder::DELIVERY_STATUS_DELIVERED,
                    'status' => FoodOrder::STATUS_DELIVERED,
                    'delivered_at' => now(),
                ]);
            }

            try {
                $foodOrder->loadMissing(['client', 'prestataire.user']);
                $foodOrder->client?->notify(new \App\Notifications\FoodOrderDelivered($foodOrder));
                $foodOrder->prestataire?->user?->notify(new \App\Notifications\FoodOrderDeliveredForPrestataire($foodOrder));
            } catch (\Exception $e) {
                \Log::warning('Failed to send delivery notifications for order #' . $foodOrder->id . ': ' . $e->getMessage());
            }

            $deliveryTime = 0;
            if ($foodOrder->picked_up_at) {
                $deliveryTime = max(0, min(120, abs(now()->diffInMinutes(Carbon::parse($foodOrder->picked_up_at)))));
            }
            $driver->recordDeliveryCompletion($foodOrder->driver_commission ?? 0, $deliveryTime);

            if (method_exists($driver, 'incrementProbationDelivery'))  $driver->incrementProbationDelivery();
            if (method_exists($driver, 'checkAutoSuspension'))        $driver->checkAutoSuspension();

            if ($driver->activeFoodOrders()->count() === 0)
                $driver->setStatus(DeliveryDriver::STATUS_AVAILABLE);

            return response()->json([
                'success'  => true,
                'message'  => 'Livraison terminée !',
                'earnings' => $foodOrder->driver_commission,
            ]);
        } catch (\Exception $e) {
            Log::error('deliver: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur'], 500);
        }
    }

    public function reportProblem(FoodOrder $foodOrder, Request $request)
    {
        $driver = $this->getDriver();
        if (!$driver || $foodOrder->driver_id !== $driver->id)
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);

        $request->validate(['reason' => 'required|string|max:500']);

        $foodOrder->update([
            'delivery_status' => FoodOrder::DELIVERY_STATUS_FAILED,
            'driver_notes'    => $request->reason,
        ]);
        $driver->recordDeliveryFailure();

        return response()->json(['success' => true, 'message' => 'Problème signalé']);
    }

    // ──────────────────────────────────────────────────────────────
    //  GPS & DISPONIBILITÉ
    // ──────────────────────────────────────────────────────────────

    public function updateLocation(Request $request)
    {
        $driver = $this->getDriver();
        if (!$driver) return response()->json(['success' => false], 403);

        $request->validate(['lat' => 'required|numeric', 'lng' => 'required|numeric']);
        $driver->updateLocation($request->lat, $request->lng);

        $eta = null;
        $activeOrder = $driver->activeFoodOrders()->latest()->first();
        if ($activeOrder) {
            $destLat = $activeOrder->delivery_status === FoodOrder::DELIVERY_STATUS_ASSIGNED
                ? ($activeOrder->prestataire->latitude ?? 0) : ($activeOrder->delivery_lat ?? 0);
            $destLng = $activeOrder->delivery_status === FoodOrder::DELIVERY_STATUS_ASSIGNED
                ? ($activeOrder->prestataire->longitude ?? 0) : ($activeOrder->delivery_lng ?? 0);
            $dist = $this->calculateDistance($request->lat, $request->lng, $destLat, $destLng);
            $eta  = $this->estimateDeliveryTime($dist, $driver->vehicle_type);
        }

        return response()->json(['success' => true, 'eta' => $eta]);
    }

    public function toggleAvailability()
    {
        $driver = $this->getDriver();
        if (!$driver) return response()->json(['success' => false], 403);

        $newStatus = $driver->status === DeliveryDriver::STATUS_AVAILABLE
            ? DeliveryDriver::STATUS_OFFLINE : DeliveryDriver::STATUS_AVAILABLE;

        $driver->update(['status' => $newStatus, 'is_available' => $newStatus === DeliveryDriver::STATUS_AVAILABLE]);

        return response()->json([
            'success' => true, 'status' => $newStatus,
            'message' => $newStatus === DeliveryDriver::STATUS_AVAILABLE ? 'En ligne' : 'Hors ligne',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  OPTIMISATION DE PARCOURS
    // ──────────────────────────────────────────────────────────────

    public function optimizeRoute(Request $request)
    {
        $driver = $this->getDriver();
        if (!$driver) return response()->json(['success' => false], 403);

        $waypoints = $request->input('waypoints', []);
        if (count($waypoints) < 2) return response()->json(['success' => false, 'message' => 'Min 2 pts']);

        $start = [$driver->current_lng, $driver->current_lat];
        $coordinates = [$start];
        foreach ($waypoints as $wp) $coordinates[] = [$wp['lng'], $wp['lat']];

        try {
            $response = Http::withHeaders([
                'Authorization' => config('services.openrouteservice.key'),
                'Content-Type'  => 'application/json',
            ])->post('https://api.openrouteservice.org/v2/directions/driving-car/geojson', [
                'coordinates' => $coordinates, 'instructions' => true, 'language' => 'fr',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success'  => true, 'route' => $data,
                    'distance' => $data['features'][0]['properties']['summary']['distance'] ?? 0,
                    'duration' => $data['features'][0]['properties']['summary']['duration'] ?? 0,
                ]);
            }
        } catch (\Exception $e) { Log::error('ORS: ' . $e->getMessage()); }

        return response()->json(['success' => true, 'waypoints' => $waypoints, 'optimized' => false]);
    }

    // ──────────────────────────────────────────────────────────────
    //  STATISTIQUES AVANCÉES
    // ──────────────────────────────────────────────────────────────

    public function stats(Request $request)
    {
        $driver = $this->getDriver();
        if (!$driver) return redirect()->route('driver.register');
        if ($this->isInternalAssignedOnlyDriver($driver)) {
            return redirect()->route('driver.map')
                ->with('info', 'Mode interne: statistiques avancées indisponibles.');
        }

        $period = $request->get('period', 'week');

        $weeklyStats = $driver->foodOrders()
            ->where('delivery_status', FoodOrder::DELIVERY_STATUS_DELIVERED)
            ->where('updated_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(updated_at) as date, COUNT(*) as count, SUM(driver_commission) as earnings, SUM(delivery_distance) as distance, AVG(estimated_delivery_time) as avg_time')
            ->groupBy('date')->orderBy('date')->get();

        $monthlyStats = $driver->foodOrders()
            ->where('delivery_status', FoodOrder::DELIVERY_STATUS_DELIVERED)
            ->where('updated_at', '>=', now()->startOfMonth())
            ->selectRaw('COUNT(*) as total, SUM(driver_commission) as earnings, SUM(delivery_distance) as distance, AVG(estimated_delivery_time) as avg_time')
            ->first();

        $allTimeStats = [
            'total_deliveries' => $driver->completed_deliveries ?? 0,
            'total_earnings'   => (float) ($driver->total_earnings ?? 0),
            'avg_rating'       => (float) ($driver->rating ?? 0),
            'failed'           => $driver->failed_deliveries ?? 0,
            'success_rate'     => $driver->completed_deliveries > 0
                ? round(($driver->completed_deliveries / max(1, $driver->completed_deliveries + ($driver->failed_deliveries ?? 0))) * 100) : 0,
        ];

        $topRestaurants = $driver->foodOrders()
            ->where('delivery_status', FoodOrder::DELIVERY_STATUS_DELIVERED)
            ->with('prestataire')
            ->selectRaw('prestataire_id, COUNT(*) as count, SUM(driver_commission) as earnings')
            ->groupBy('prestataire_id')->orderByDesc('count')->limit(5)->get();

        $peakHours = $driver->foodOrders()
            ->where('delivery_status', FoodOrder::DELIVERY_STATUS_DELIVERED)
            ->where('updated_at', '>=', now()->subDays(30))
            ->selectRaw('HOUR(updated_at) as hour, COUNT(*) as count, SUM(driver_commission) as earnings')
            ->groupBy('hour')->orderByDesc('count')->get();

        return view('driver.stats', compact('driver', 'weeklyStats', 'monthlyStats', 'allTimeStats', 'topRestaurants', 'peakHours', 'period'));
    }

    // ──────────────────────────────────────────────────────────────
    //  TARIFS & PRICING
    // ──────────────────────────────────────────────────────────────

    public function showTarifs()
    {
        $driver = $this->getDriver();
        if (!$driver) return redirect()->route('driver.register');
        if ($this->isInternalAssignedOnlyDriver($driver)) {
            return redirect()->route('driver.map')
                ->with('info', 'Mode interne: page tarifs indisponible.');
        }

        $tarifs = [
            'base_fee'       => config('delivery.driver.base_fee', 2.00),
            'pickup_per_km'  => config('delivery.driver.pickup_per_km', 0.50),
            'dropoff_per_km' => config('delivery.driver.dropoff_per_km', 0.80),
            'min_earning'    => config('delivery.driver.min_earning', 3.00),
            'max_earning'    => config('delivery.driver.max_earning', 20.00),
            'platform_fee'   => config('delivery.platform_fee', 0.05),
            'surge'          => config('delivery.surge', []),
            'batch'          => config('delivery.batch', []),
        ];

        $simulations = [];
        foreach ([1, 2, 3, 5, 7, 10] as $km) {
            $earning = $tarifs['base_fee'] + ($tarifs['pickup_per_km'] * min($km, 2)) + ($tarifs['dropoff_per_km'] * $km);
            $simulations[] = ['km' => $km, 'earning' => max($tarifs['min_earning'], min($tarifs['max_earning'], round($earning, 2)))];
        }

        $myAvgEarning = $driver->foodOrders()
            ->where('delivery_status', FoodOrder::DELIVERY_STATUS_DELIVERED)
            ->where('updated_at', '>=', now()->subDays(30))
            ->avg('driver_commission') ?? 0;

        $myAvgDistance = $driver->foodOrders()
            ->where('delivery_status', FoodOrder::DELIVERY_STATUS_DELIVERED)
            ->where('updated_at', '>=', now()->subDays(30))
            ->avg('delivery_distance') ?? 0;

        return view('driver.tarifs', compact('driver', 'tarifs', 'simulations', 'myAvgEarning', 'myAvgDistance'));
    }

    // ──────────────────────────────────────────────────────────────
    //  INSCRIPTION
    // ──────────────────────────────────────────────────────────────

    public function registerForm()
    {
        if ($this->getDriver()) return redirect()->route('driver.dashboard');
        return view('driver.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'driver_mode'    => 'required|in:stripe,internal_code,create_stripe',
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'email'          => 'required|email|max:255',
            'birth_date'     => 'required|date|before:-18 years',
            'address'        => 'nullable|string|max:500',
            'city'           => 'nullable|string|max:100',
            'postal_code'    => 'nullable|string|max:10',
            'vehicle_type'   => 'required|in:bike,scooter,car,van',
            'vehicle_plate'  => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:50',
            'current_lat'    => 'nullable|numeric',
            'current_lng'    => 'nullable|numeric',
            'working_hours'  => 'nullable|string',
            'max_radius'     => 'nullable|integer|min:1|max:50',
            'sponsor_code'   => 'nullable|string|max:100',
            'internal_access_code' => 'nullable|string|min:6|max:32',
            'terms'          => 'required|accepted',
        ], ['birth_date.before' => 'Minimum 18 ans.', 'terms.accepted' => 'Conditions requises.']);

        $driverMode = (string) $request->input('driver_mode', 'stripe');

        if (function_exists('prestataire_stripe_connect_enabled') && !prestataire_stripe_connect_enabled() && $driverMode !== 'internal_code') {
            return back()
                ->withInput()
                ->withErrors(['driver_mode' => 'Les paiements Stripe livreur sont désactivés. Utilisez un code prestataire.']);
        }

        if ($driverMode === 'internal_code') {
            $request->validate([
                'internal_access_code' => 'required|string|min:6|max:32',
            ]);
        } else {
            $request->validate([
                'address'     => 'required|string|max:500',
                'city'        => 'required|string|max:100',
                'postal_code' => 'required|string|max:10',
            ]);
        }

        $existing = DeliveryDriver::where('user_id', Auth::id())->first();
        if ($existing) {
            return redirect()->route($this->isInternalAssignedOnlyDriver($existing) ? 'driver.map' : 'driver.dashboard')
                ->with('info', 'Votre profil livreur existe déjà.');
        }

        $workingHours = [];
        if ($request->working_hours) {
            try {
                $workingHours = json_decode($request->working_hours, true) ?? [];
            } catch (\Exception $e) {
                $workingHours = [];
            }
        }

        // Mode interne: saisie d'un code prestataire => rattachement à un livreur interne existant
        if ($driverMode === 'internal_code') {
            $internalDriver = $this->resolveInternalDriverByCode((string) $request->input('internal_access_code'));

            if (!$internalDriver) {
                return back()->withErrors([
                    'internal_access_code' => 'Code prestataire invalide ou expiré.',
                ])->withInput();
            }

            if ($internalDriver->isSuspended()) {
                return back()->withErrors([
                    'internal_access_code' => 'Ce profil livreur interne est suspendu.',
                ])->withInput();
            }

            if (!empty($internalDriver->user_id) && (int) $internalDriver->user_id !== (int) Auth::id()) {
                return back()->withErrors([
                    'internal_access_code' => 'Ce code est déjà utilisé par un autre compte.',
                ])->withInput();
            }

            $targetEmail = strtolower(trim((string) $request->input('email')));
            $emailUsedByAnotherDriver = DeliveryDriver::where('email', $targetEmail)
                ->where('id', '!=', $internalDriver->id)
                ->exists();

            $metadata = is_array($internalDriver->metadata) ? $internalDriver->metadata : [];
            $metadata['internal_access_code_used_at'] = now()->toIso8601String();
            $metadata['internal_access_code_used_by_user_id'] = Auth::id();
            $metadata['onboarding_mode'] = 'internal_code';
            $metadata['map_only_access'] = true;
            $metadata['max_radius'] = (int) ($request->input('max_radius', $metadata['max_radius'] ?? 5));
            $metadata['working_hours'] = $workingHours;

            $payload = [
                'user_id' => Auth::id(),
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'phone' => $request->input('phone'),
                'vehicle_type' => $request->input('vehicle_type'),
                'vehicle_plate' => $request->input('vehicle_plate'),
                'license_number' => $request->input('license_number'),
                'current_lat' => $request->input('current_lat'),
                'current_lng' => $request->input('current_lng'),
                'last_location_update' => $request->filled('current_lat') ? now() : $internalDriver->last_location_update,
                'working_hours' => $workingHours,
                'status' => DeliveryDriver::STATUS_OFFLINE,
                'is_available' => false,
                'is_active' => true,
                'metadata' => $metadata,
            ];

            if (!$emailUsedByAnotherDriver) {
                $payload['email'] = $targetEmail;
            }
            if (Schema::hasColumn('delivery_drivers', 'birth_date')) {
                $payload['birth_date'] = $request->input('birth_date');
            }
            if (Schema::hasColumn('delivery_drivers', 'address')) {
                $payload['address'] = $request->filled('address') ? $request->input('address') : $internalDriver->address;
            }
            if (Schema::hasColumn('delivery_drivers', 'city')) {
                $payload['city'] = $request->filled('city') ? $request->input('city') : $internalDriver->city;
            }
            if (Schema::hasColumn('delivery_drivers', 'postal_code')) {
                $payload['postal_code'] = $request->filled('postal_code') ? $request->input('postal_code') : $internalDriver->postal_code;
            }
            if (Schema::hasColumn('delivery_drivers', 'country') && empty($internalDriver->country)) {
                $payload['country'] = 'FR';
            }

            $internalDriver->update($payload);

            $user = Auth::user();
            if ($user && !$user->phone && $request->filled('phone')) {
                $user->forceFill(['phone' => $request->input('phone')])->save();
            }

            return redirect()->route('driver.map')
                ->with('success', 'Accès livreur interne activé.')
                ->with('info', 'Vous avez accès à la carte et aux tournées qui vous sont assignées.');
        }

        $existingByEmail = DeliveryDriver::where('email', $request->email)->first();
        if ($existingByEmail) {
            if ($this->isInternalAssignedOnlyDriver($existingByEmail)) {
                return back()->withErrors([
                    'email' => 'Ce profil interne existe déjà. Utilisez le code prestataire pour l’activer.',
                ])->withInput();
            }

            $oldUser = User::find($existingByEmail->user_id);
            if (!$oldUser || $oldUser->deleted_at !== null) {
                $existingByEmail->update(['user_id' => Auth::id()]);
                return redirect()->route('driver.dashboard')->with('success', 'Profil restauré !');
            }
            return back()->withErrors(['email' => 'Email déjà utilisé.'])->withInput();
        }

        $sponsorPrestataireId = null;
        $dailyLimit = 3; $maxOrderAmount = 50.00; $sponsoredAt = null;

        if ($request->sponsor_code) {
            $prestataire = Prestataire::where('company_name', 'LIKE', '%' . $request->sponsor_code . '%')
                ->orWhere('id', $request->sponsor_code)->first();
            if ($prestataire) {
                $validation = DeliveryDriver::validateSponsorPrestataire($prestataire->id);
                if ($validation['valid']) {
                    $sponsorPrestataireId = $prestataire->id;
                    $dailyLimit = 5; $maxOrderAmount = 100.00; $sponsoredAt = now();
                }
            }
        }

        $driver = DeliveryDriver::create([
            'user_id' => Auth::id(), 'first_name' => $request->first_name, 'last_name' => $request->last_name,
            'email' => $request->email, 'phone' => $request->phone, 'address' => $request->address,
            'city' => $request->city, 'postal_code' => $request->postal_code, 'country' => 'FR',
            'birth_date' => $request->birth_date, 'vehicle_type' => $request->vehicle_type,
            'vehicle_plate' => $request->vehicle_plate, 'license_number' => $request->license_number,
            'current_lat' => $request->current_lat, 'current_lng' => $request->current_lng,
            'last_location_update' => $request->current_lat ? now() : null,
            'working_hours' => $workingHours, 'status' => DeliveryDriver::STATUS_OFFLINE,
            'is_available' => false, 'is_active' => true, 'commission_rate' => 85,
            'sponsor_prestataire_id' => $sponsorPrestataireId,
            'sponsor_code' => DeliveryDriver::generateSponsorCode(),
            'sponsored_at' => $sponsoredAt, 'trust_level' => DeliveryDriver::TRUST_PROBATION,
            'daily_limit' => $dailyLimit, 'max_order_amount' => $maxOrderAmount,
            'probation_deliveries_count' => 0,
            'metadata' => [
                'birth_date' => $request->birth_date, 'max_radius' => $request->max_radius ?? 5,
                'has_insurance' => $request->has('has_insurance'),
                'registered_at' => now()->toIso8601String(), 'sponsor_code_used' => $request->sponsor_code,
                'onboarding_mode' => $driverMode,
            ],
        ]);

        $user = Auth::user();
        if ($user && !$user->phone && $request->phone) $user->forceFill(['phone' => $request->phone])->save();

        return redirect()->route('driver.stripe.setup')
            ->with('success', $driverMode === 'create_stripe'
                ? 'Compte créé. Configurez Stripe pour activer les paiements.'
                : 'Compte livreur créé. Configurez Stripe pour recevoir vos paiements.');
    }

    // ──────────────────────────────────────────────────────────────
    //  STRIPE CONNECT
    // ──────────────────────────────────────────────────────────────

    public function stripeSetup()
    {
        if (function_exists('prestataire_stripe_connect_enabled') && !prestataire_stripe_connect_enabled()) {
            return redirect()->route('driver.register')
                ->with('info', 'La configuration Stripe livreur est désactivée pour le moment.');
        }

        $driver = $this->getDriver();
        if (!$driver) return redirect()->route('driver.register');
        if ($this->isInternalAssignedOnlyDriver($driver)) {
            return redirect()->route('driver.map')
                ->with('info', 'Mode interne: Stripe non requis pour vos tournées assignées.');
        }

        $user        = Auth::user();
        $prestataire = $user->prestataire ?? Prestataire::where('user_id', $user->id)->first();
        $hasExistingStripeFromPrestataire = $prestataire && !empty($prestataire->stripe_account_id) && !$driver->stripe_account_id;
        $isInternalDriver = !empty($driver->employer_prestataire_id) || (bool) ($driver->is_internal ?? false);

        return view('driver.stripe-connect', compact('driver', 'hasExistingStripeFromPrestataire', 'isInternalDriver'));
    }

    public function stripeConnect()
    {
        if (function_exists('prestataire_stripe_connect_enabled') && !prestataire_stripe_connect_enabled()) {
            return redirect()->route('driver.register')
                ->with('info', 'La configuration Stripe livreur est désactivée pour le moment.');
        }

        $driver = $this->getDriver();
        if (!$driver) return redirect()->route('driver.register');
        if ($this->isInternalAssignedOnlyDriver($driver)) {
            return redirect()->route('driver.map')
                ->with('info', 'Mode interne: configuration Stripe non disponible.');
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            if ($driver->stripe_account_id) {
                $accountLink = \Stripe\AccountLink::create([
                    'account' => $driver->stripe_account_id, 'refresh_url' => route('driver.stripe.setup'),
                    'return_url' => route('driver.stripe.callback'), 'type' => 'account_onboarding',
                ]);
                return redirect($accountLink->url);
            }

            $user = Auth::user();
            $prestataire = $user->prestataire ?? Prestataire::where('user_id', $user->id)->first();

            if ($prestataire && !empty($prestataire->stripe_account_id)) {
                try {
                    $existingAccount = \Stripe\Account::retrieve($prestataire->stripe_account_id);
                    if ($existingAccount?->id) {
                        $isComplete = $existingAccount->details_submitted && $existingAccount->charges_enabled;
                        $driver->update(['stripe_account_id' => $prestataire->stripe_account_id, 'stripe_onboarding_complete' => $isComplete]);
                        if ($isComplete) return redirect()->route('driver.stripe.setup')->with('success', 'Compte Stripe synchronisé !');
                        $accountLink = \Stripe\AccountLink::create([
                            'account' => $prestataire->stripe_account_id, 'refresh_url' => route('driver.stripe.setup'),
                            'return_url' => route('driver.stripe.callback'), 'type' => 'account_onboarding',
                        ]);
                        return redirect($accountLink->url);
                    }
                } catch (\Exception $e) { Log::warning('Sync Stripe: ' . $e->getMessage()); }
            }

            $stripePhone = $this->normalizePhoneForStripe(
                $driver->phone ?: optional(Auth::user())->phone,
                (string) ($driver->country ?? 'FR')
            );

            $individualData = [
                'first_name' => $driver->first_name,
                'last_name' => $driver->last_name,
                'email' => $driver->email,
                'dob' => $driver->birth_date ? [
                    'day' => $driver->birth_date->day,
                    'month' => $driver->birth_date->month,
                    'year' => $driver->birth_date->year,
                ] : null,
                'address' => [
                    'line1' => $driver->address,
                    'city' => $driver->city,
                    'postal_code' => $driver->postal_code,
                    'country' => $driver->country ?? 'FR',
                ],
            ];
            if (!empty($stripePhone)) {
                $individualData['phone'] = $stripePhone;
            }

            $accountPayload = [
                'type' => 'express',
                'country' => $driver->country ?? 'FR',
                'email' => $driver->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'business_type' => 'individual',
                'individual' => $individualData,
                'business_profile' => ['mcc' => '4215', 'product_description' => 'Livreur TaPrestation'],
                'metadata' => ['driver_id' => (string) $driver->id, 'user_id' => (string) $driver->user_id],
            ];

            try {
                $account = \Stripe\Account::create($accountPayload);
            } catch (\Exception $phoneError) {
                $msg = strtolower((string) $phoneError->getMessage());
                $phoneRejected = str_contains($msg, 'phone') || str_contains($msg, 'num');
                if (!$phoneRejected || empty($accountPayload['individual']['phone'])) {
                    throw $phoneError;
                }

                unset($accountPayload['individual']['phone']);
                \Log::warning('Stripe Connect driver: retry sans téléphone après rejet du format', [
                    'driver_id' => $driver->id,
                    'phone_raw' => $driver->phone,
                    'error' => $phoneError->getMessage(),
                ]);

                $account = \Stripe\Account::create($accountPayload);
            }

            $driver->update(['stripe_account_id' => $account->id]);

            $accountLink = \Stripe\AccountLink::create([
                'account' => $account->id, 'refresh_url' => route('driver.stripe.setup'),
                'return_url' => route('driver.stripe.callback'), 'type' => 'account_onboarding',
            ]);
            return redirect($accountLink->url);
        } catch (\Exception $e) {
            Log::error('Stripe Connect: ' . $e->getMessage());
            return redirect()->route('driver.stripe.setup')->with('error', 'La connexion Stripe est temporairement indisponible.');
        }
    }

    public function stripeCallback()
    {
        $driver = $this->getDriver();
        if (!$driver || !$driver->stripe_account_id)
            return redirect()->route('driver.stripe.setup')->with('error', 'Compte Stripe non trouvé.');

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $account = \Stripe\Account::retrieve($driver->stripe_account_id);
            $isComplete = $account->details_submitted && $account->charges_enabled;
            $driver->update(['stripe_onboarding_complete' => $isComplete]);

            return $isComplete
                ? redirect()->route('driver.dashboard')->with('success', '🎉 Stripe configuré !')
                : redirect()->route('driver.stripe.setup')->with('warning', 'Configuration incomplète.');
        } catch (\Exception $e) {
            return redirect()->route('driver.stripe.setup')->with('error', 'Erreur Stripe.');
        }
    }

    public function stripeDashboard()
    {
        if (function_exists('prestataire_stripe_connect_enabled') && !prestataire_stripe_connect_enabled()) {
            return redirect()->route('driver.register')
                ->with('info', 'La configuration Stripe livreur est désactivée pour le moment.');
        }

        $driver = $this->getDriver();
        if (!$driver || !$driver->stripe_account_id)
            return redirect()->route('driver.stripe.setup')->with('error', 'Configurez Stripe d\'abord.');
        if ($this->isInternalAssignedOnlyDriver($driver)) {
            return redirect()->route('driver.map')
                ->with('info', 'Mode interne: tableau Stripe non disponible.');
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            return redirect(\Stripe\Account::createLoginLink($driver->stripe_account_id)->url);
        } catch (\Exception $e) {
            return redirect()->route('driver.stripe.setup')->with('error', 'Accès Stripe impossible.');
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  MÉTHODES UTILITAIRES PRIVÉES
    // ──────────────────────────────────────────────────────────────

    protected function calculateDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return round($earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }

    protected function estimateDeliveryTime(float $distance, string $vehicleType): int
    {
        $speeds = ['bike' => 15, 'scooter' => 25, 'car' => 35, 'van' => 30];
        return (int) ceil(($distance / ($speeds[$vehicleType] ?? 25)) * 60) + 5;
    }

    protected function calculateCommission(FoodOrder $order, DeliveryDriver $driver): float
    {
        $deliveryFee = (float) ($order->delivery_fee ?? 0);
        if ($deliveryFee <= 0) return 0;

        $stripeFees = 0;
        if (($order->payment_method ?? 'card') !== 'cash') {
            $stripeFees = class_exists('\App\Services\CommissionService')
                ? \App\Services\CommissionService::stripeFeesAmount($deliveryFee)
                : round($deliveryFee * 0.029 + 0.25, 2);
        }
        return max(2.00, round($deliveryFee - $stripeFees, 2));
    }

    protected function getCurrentSurgeMultiplier(): float
    {
        if (!config('delivery.surge.enabled', false)) return 1.0;
        $hour = (int) now()->format('H');
        $multiplier = 1.0;
        foreach (config('delivery.surge.peak_hours', []) as $peak) {
            if ($hour >= $peak['start'] && $hour < $peak['end'])
                $multiplier = max($multiplier, $peak['multiplier']);
        }
        return $multiplier;
    }

    protected function calculateStreak(DeliveryDriver $driver): int
    {
        $date = today(); $streak = 0;
        for ($i = 0; $i < 30; $i++) {
            $count = $driver->foodOrders()->whereDate('updated_at', $date)
                ->where('delivery_status', FoodOrder::DELIVERY_STATUS_DELIVERED)->count();
            if ($count > 0) { $streak++; $date = $date->subDay(); } else break;
        }
        return $streak;
    }

    protected function buildDeliveryTimeline(FoodOrder $order): array
    {
        $t = [];
        if ($order->created_at)         $t[] = ['time' => $order->created_at,                             'label' => 'Commande passée',  'icon' => '📝', 'status' => 'done'];
        if ($order->driver_accepted_at) $t[] = ['time' => Carbon::parse($order->driver_accepted_at),      'label' => 'Acceptée',         'icon' => '✅', 'status' => 'done'];
        if ($order->picked_up_at)       $t[] = ['time' => Carbon::parse($order->picked_up_at),            'label' => 'Récupérée',        'icon' => '📦', 'status' => 'done'];
        if ($order->in_transit_at ?? null) $t[] = ['time' => Carbon::parse($order->in_transit_at),         'label' => 'En route',         'icon' => '🚗', 'status' => 'done'];
        if ($order->delivered_at)       $t[] = ['time' => Carbon::parse($order->delivered_at),            'label' => 'Livrée',           'icon' => '🎉', 'status' => 'done'];
        if ($order->delivery_status === FoodOrder::DELIVERY_STATUS_FAILED)
                                         $t[] = ['time' => $order->updated_at,                             'label' => 'Problème',         'icon' => '⚠️', 'status' => 'failed'];
        return $t;
    }
}
