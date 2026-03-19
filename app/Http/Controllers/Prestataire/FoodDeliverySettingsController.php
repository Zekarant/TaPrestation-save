<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Prestataire;
use App\Models\DeliveryDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class FoodDeliverySettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Afficher les paramètres de livraison
     */
    public function index()
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            return redirect()->route('prestataire.register')
                ->with('error', 'Vous devez d\'abord créer votre profil prestataire.');
        }

        // Vérifier si les colonnes food existent, sinon utiliser des valeurs par défaut
        if (!Schema::hasColumn('prestataires', 'food_delivery_enabled')) {
            // Les colonnes n'existent pas encore, on passe des valeurs par défaut
            $prestataire->food_delivery_enabled = false;
            $prestataire->food_pickup_enabled = true;
            $prestataire->food_delivery_radius_km = 5;
            $prestataire->food_delivery_base_fee = 3.00;
            $prestataire->food_delivery_fee_per_km = 0.50;
            $prestataire->food_min_order_delivery = null;
            $prestataire->food_min_order_pickup = null;
            $prestataire->food_free_delivery_above = null;
            $prestataire->food_estimated_prep_time = 30;
            $prestataire->food_delivery_schedule = [];
            $prestataire->food_delivery_instructions = null;
        }

        $prestataire->food_delivery_schedule = $this->normalizeDeliverySchedule($prestataire->food_delivery_schedule ?? []);

        // Vérifier la disponibilité des livreurs externes (minimum 4 dans 10km)
        $externalDriversCheck = DeliveryDriver::checkExternalDriversAvailability($prestataire);

        return view('prestataire.food-delivery.settings', compact('prestataire', 'externalDriversCheck'));
    }

    /**
     * Mettre à jour les paramètres de livraison
     */
    public function update(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            return redirect()->route('prestataire.register')
                ->with('error', 'Vous devez d\'abord créer votre profil prestataire.');
        }

        // Vérifier si les colonnes existent
        if (!Schema::hasColumn('prestataires', 'food_delivery_enabled')) {
            return back()->withErrors(['error' => 'Les paramètres de livraison ne sont pas encore disponibles. Veuillez contacter l\'administrateur.']);
        }

        // Convertir les cases à cocher d'abord
        $foodDeliveryEnabled = $request->has('food_delivery_enabled');
        $foodPickupEnabled = $request->has('food_pickup_enabled');
        $deliveryMode = $request->input('delivery_mode', 'both');

        // Au moins un mode doit être actif
        if (!$foodDeliveryEnabled && !$foodPickupEnabled) {
            return back()->withErrors(['mode' => 'Vous devez activer au moins un mode (livraison ou retrait).'])->withInput();
        }

        // Vérifier la disponibilité des livreurs externes si mode external ou both
        if ($foodDeliveryEnabled && in_array($deliveryMode, ['external', 'both'])) {
            $externalDriversCheck = DeliveryDriver::checkExternalDriversAvailability($prestataire);
            if (!$externalDriversCheck['available']) {
                $errorMsg = "Vous ne pouvez pas activer les livreurs externes : seulement {$externalDriversCheck['count']} livreur(s) externe(s) disponible(s) dans un rayon de {$externalDriversCheck['radius_km']}km. Minimum requis : {$externalDriversCheck['required']}.";
                if ($externalDriversCheck['reason'] === 'no_coordinates') {
                    $errorMsg = "Impossible de vérifier les livreurs externes : votre adresse n'est pas géolocalisée. Veuillez mettre à jour votre profil.";
                }
                return back()->withErrors(['delivery_mode' => $errorMsg])->withInput();
            }
        }

        // Validation conditionnelle - les paramètres de livraison ne sont requis que si interne ou both
        $rules = [
            'food_delivery_instructions' => 'nullable|string|max:1000',
            'delivery_schedule' => 'nullable|array',
            'delivery_mode' => 'nullable|in:internal,external,both',
            'auto_assign_drivers' => 'boolean',
            'min_driver_rating' => 'nullable|numeric|min:0|max:5',
        ];

        // Si livraison activée ET mode interne ou both, on valide les paramètres de tarification
        if ($foodDeliveryEnabled && in_array($deliveryMode, ['internal', 'both'])) {
            $rules['food_delivery_radius_km'] = 'required|integer|min:1|max:50';
            $rules['food_delivery_base_fee'] = 'required|numeric|min:0|max:50';
            $rules['food_delivery_fee_per_km'] = 'required|numeric|min:0|max:5';
            $rules['food_min_order_delivery'] = 'nullable|numeric|min:0';
            $rules['food_free_delivery_above'] = 'nullable|numeric|min:0';
            $rules['food_estimated_prep_time'] = 'required|integer|min:5|max:180';
        } else {
            $rules['food_delivery_radius_km'] = 'nullable|integer|min:1|max:50';
            $rules['food_delivery_base_fee'] = 'nullable|numeric|min:0|max:50';
            $rules['food_delivery_fee_per_km'] = 'nullable|numeric|min:0|max:5';
            $rules['food_min_order_delivery'] = 'nullable|numeric|min:0';
            $rules['food_free_delivery_above'] = 'nullable|numeric|min:0';
            $rules['food_estimated_prep_time'] = 'nullable|integer|min:5|max:180';
        }

        $rules['food_min_order_pickup'] = 'nullable|numeric|min:0';

        $validated = $request->validate($rules);

        // Préparer les données à sauvegarder
        $dataToSave = [
            'food_delivery_enabled' => $foodDeliveryEnabled,
            'food_pickup_enabled' => $foodPickupEnabled,
            'food_delivery_instructions' => $validated['food_delivery_instructions'] ?? null,
            'food_min_order_pickup' => $validated['food_min_order_pickup'] ?? null,
        ];

        // Ajouter les paramètres de livraison si activée
        if ($foodDeliveryEnabled) {
            $dataToSave['delivery_mode'] = $deliveryMode;
            $dataToSave['auto_assign_drivers'] = $request->has('auto_assign_drivers');
            $dataToSave['min_driver_rating'] = $validated['min_driver_rating'] ?? null;

            // Paramètres de tarification uniquement si interne ou both
            if (in_array($deliveryMode, ['internal', 'both'])) {
                $dataToSave['food_delivery_radius_km'] = $validated['food_delivery_radius_km'] ?? 5;
                $dataToSave['food_delivery_base_fee'] = $validated['food_delivery_base_fee'] ?? 3.00;
                $dataToSave['food_delivery_fee_per_km'] = $validated['food_delivery_fee_per_km'] ?? 0.50;
                $dataToSave['food_min_order_delivery'] = $validated['food_min_order_delivery'] ?? null;
                $dataToSave['food_free_delivery_above'] = $validated['food_free_delivery_above'] ?? null;
                $dataToSave['food_estimated_prep_time'] = $validated['food_estimated_prep_time'] ?? 30;
            }
        }

        $dataToSave['food_delivery_schedule'] = $this->sanitizeDeliverySchedule($request->input('delivery_schedule', []));

        $prestataire->update($dataToSave);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Paramètres de livraison mis à jour avec succès.',
            ]);
        }

        return back()->with('success', 'Paramètres de livraison mis à jour avec succès.');
    }

    private function getDeliveryScheduleDays(): array
    {
        return [
            'lundi' => 'Lundi',
            'mardi' => 'Mardi',
            'mercredi' => 'Mercredi',
            'jeudi' => 'Jeudi',
            'vendredi' => 'Vendredi',
            'samedi' => 'Samedi',
            'dimanche' => 'Dimanche',
        ];
    }

    private function normalizeDeliverySchedule(?array $schedule): array
    {
        $schedule = is_array($schedule) ? $schedule : [];
        $normalized = [];

        foreach ($this->getDeliveryScheduleDays() as $day => $label) {
            $normalized[$day] = $this->normalizeDaySchedule($schedule[$day] ?? null);
        }

        return $normalized;
    }

    private function sanitizeDeliverySchedule(?array $schedule): array
    {
        $schedule = is_array($schedule) ? $schedule : [];
        $normalized = [];
        $errors = [];

        foreach ($this->getDeliveryScheduleDays() as $day => $label) {
            $hours = is_array($schedule[$day] ?? null) ? $schedule[$day] : [];
            $enabled = !empty($hours['enabled']);
            $slots = [];
            $rawSlots = [];

            if (isset($hours['slots']) && is_array($hours['slots'])) {
                $rawSlots = $hours['slots'];
            } elseif (array_key_exists('start', $hours) || array_key_exists('end', $hours)) {
                $rawSlots = [[
                    'start' => $hours['start'] ?? null,
                    'end' => $hours['end'] ?? null,
                ]];
            }

            foreach ($rawSlots as $slotIndex => $slot) {
                if (!is_array($slot)) {
                    continue;
                }

                $start = $this->normalizeTimeValue($slot['start'] ?? null);
                $end = $this->normalizeTimeValue($slot['end'] ?? null);

                if (!$start && !$end) {
                    continue;
                }

                if (!$start || !$end) {
                    $errors["delivery_schedule.$day.slots.$slotIndex"] = "Le créneau " . ($slotIndex + 1) . " du {$label} doit avoir une heure de début et de fin.";
                    continue;
                }

                if ($start >= $end) {
                    $errors["delivery_schedule.$day.slots.$slotIndex"] = "Le créneau " . ($slotIndex + 1) . " du {$label} doit finir après son heure de début.";
                    continue;
                }

                $slots[] = [
                    'start' => $start,
                    'end' => $end,
                ];
            }

            usort($slots, static fn (array $left, array $right) => strcmp($left['start'], $right['start']));

            for ($i = 1; $i < count($slots); $i++) {
                if ($slots[$i]['start'] < $slots[$i - 1]['end']) {
                    $errors["delivery_schedule.$day.overlap.$i"] = "Les créneaux du {$label} se chevauchent. Corrigez-les avant d'enregistrer.";
                    break;
                }
            }

            if ($enabled && empty($slots)) {
                $errors["delivery_schedule.$day.required"] = "Ajoutez au moins un créneau pour {$label}, ou désactivez ce jour.";
            }

            $normalized[$day] = $this->buildScheduleDay($enabled, $slots);
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $normalized;
    }

    private function normalizeDaySchedule($daySchedule): array
    {
        $daySchedule = is_array($daySchedule) ? $daySchedule : [];
        $enabled = array_key_exists('enabled', $daySchedule) ? (bool) $daySchedule['enabled'] : true;
        $slots = [];
        $rawSlots = [];

        if (isset($daySchedule['slots']) && is_array($daySchedule['slots'])) {
            $rawSlots = $daySchedule['slots'];
        } elseif (!empty($daySchedule['start']) && !empty($daySchedule['end'])) {
            $rawSlots = [[
                'start' => $daySchedule['start'],
                'end' => $daySchedule['end'],
            ]];
        }

        foreach ($rawSlots as $slot) {
            if (!is_array($slot)) {
                continue;
            }

            $start = $this->normalizeTimeValue($slot['start'] ?? null);
            $end = $this->normalizeTimeValue($slot['end'] ?? null);

            if (!$start || !$end || $start >= $end) {
                continue;
            }

            $slots[] = [
                'start' => $start,
                'end' => $end,
            ];
        }

        usort($slots, static fn (array $left, array $right) => strcmp($left['start'], $right['start']));

        return $this->buildScheduleDay($enabled, $slots);
    }

    private function buildScheduleDay(bool $enabled, array $slots): array
    {
        if (!$enabled) {
            return [
                'enabled' => false,
                'slots' => [],
            ];
        }

        $slots = array_values($slots);

        if (empty($slots)) {
            $slots = [[
                'start' => '11:00',
                'end' => '22:00',
            ]];
        }

        return [
            'enabled' => true,
            'slots' => $slots,
            'start' => $slots[0]['start'],
            'end' => $slots[count($slots) - 1]['end'],
        ];
    }

    private function normalizeTimeValue($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $value) !== 1) {
            return null;
        }

        return substr($value, 0, 5);
    }

    /**
     * Calculer les frais de livraison pour une distance donnée
     */
    public function calculateDeliveryFee(Request $request)
    {
        $prestataire = Prestataire::findOrFail($request->prestataire_id);

        if (!$prestataire->food_delivery_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'La livraison n\'est pas disponible pour ce prestataire.',
            ]);
        }

        $distance = $request->distance ?? 0;
        $orderTotal = $request->order_total ?? 0;

        // Vérifier si dans la zone de livraison
        if ($distance > $prestataire->food_delivery_radius_km) {
            return response()->json([
                'success' => false,
                'message' => 'Vous êtes en dehors de la zone de livraison.',
                'max_distance' => $prestataire->food_delivery_radius_km,
            ]);
        }

        // Vérifier le minimum de commande
        if ($prestataire->food_min_order_delivery && $orderTotal < $prestataire->food_min_order_delivery) {
            return response()->json([
                'success' => false,
                'message' => 'Le montant minimum de commande pour la livraison est de ' . number_format($prestataire->food_min_order_delivery, 2) . ' €.',
                'min_order' => $prestataire->food_min_order_delivery,
            ]);
        }

        // Calculer les frais de livraison
        $deliveryFee = $prestataire->food_delivery_base_fee + ($distance * $prestataire->food_delivery_fee_per_km);

        // Livraison gratuite au-dessus d'un certain montant
        if ($prestataire->food_free_delivery_above && $orderTotal >= $prestataire->food_free_delivery_above) {
            $deliveryFee = 0;
        }

        return response()->json([
            'success' => true,
            'delivery_fee' => round($deliveryFee, 2),
            'distance' => $distance,
            'free_above' => $prestataire->food_free_delivery_above,
            'estimated_time' => $prestataire->food_estimated_prep_time + ceil($distance * 3), // 3 min par km en moyenne
        ]);
    }

    /**
     * Calculer la distance entre deux points GPS
     */
    public function calculateDistance(Request $request)
    {
        $request->validate([
            'prestataire_id' => 'required|exists:prestataires,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $prestataire = Prestataire::findOrFail($request->prestataire_id);

        $origin = $this->resolvePrestataireCoordinates($prestataire);
        if (!$origin) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de localiser le prestataire. Merci de verifier son adresse.',
            ]);
        }

        // Formule de Haversine
        $earthRadius = 6371; // km
        $lat1 = deg2rad($origin['lat']);
        $lat2 = deg2rad($request->lat);
        $deltaLat = deg2rad($request->lat - $origin['lat']);
        $deltaLng = deg2rad($request->lng - $origin['lng']);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($deltaLng / 2) * sin($deltaLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        $inZone = $distance <= $prestataire->food_delivery_radius_km;

        return response()->json([
            'success' => true,
            'distance' => round($distance, 2),
            'in_delivery_zone' => $inZone,
            'max_distance' => $prestataire->food_delivery_radius_km,
        ]);
    }

    /**
     * Retourne les coordonnees du prestataire.
     * Priorite: latitude/longitude en base, sinon geocodage de l'adresse du prestataire.
     */
    private function resolvePrestataireCoordinates(Prestataire $prestataire): ?array
    {
        $lat = (float) ($prestataire->latitude ?? 0);
        $lng = (float) ($prestataire->longitude ?? 0);

        if ($lat !== 0.0 && $lng !== 0.0) {
            return ['lat' => $lat, 'lng' => $lng];
        }

        $cityFallbacks = [
            'paris' => [48.8566, 2.3522],
            'marseille' => [43.2965, 5.3698],
            'lyon' => [45.7640, 4.8357],
            'toulouse' => [43.6047, 1.4442],
            'nice' => [43.7102, 7.2620],
            'nantes' => [47.2184, -1.5536],
            'montpellier' => [43.6110, 3.8767],
            'strasbourg' => [48.5734, 7.7521],
            'bordeaux' => [44.8378, -0.5792],
            'lille' => [50.6292, 3.0573],
            'rennes' => [48.1173, -1.6778],
            'saint-etienne' => [45.4397, 4.3872],
            'saint etienne' => [45.4397, 4.3872],
        ];

        $cityKey = strtolower(trim((string) ($prestataire->city ?? '')));
        if ($cityKey !== '' && isset($cityFallbacks[$cityKey])) {
            [$lat, $lng] = $cityFallbacks[$cityKey];
            $this->persistPrestataireCoordinates($prestataire, $lat, $lng);
            return ['lat' => $lat, 'lng' => $lng];
        }

        $addressParts = array_filter([
            trim((string) ($prestataire->address ?? '')),
            trim((string) ($prestataire->postal_code ?? '')),
            trim((string) ($prestataire->city ?? '')),
            trim((string) ($prestataire->country ?: 'France')),
        ], static fn ($part) => $part !== '');

        if (empty($addressParts)) {
            return null;
        }

        try {
            $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
                'q' => implode(', ', $addressParts),
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 0,
            ]);

            $context = stream_context_create([
                'http' => [
                    'timeout' => 4,
                    'user_agent' => 'TaPrestation/1.0 (Food delivery distance)',
                ],
            ]);

            $response = @file_get_contents($url, false, $context);
            if (!$response) {
                return null;
            }

            $data = json_decode($response, true);
            if (!is_array($data) || empty($data[0]['lat']) || empty($data[0]['lon'])) {
                return null;
            }

            $lat = (float) $data[0]['lat'];
            $lng = (float) $data[0]['lon'];
            if ($lat === 0.0 || $lng === 0.0) {
                return null;
            }

            $this->persistPrestataireCoordinates($prestataire, $lat, $lng);

            return ['lat' => $lat, 'lng' => $lng];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function persistPrestataireCoordinates(Prestataire $prestataire, float $lat, float $lng): void
    {
        try {
            $prestataire->forceFill([
                'latitude' => round($lat, 8),
                'longitude' => round($lng, 8),
            ])->save();
        } catch (\Throwable $e) {
            // Le calcul continue meme si la sauvegarde echoue.
        }
    }
}
