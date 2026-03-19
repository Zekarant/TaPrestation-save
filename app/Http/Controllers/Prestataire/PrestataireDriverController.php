<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\DeliveryDriver;
use App\Models\PrestataireDriverPreference;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

use App\Support\TableExistenceCache;
class PrestataireDriverController extends Controller
{
    protected function issueInternalAccessCode(DeliveryDriver $driver): string
    {
        if (!Schema::hasColumn('delivery_drivers', 'metadata')) {
            throw new \RuntimeException('Colonne metadata manquante pour stocker le code interne.');
        }

        $metadata = $this->decodeMetadata($driver->metadata);
        $code = null;
        $codeHash = null;

        // Code robuste (INT-XXXXX-XXXXX) avec vérification d'unicité en base.
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $random = strtoupper(Str::random(10));
            $candidate = 'INT-' . substr($random, 0, 5) . '-' . substr($random, 5, 5);
            $candidateHash = hash('sha256', $candidate);

            $alreadyUsed = DeliveryDriver::query()
                ->where('id', '!=', $driver->id)
                ->where('metadata->internal_access_code_hash', $candidateHash)
                ->exists();

            if (!$alreadyUsed) {
                $code = $candidate;
                $codeHash = $candidateHash;
                break;
            }
        }

        if ($code === null || $codeHash === null) {
            throw new \RuntimeException('Impossible de générer un code interne unique.');
        }

        $metadata['internal_access_code_hash'] = $codeHash;
        $metadata['internal_access_code_cipher'] = Crypt::encryptString($code);
        $metadata['internal_access_code_generated_at'] = now()->toIso8601String();
        $metadata['internal_access_enabled'] = true;
        unset($metadata['internal_access_code']); // Legacy plaintext field

        $driver->update(['metadata' => $metadata]);

        return $code;
    }

    /**
     * Créer un livreur interne sans compte prestataire (compte plateforme non requis).
     */
    public function storeInternal(Request $request)
    {
        $prestataire = auth()->user()->prestataire ?? null;

        if (!$prestataire) {
            return back()->with('error', 'Vous devez être un prestataire.');
        }

        if (!TableExistenceCache::has('delivery_drivers')) {
            return back()->with('error', 'Table des livreurs indisponible.');
        }

        if (!Schema::hasColumn('delivery_drivers', 'employer_prestataire_id')) {
            return back()->with('error', 'La gestion des livreurs internes n\'est pas activée sur cette base.');
        }
        if (!Schema::hasColumn('delivery_drivers', 'metadata')) {
            return back()->with('error', 'La colonne metadata est manquante: impossible de générer un code interne.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'vehicle_type' => 'required|in:bike,scooter,car,van,truck',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:120',
            'postal_code' => 'nullable|string|max:20',
        ]);

        try {
            $email = trim((string) ($validated['email'] ?? ''));
            if ($email === '') {
                $email = 'internal+' . now()->format('YmdHis') . '.' . Str::lower(Str::random(6)) . '@internal.taprestation.local';
            }

            if (DeliveryDriver::where('email', $email)->exists()) {
                return back()->with('error', 'Un livreur existe déjà avec cet email.')->withInput();
            }

            $payload = [
                'user_id' => null,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $email,
                'phone' => $validated['phone'],
                'vehicle_type' => $validated['vehicle_type'],
                'status' => DeliveryDriver::STATUS_OFFLINE,
                'is_available' => false,
                'is_active' => true,
                'employer_prestataire_id' => $prestataire->id,
            ];

            if (Schema::hasColumn('delivery_drivers', 'is_internal')) {
                $payload['is_internal'] = true;
            }
            if (Schema::hasColumn('delivery_drivers', 'sponsor_prestataire_id')) {
                $payload['sponsor_prestataire_id'] = null;
            }
            if (Schema::hasColumn('delivery_drivers', 'address')) {
                $payload['address'] = $validated['address'] ?? null;
            }
            if (Schema::hasColumn('delivery_drivers', 'city')) {
                $payload['city'] = $validated['city'] ?? null;
            }
            if (Schema::hasColumn('delivery_drivers', 'postal_code')) {
                $payload['postal_code'] = $validated['postal_code'] ?? null;
            }
            if (Schema::hasColumn('delivery_drivers', 'country')) {
                $payload['country'] = 'FR';
            }
            if (Schema::hasColumn('delivery_drivers', 'metadata')) {
                $payload['metadata'] = [
                    'internal_created_by_prestataire_id' => $prestataire->id,
                    'internal_created_at' => now()->toIso8601String(),
                    'no_platform_account' => true,
                ];
            }

            $driver = DeliveryDriver::create($payload);
            $accessCode = $this->issueInternalAccessCode($driver);

            return redirect()
                ->route('prestataire.drivers.show', $driver->id)
                ->with('success', 'Livreur interne créé. Aucun abonnement prestataire supplémentaire requis. Code livreur: ' . $accessCode);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->with('error', 'Création du livreur interne impossible pour le moment.')
                ->withInput();
        }
    }

    /**
     * Relier un livreur interne a un compte utilisateur existant (compte client possible).
     * Le livreur accedera ensuite a /driver/dashboard sans acces au compte prestataire employeur.
     */
    public function linkUser(Request $request, $driverId)
    {
        $prestataire = auth()->user()->prestataire ?? null;

        if (!$prestataire) {
            return back()->with('error', 'Vous devez être un prestataire.');
        }

        $request->validate([
            'user_email' => 'required|email|max:255',
        ]);

        try {
            $driver = DeliveryDriver::findOrFail($driverId);

            if ((int) ($driver->employer_prestataire_id ?? 0) !== (int) $prestataire->id) {
                return back()->with('error', 'Vous ne pouvez lier un compte qu\'a un livreur de votre equipe interne.');
            }

            $user = User::where('email', trim((string) $request->input('user_email')))->first();
            if (!$user) {
                return back()->with('error', 'Aucun compte trouvé avec cet email. Créez d\'abord un compte client standard.')
                    ->withInput();
            }

            $otherLink = DeliveryDriver::where('user_id', $user->id)
                ->where('id', '!=', $driver->id)
                ->first();
            if ($otherLink) {
                return back()->with('error', 'Ce compte est déjà lié à un autre profil livreur.');
            }

            $driver->update([
                'user_id' => $user->id,
                'email' => $driver->email ?: $user->email,
            ]);

            return back()
                ->with('success', 'Compte lié avec succès. Le livreur peut se connecter sur /driver/dashboard.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Liaison impossible pour le moment.')->withInput();
        }
    }

    /**
     * Régénérer le code d'accès d'un livreur interne.
     */
    public function regenerateInternalCode($driverId)
    {
        $prestataire = auth()->user()->prestataire ?? null;
        if (!$prestataire) {
            return back()->with('error', 'Vous devez être un prestataire.');
        }

        try {
            $driver = DeliveryDriver::findOrFail($driverId);

            if ((int) ($driver->employer_prestataire_id ?? 0) !== (int) $prestataire->id) {
                return back()->with('error', 'Ce livreur ne fait pas partie de votre équipe interne.');
            }

            $code = $this->issueInternalAccessCode($driver);
            return back()
                ->with('success', 'Nouveau code généré. Code livreur: ' . $code);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Génération du code impossible pour le moment.');
        }
    }

    /**
     * Liste des livreurs actifs pour le prestataire
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $prestataire = $user->prestataire ?? null;

        if (!$prestataire) {
            return redirect()->route('home')->with('error', 'Vous devez être un prestataire.');
        }

        $drivers = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
        $preferences = collect();
        $filter = (string) $request->query('filter', 'all');
        $search = trim((string) $request->query('search', ''));
        $stats = [
            'total' => 0,
            'available' => 0,
            'busy' => 0,
            'offline' => 0,
            'linked' => 0,
            'preferred' => 0,
            'blocked' => 0,
            'internal' => 0,
        ];

        try {
            if (class_exists(DeliveryDriver::class) && TableExistenceCache::has('delivery_drivers')) {
                if (!Schema::hasColumn('delivery_drivers', 'employer_prestataire_id')) {
                    return view('prestataire.food-delivery.drivers.index-simple', compact('drivers', 'stats', 'prestataire', 'filter', 'search', 'preferences'))
                        ->with('error', 'La base ne supporte pas encore la séparation des livreurs internes.');
                }

                $hasInternalFlag = Schema::hasColumn('delivery_drivers', 'is_internal');
                $hasAvailableColumn = Schema::hasColumn('delivery_drivers', 'is_available');
                $hasStatusColumn = Schema::hasColumn('delivery_drivers', 'status');
                $hasCityColumn = Schema::hasColumn('delivery_drivers', 'city');
                $hasPreferenceTable = class_exists(PrestataireDriverPreference::class) && TableExistenceCache::has('prestataire_driver_preferences');

                $baseScope = DeliveryDriver::query()
                    ->where('is_active', true)
                    ->where('employer_prestataire_id', $prestataire->id);

                if ($hasInternalFlag) {
                    $baseScope->where('is_internal', true);
                }

                if ($hasPreferenceTable) {
                    $preferences = PrestataireDriverPreference::query()
                        ->where('prestataire_id', $prestataire->id)
                        ->get()
                        ->keyBy('driver_id');
                }

                $preferredIds = $preferences
                    ->where('status', PrestataireDriverPreference::STATUS_PREFERRED)
                    ->keys()
                    ->map(fn ($id) => (int) $id)
                    ->values();
                $blockedIds = $preferences
                    ->where('status', PrestataireDriverPreference::STATUS_BLOCKED)
                    ->keys()
                    ->map(fn ($id) => (int) $id)
                    ->values();

                $driversQuery = (clone $baseScope)
                    ->with('user')
                    ->withCount(['activeFoodOrders as active_orders_count']);

                switch ($filter) {
                    case 'available':
                        if ($hasAvailableColumn) {
                            $driversQuery->where('is_available', true);
                        }
                        break;

                    case 'busy':
                        $driversQuery->where(function ($q) use ($hasStatusColumn) {
                            if ($hasStatusColumn) {
                                $q->where('status', DeliveryDriver::STATUS_BUSY)
                                    ->orWhereHas('activeFoodOrders');
                                return;
                            }

                            $q->whereHas('activeFoodOrders');
                        });
                        break;

                    case 'offline':
                        if ($hasStatusColumn) {
                            $driversQuery->where('status', DeliveryDriver::STATUS_OFFLINE);
                        } elseif ($hasAvailableColumn) {
                            $driversQuery->where('is_available', false);
                        }
                        break;

                    case 'linked':
                        $driversQuery->whereNotNull('user_id');
                        break;

                    case 'preferred':
                        $driversQuery->whereIn('id', $preferredIds->isNotEmpty() ? $preferredIds : [-1]);
                        break;

                    case 'blocked':
                        $driversQuery->whereIn('id', $blockedIds->isNotEmpty() ? $blockedIds : [-1]);
                        break;
                }

                if ($search !== '') {
                    $driversQuery->where(function ($q) use ($search, $hasCityColumn) {
                        $q->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ['%' . $search . '%'])
                            ->orWhere('phone', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');

                        if ($hasCityColumn) {
                            $q->orWhere('city', 'like', '%' . $search . '%');
                        }
                    });
                }

                if ($hasAvailableColumn) {
                    $driversQuery->orderByDesc('is_available');
                }

                if ($hasStatusColumn) {
                    $driversQuery->orderByRaw(
                        "CASE status
                            WHEN ? THEN 0
                            WHEN ? THEN 1
                            WHEN ? THEN 2
                            ELSE 3
                        END",
                        [
                            DeliveryDriver::STATUS_AVAILABLE,
                            DeliveryDriver::STATUS_BUSY,
                            DeliveryDriver::STATUS_OFFLINE,
                        ]
                    );
                }

                $drivers = $driversQuery
                    ->orderByDesc('active_orders_count')
                    ->orderByDesc('rating')
                    ->orderBy('first_name')
                    ->paginate(12)
                    ->withQueryString();

                $stats['total'] = (clone $baseScope)->count();
                $stats['internal'] = $stats['total'];
                $stats['linked'] = (clone $baseScope)->whereNotNull('user_id')->count();
                $stats['preferred'] = $preferredIds->count();
                $stats['blocked'] = $blockedIds->count();

                if ($hasAvailableColumn) {
                    $stats['available'] = (clone $baseScope)->where('is_available', true)->count();
                }

                if ($hasStatusColumn) {
                    $stats['busy'] = (clone $baseScope)->where('status', DeliveryDriver::STATUS_BUSY)->count();
                    $stats['offline'] = (clone $baseScope)->where('status', DeliveryDriver::STATUS_OFFLINE)->count();
                } else {
                    $stats['busy'] = (clone $baseScope)->whereHas('activeFoodOrders')->count();
                    if ($hasAvailableColumn) {
                        $stats['offline'] = max(0, $stats['total'] - $stats['available'] - $stats['busy']);
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignorer les erreurs de connexion DB
        }

        return view('prestataire.food-delivery.drivers.index-simple', compact(
            'drivers',
            'stats',
            'prestataire',
            'filter',
            'search',
            'preferences'
        ));
    }

    /**
     * Profil d'un livreur
     */
    public function show($driverId)
    {
        try {
            $prestataire = auth()->user()->prestataire ?? null;
            $driver = DeliveryDriver::with('user')->findOrFail($driverId);

            if (!$prestataire) {
                return redirect()->route('home')->with('error', 'Vous devez être un prestataire.');
            }

            $isInternalForMe = (int) ($driver->employer_prestataire_id ?? 0) === (int) $prestataire->id;
            if (!$isInternalForMe) {
                return redirect()->route('prestataire.drivers.index')
                    ->with('error', 'Ce profil n\'appartient pas à votre équipe interne.');
            }

            $internalAccessCode = $this->extractInternalAccessCode($driver);
            return view('prestataire.food-delivery.drivers.show-simple', compact('driver', 'prestataire', 'internalAccessCode'));
        } catch (\Exception $e) {
            return redirect()->route('prestataire.drivers.index')->with('error', 'Livreur non trouvé.');
        }
    }

    /**
     * Parrainer un livreur
     */
    public function sponsor($driverId)
    {
        try {
            $driver = DeliveryDriver::findOrFail($driverId);
            $prestataire = auth()->user()->prestataire ?? null;

            if (!$prestataire) {
                return back()->with('error', 'Vous devez être un prestataire.');
            }

            // Vérifier que le prestataire peut parrainer
            $validation = DeliveryDriver::validateSponsorPrestataire($prestataire->id);
            if (!$validation['valid']) {
                return back()->with('error', $validation['reason']);
            }

            // Vérifier que le livreur n'est pas déjà parrainé
            if ($driver->sponsor_prestataire_id) {
                return back()->with('error', 'Ce livreur est déjà parrainé.');
            }

            // Parrainer le livreur
            $driver->update([
                'sponsor_prestataire_id' => $prestataire->id,
                'sponsored_at' => now(),
                'daily_limit' => 5,
                'max_order_amount' => 100.00,
            ]);

            return back()->with('success', '🎉 Vous avez parrainé ' . $driver->first_name . ' ! Ses limites ont été augmentées.');
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Parrainage impossible pour le moment.');
        }
    }

    /**
     * Gérer les préférences (whitelist/blacklist) d'un livreur
     */
    public function togglePreference(Request $request, $driverId)
    {
        $prestataire = auth()->user()->prestataire ?? null;

        if (!$prestataire) {
            return back()->with('error', 'Vous devez être un prestataire.');
        }

        $request->validate([
            'type' => 'required|in:preferred,blocked,none',
        ]);

        try {
            $driver = DeliveryDriver::findOrFail($driverId);

            if ($request->type === 'none') {
                // Supprimer la préférence
                if (class_exists(\App\Models\PrestataireDriverPreference::class)) {
                    \App\Models\PrestataireDriverPreference::where('prestataire_id', $prestataire->id)
                        ->where('driver_id', $driver->id)
                        ->delete();
                }
                return back()->with('success', 'Préférence supprimée.');
            }

            if (class_exists(\App\Models\PrestataireDriverPreference::class)) {
                \App\Models\PrestataireDriverPreference::updateOrCreate(
                    ['prestataire_id' => $prestataire->id, 'driver_id' => $driver->id],
                    ['status' => $request->type]
                );
            }

            $msg = $request->type === 'preferred' ? 'Livreur ajouté aux favoris.' : 'Livreur bloqué.';
            return back()->with('success', $msg);
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Mise à jour de préférence impossible pour le moment.');
        }
    }

    /**
     * Associer un livreur a l'equipe interne du prestataire.
     */
    public function attachInternal($driverId)
    {
        $prestataire = auth()->user()->prestataire ?? null;

        if (!$prestataire) {
            return back()->with('error', 'Vous devez être un prestataire.');
        }

        if (!TableExistenceCache::has('delivery_drivers') || !Schema::hasColumn('delivery_drivers', 'employer_prestataire_id')) {
            return back()->with('error', 'Gestion des livreurs internes indisponible sur cette base.');
        }

        try {
            $driver = DeliveryDriver::findOrFail($driverId);

            if (!empty($driver->employer_prestataire_id) && (int) $driver->employer_prestataire_id !== (int) $prestataire->id) {
                return back()->with('error', 'Ce livreur est déjà dans l\'équipe interne d\'un autre prestataire.');
            }

            $payload = [
                'employer_prestataire_id' => $prestataire->id,
            ];

            if (Schema::hasColumn('delivery_drivers', 'is_internal')) {
                $payload['is_internal'] = true;
            }

            // Evite le melange externe/interne pour ce cas d'usage.
            if (Schema::hasColumn('delivery_drivers', 'sponsor_prestataire_id')) {
                $payload['sponsor_prestataire_id'] = null;
            }

            $driver->update($payload);

            return back()->with('success', 'Livreur ajouté à votre équipe interne.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Association interne impossible pour le moment.');
        }
    }

    /**
     * Retirer un livreur de l'equipe interne du prestataire.
     */
    public function detachInternal($driverId)
    {
        $prestataire = auth()->user()->prestataire ?? null;

        if (!$prestataire) {
            return back()->with('error', 'Vous devez être un prestataire.');
        }

        if (!TableExistenceCache::has('delivery_drivers') || !Schema::hasColumn('delivery_drivers', 'employer_prestataire_id')) {
            return back()->with('error', 'Gestion des livreurs internes indisponible sur cette base.');
        }

        try {
            $driver = DeliveryDriver::findOrFail($driverId);

            if ((int) ($driver->employer_prestataire_id ?? 0) !== (int) $prestataire->id) {
                return back()->with('error', 'Ce livreur ne fait pas partie de votre équipe interne.');
            }

            $payload = [
                'employer_prestataire_id' => null,
            ];

            if (Schema::hasColumn('delivery_drivers', 'is_internal')) {
                $payload['is_internal'] = false;
            }

            $driver->update($payload);

            return back()->with('success', 'Livreur retiré de votre équipe interne.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Retrait interne impossible pour le moment.');
        }
    }

    private function decodeMetadata($metadata): array
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

    private function extractInternalAccessCode(DeliveryDriver $driver): ?string
    {
        $metadata = $this->decodeMetadata($driver->metadata);
        $legacyCode = trim((string) ($metadata['internal_access_code'] ?? ''));
        if ($legacyCode !== '') {
            return $legacyCode;
        }

        $cipher = trim((string) ($metadata['internal_access_code_cipher'] ?? ''));
        if ($cipher === '') {
            return null;
        }

        try {
            return Crypt::decryptString($cipher);
        } catch (\Throwable $e) {
            return null;
        }
    }

}