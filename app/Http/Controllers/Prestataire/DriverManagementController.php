<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\DeliveryDriver;
use App\Models\DriverRating;
use App\Models\DriverPricing;
use App\Models\FoodOrder;
use App\Models\PrestataireDriverPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Support\TableExistenceCache;

class DriverManagementController extends Controller
{
    /**
     * Display list of drivers available for this prestataire
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur a un prestataire
        if (!$user || !$user->prestataire) {
            return redirect()->route('home')->with('error', 'Vous devez être un prestataire pour accéder à cette page.');
        }
        
        $prestataire = $user->prestataire;
        
        // Vérifier que les tables existent
        if (!TableExistenceCache::has('delivery_drivers')) {
            return view('prestataire.food-delivery.drivers.index', [
                'drivers' => collect(),
                'preferences' => collect(),
                'myRatings' => collect(),
                'deliveryCounts' => collect(),
                'filter' => 'all',
                'search' => '',
                'stats' => [
                    'total' => 0,
                    'worked' => 0,
                    'preferred' => 0,
                    'blocked' => 0,
                    'internal' => 0,
                ],
                'prestataire' => $prestataire,
                'error' => 'Les tables de livraison ne sont pas encore configurées. Veuillez exécuter les migrations.',
            ]);
        }
        
        // Get filter
        $filter = $request->get('filter', 'all');
        $search = $request->get('search');
        
        // Check if preference tables exist
        $hasPreferenceTable = TableExistenceCache::has('prestataire_driver_preferences');
        $hasRatingTable = TableExistenceCache::has('driver_ratings');
        $hasEmployerColumn = Schema::hasColumn('delivery_drivers', 'employer_prestataire_id');
        
        // Get driver IDs that have delivered for this prestataire
        $workedDriverIds = FoodOrder::where('prestataire_id', $prestataire->id)
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->unique();
        
        // Get all active drivers with their preferences
        $driversQuery = DeliveryDriver::where('is_active', true);
        
        // Only load pricing if relation exists
        if (TableExistenceCache::has('driver_pricing')) {
            $driversQuery->with(['pricing']);
        }
        
        // Apply filter
        switch ($filter) {
            case 'worked':
                $driversQuery->whereIn('id', $workedDriverIds);
                break;
            case 'preferred':
                if ($hasPreferenceTable) {
                    $preferredIds = PrestataireDriverPreference::where('prestataire_id', $prestataire->id)
                        ->where('status', 'preferred')
                        ->pluck('driver_id');
                    $driversQuery->whereIn('id', $preferredIds);
                }
                break;
            case 'blocked':
                if ($hasPreferenceTable) {
                    $blockedIds = PrestataireDriverPreference::where('prestataire_id', $prestataire->id)
                        ->where('status', 'blocked')
                        ->pluck('driver_id');
                    $driversQuery->whereIn('id', $blockedIds);
                }
                break;
            case 'internal':
                if ($hasEmployerColumn) {
                    $driversQuery->where('employer_prestataire_id', $prestataire->id);
                }
                break;
        }
        
        // Apply search
        if ($search) {
            $driversQuery->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $drivers = $driversQuery->orderBy('rating', 'desc')->paginate(12);
        
        // Get preferences for current prestataire (if table exists)
        $preferences = collect();
        if ($hasPreferenceTable) {
            $preferences = PrestataireDriverPreference::where('prestataire_id', $prestataire->id)
                ->get()
                ->keyBy('driver_id');
        }
        
        // Get ratings given by this prestataire (if table exists)
        $myRatings = collect();
        if ($hasRatingTable) {
            $myRatings = DriverRating::where('prestataire_id', $prestataire->id)
                ->selectRaw('driver_id, AVG(rating) as avg_rating, COUNT(*) as count')
                ->groupBy('driver_id')
                ->get()
                ->keyBy('driver_id');
        }
        
        // Get delivery counts per driver for this prestataire
        $deliveryCounts = FoodOrder::where('prestataire_id', $prestataire->id)
            ->whereNotNull('driver_id')
            ->where('status', FoodOrder::STATUS_DELIVERED)
            ->selectRaw('driver_id, COUNT(*) as count')
            ->groupBy('driver_id')
            ->pluck('count', 'driver_id');
        
        // Count stats
        $stats = [
            'total' => DeliveryDriver::where('is_active', true)->count(),
            'worked' => $workedDriverIds->count(),
            'preferred' => $hasPreferenceTable ? PrestataireDriverPreference::where('prestataire_id', $prestataire->id)
                ->where('status', 'preferred')->count() : 0,
            'blocked' => $hasPreferenceTable ? PrestataireDriverPreference::where('prestataire_id', $prestataire->id)
                ->where('status', 'blocked')->count() : 0,
            'internal' => $hasEmployerColumn ? DeliveryDriver::where('employer_prestataire_id', $prestataire->id)->count() : 0,
        ];
        
        // Check if migrations are needed
        $migrationNeeded = !$hasPreferenceTable || !$hasRatingTable;
        
        // Utiliser la version simple pour debug
        return view('prestataire.food-delivery.drivers.index-simple', compact(
            'drivers', 'preferences', 'myRatings', 'deliveryCounts', 'filter', 'search', 'stats', 'prestataire', 'migrationNeeded'
        ));
    }

    /**
     * Show driver profile
     */
    public function show(DeliveryDriver $driver)
    {
        $user = Auth::user();
        
        if (!$user || !$user->prestataire) {
            return redirect()->route('home')->with('error', 'Vous devez être un prestataire pour accéder à cette page.');
        }
        
        $prestataire = $user->prestataire;
        
        // Check if preference tables exist
        $hasPreferenceTable = TableExistenceCache::has('prestataire_driver_preferences');
        $hasRatingTable = TableExistenceCache::has('driver_ratings');
        $hasPricingTable = TableExistenceCache::has('driver_pricing');
        
        // Get preference (if table exists)
        $preference = null;
        if ($hasPreferenceTable) {
            $preference = PrestataireDriverPreference::where('prestataire_id', $prestataire->id)
                ->where('driver_id', $driver->id)
                ->first();
        }
        
        // Get ratings from this prestataire (if table exists)
        $myRatings = collect();
        if ($hasRatingTable) {
            $myRatings = DriverRating::where('prestataire_id', $prestataire->id)
                ->where('driver_id', $driver->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }
        
        // Get my delivery history with this driver
        $deliveryHistory = FoodOrder::where('prestataire_id', $prestataire->id)
            ->where('driver_id', $driver->id)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        // Calculate stats with this driver
        $stats = [
            'total_deliveries' => $deliveryHistory->count(),
            'successful' => $deliveryHistory->where('status', FoodOrder::STATUS_DELIVERED)->count(),
            'avg_delivery_time' => $deliveryHistory->where('delivered_at', '!=', null)
                ->avg(fn($o) => $o->delivered_at?->diffInMinutes($o->picked_up_at) ?? 0),
            'my_avg_rating' => $hasRatingTable ? DriverRating::getAverageForDriverFromPrestataire($driver->id, $prestataire->id) : null,
        ];
        
        // Get driver pricing (if table exists)
        $pricing = null;
        if ($hasPricingTable) {
            $pricing = DriverPricing::where('driver_id', $driver->id)->first();
        }
        
        return view('prestataire.food-delivery.drivers.show', compact(
            'driver', 'preference', 'myRatings', 'deliveryHistory', 'stats', 'pricing', 'prestataire'
        ));
    }

    /**
     * Rate a driver
     */
    public function rate(Request $request, DeliveryDriver $driver)
    {
        // Check if rating table exists
        if (!TableExistenceCache::has('driver_ratings')) {
            return back()->with('error', 'La fonctionnalité de notation n\'est pas encore configurée.');
        }
        
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'punctuality_rating' => 'nullable|integer|min:1|max:5',
            'professionalism_rating' => 'nullable|integer|min:1|max:5',
            'care_rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'food_order_id' => 'nullable|exists:food_orders,id',
            'is_public' => 'nullable|boolean',
        ]);
        
        $user = Auth::user();
        if (!$user || !$user->prestataire) {
            return back()->with('error', 'Accès non autorisé.');
        }
        $prestataire = $user->prestataire;
        
        // Check if already rated this order
        if ($request->food_order_id) {
            $exists = DriverRating::where('prestataire_id', $prestataire->id)
                ->where('driver_id', $driver->id)
                ->where('food_order_id', $request->food_order_id)
                ->exists();
            
            if ($exists) {
                return back()->with('error', 'Vous avez déjà noté ce livreur pour cette commande.');
            }
        }
        
        DriverRating::create([
            'prestataire_id' => $prestataire->id,
            'driver_id' => $driver->id,
            'food_order_id' => $request->food_order_id,
            'rating' => $request->rating,
            'punctuality_rating' => $request->punctuality_rating,
            'professionalism_rating' => $request->professionalism_rating,
            'care_rating' => $request->care_rating,
            'comment' => $request->comment,
            'is_public' => $request->boolean('is_public', false),
        ]);
        
        // Update driver's overall rating
        $this->updateDriverOverallRating($driver);
        
        return back()->with('success', 'Note enregistrée avec succès.');
    }

    /**
     * Set driver preference (whitelist/blacklist)
     */
    public function setPreference(Request $request, DeliveryDriver $driver)
    {
        // Check if preference table exists
        if (!TableExistenceCache::has('prestataire_driver_preferences')) {
            return back()->with('error', 'La fonctionnalité de préférences n\'est pas encore configurée.');
        }
        
        $request->validate([
            'status' => 'required|in:preferred,neutral,blocked',
            'notes' => 'nullable|string|max:500',
            'block_reason' => 'nullable|string|max:255',
            'priority' => 'nullable|integer|min:1|max:100',
        ]);
        
        $user = Auth::user();
        if (!$user || !$user->prestataire) {
            return back()->with('error', 'Accès non autorisé.');
        }
        $prestataire = $user->prestataire;
        
        $preference = PrestataireDriverPreference::updateOrCreate(
            [
                'prestataire_id' => $prestataire->id,
                'driver_id' => $driver->id,
            ],
            [
                'status' => $request->status,
                'notes' => $request->notes,
                'priority' => $request->priority ?? 50,
                'block_reason' => $request->status === 'blocked' ? $request->block_reason : null,
                'blocked_at' => $request->status === 'blocked' ? now() : null,
            ]
        );
        
        $messages = [
            'preferred' => 'Livreur ajouté à vos favoris.',
            'neutral' => 'Préférence réinitialisée.',
            'blocked' => 'Livreur bloqué. Il ne recevra plus vos commandes.',
        ];
        
        return back()->with('success', $messages[$request->status]);
    }

    /**
     * Quick actions (AJAX)
     */
    public function quickAction(Request $request, DeliveryDriver $driver)
    {
        $request->validate([
            'action' => 'required|in:prefer,unprefer,block,unblock',
        ]);
        
        $prestataire = Auth::user()->prestataire;
        
        $statusMap = [
            'prefer' => 'preferred',
            'unprefer' => 'neutral',
            'block' => 'blocked',
            'unblock' => 'neutral',
        ];
        
        $preference = PrestataireDriverPreference::updateOrCreate(
            [
                'prestataire_id' => $prestataire->id,
                'driver_id' => $driver->id,
            ],
            [
                'status' => $statusMap[$request->action],
                'blocked_at' => $request->action === 'block' ? now() : null,
            ]
        );
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $preference->status,
            ]);
        }
        
        return back();
    }

    /**
     * Hire a driver as internal
     */
    public function hire(Request $request, DeliveryDriver $driver)
    {
        $prestataire = Auth::user()->prestataire;
        
        // Check if driver is already employed
        if ($driver->employer_prestataire_id && $driver->employer_prestataire_id !== $prestataire->id) {
            return back()->with('error', 'Ce livreur travaille déjà pour un autre prestataire.');
        }
        
        $driver->update([
            'employer_prestataire_id' => $prestataire->id,
            'is_internal' => true,
        ]);
        
        // Automatically prefer this driver
        PrestataireDriverPreference::updateOrCreate(
            [
                'prestataire_id' => $prestataire->id,
                'driver_id' => $driver->id,
            ],
            [
                'status' => 'preferred',
                'priority' => 100,
                'notes' => 'Livreur interne',
            ]
        );
        
        return back()->with('success', 'Livreur embauché comme livreur interne.');
    }

    /**
     * Release internal driver
     */
    public function release(DeliveryDriver $driver)
    {
        $prestataire = Auth::user()->prestataire;
        
        if ($driver->employer_prestataire_id !== $prestataire->id) {
            return back()->with('error', 'Ce livreur ne fait pas partie de votre équipe.');
        }
        
        $driver->update([
            'employer_prestataire_id' => null,
            'is_internal' => false,
        ]);
        
        return back()->with('success', 'Livreur retiré de votre équipe interne.');
    }

    /**
     * Update driver's overall rating
     */
    protected function updateDriverOverallRating(DeliveryDriver $driver): void
    {
        $avgRating = DriverRating::where('driver_id', $driver->id)->avg('rating');
        
        if ($avgRating) {
            $driver->update(['rating' => round($avgRating, 2)]);
        }
    }
}
