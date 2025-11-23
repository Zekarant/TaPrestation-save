<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Prestataire;
use App\Models\PrestataireAvailability;

use App\Models\TimeSlot;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    /**
     * Affiche la page de gestion des disponibilités
     */
    public function index()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        // Récupérer les disponibilités hebdomadaires
        $weeklyAvailability = PrestataireAvailability::where('prestataire_id', $prestataire->id)
            ->get();

        // Si aucune disponibilité n'est configurée, on en crée par défaut
        if ($weeklyAvailability->isEmpty()) {
            // Créer une disponibilité par défaut pour chaque jour de la semaine (1=Lundi, ..., 7=Dimanche)
            for ($i = 1; $i <= 7; $i++) {
                PrestataireAvailability::create([
                    'prestataire_id' => $prestataire->id,
                    'day_of_week' => $i % 7, // 1=Lundi, ..., 6=Samedi, 0=Dimanche
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'slot_duration' => 60,
                    'is_active' => false,
                ]);
            }
            // On recharge les disponibilités
            $weeklyAvailability = PrestataireAvailability::where('prestataire_id', $prestataire->id)
                ->get();
        }

        // Trier par jour de la semaine, en s'assurant que Lundi (1) vient en premier.
        $weeklyAvailability = $weeklyAvailability->sortBy(function ($item) {
            // Traite le dimanche (0) comme le 7ème jour pour le tri
            return $item->day_of_week == 0 ? 7 : $item->day_of_week;
        });

        // Récupérer les paramètres de réservation
        $bookingSettings = $prestataire->bookingSettings ?? null;

        return view('prestataire.availability.index', compact(
            'prestataire',
            'weeklyAvailability',
            'bookingSettings'
        ));
    }
    
    /**
     * API pour récupérer les événements du calendrier de disponibilité
     */
    public function events(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;
        
        $start = Carbon::parse($request->get('start'));
        $end = Carbon::parse($request->get('end'));
        
        // Récupérer les disponibilités hebdomadaires
        $weeklyAvailability = PrestataireAvailability::where('prestataire_id', $prestataire->id)
            ->where('is_active', true)
            ->get();
        

        
        // Récupérer les réservations existantes
        $bookings = TimeSlot::where('prestataire_id', $prestataire->id)
            ->whereIn('status', ['booked', 'pending'])
            ->whereBetween('start_datetime', [$start, $end])
            ->get();
        
        $events = [];
        
        // Ajouter les disponibilités hebdomadaires
        for ($day = $start->copy(); $day <= $end; $day->addDay()) {
            $dayOfWeek = $day->dayOfWeek;
            $dayAvailability = $weeklyAvailability->where('day_of_week', $dayOfWeek)->first();
            
            if ($dayAvailability && $dayAvailability->is_active) {
                $startTime = $day->copy()->setTimeFromTimeString($dayAvailability->start_time);
                $endTime = $day->copy()->setTimeFromTimeString($dayAvailability->end_time);
                
                $events[] = [
                    'id' => 'avail_' . $day->format('Y-m-d') . '_' . $dayAvailability->id,
                    'title' => 'Disponible',
                    'start' => $startTime->toISOString(),
                    'end' => $endTime->toISOString(),
                    'backgroundColor' => '#10b981', // vert
                    'borderColor' => '#10b981',
                    'textColor' => '#ffffff',
                    'allDay' => false,
                    'extendedProps' => [
                        'type' => 'availability'
                    ]
                ];
            }
        }
        
        // Ajouter les réservations
        foreach ($bookings as $booking) {
            $color = $booking->status === 'pending' ? '#f59e0b' : '#3b82f6'; // orange pour pending, bleu pour booked
            
            $events[] = [
                'id' => 'booking_' . $booking->id,
                'title' => 'Réservé',
                'start' => $booking->start_datetime->toISOString(),
                'end' => $booking->end_datetime->toISOString(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'allDay' => false,
                'extendedProps' => [
                    'type' => 'booking',
                    'status' => $booking->status
                ]
            ];
        }
        
        return response()->json($events);
    }
    
    /**
     * Met à jour les disponibilités hebdomadaires
     */
    public function updateWeeklyAvailability(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        $daysData = $request->input('days', []);

        foreach ($daysData as $dayOfWeek => $data) {
            $availability = PrestataireAvailability::where('prestataire_id', $prestataire->id)
                ->where('day_of_week', $dayOfWeek)
                ->first();

            if ($availability) {
                $availability->update([
                    'is_active' => isset($data['is_active']) && $data['is_active'] == '1',
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'slot_duration' => $data['slot_duration'] ?? 60, // Default to 60 minutes if not provided
                ]);
            }
        }

        return redirect()->route('prestataire.availability.index')
            ->with('success', 'Vos disponibilités ont été mises à jour avec succès.');
    }
    
    /**
     * Met à jour les paramètres de réservation
     */
    public function updateBookingSettings(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;
        
        $request->validate([
            'requires_approval' => 'required|boolean',
            'min_advance_hours' => 'required|integer|min:0',
            'max_advance_days' => 'required|integer|min:1',
            'buffer_between_appointments' => 'required|integer|min:0',
        ]);
        
        $prestataire->update([
            'requires_approval' => $request->input('requires_approval'),
            'min_advance_hours' => $request->input('min_advance_hours'),
            'max_advance_days' => $request->input('max_advance_days'),
            'buffer_between_appointments' => $request->input('buffer_between_appointments'),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Paramètres de réservation mis à jour avec succès'
        ]);
    }
    
    /**
     * Génère les créneaux horaires disponibles pour une période donnée
     */
    public function generateTimeSlots(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;
        
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'service_id' => 'required|exists:services,id',
        ]);
        
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        $serviceId = $request->input('service_id');
        
        // Vérifier que le service appartient au prestataire
        $service = $prestataire->services()->findOrFail($serviceId);
        
        // Récupérer les disponibilités hebdomadaires
        $weeklyAvailability = PrestataireAvailability::where('prestataire_id', $prestataire->id)
            ->where('is_active', true)
            ->get();
        

        
        $generatedSlots = [];
        
        // Pour chaque jour de la période
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dayOfWeek = $date->dayOfWeek;
            $dayAvailability = $weeklyAvailability->where('day_of_week', $dayOfWeek)->first();
            
            // Vérifier si le jour est disponible
            if ($dayAvailability) {
                $startTime = $date->copy()->setTimeFromTimeString($dayAvailability->start_time);
                $endTime = $date->copy()->setTimeFromTimeString($dayAvailability->end_time);
                $slotDuration = $dayAvailability->slot_duration;
                
                // Générer les créneaux pour ce jour
                $currentTime = $startTime->copy();
                
                while ($currentTime->copy()->addMinutes($slotDuration) <= $endTime) {
                    $slotEnd = $currentTime->copy()->addMinutes($slotDuration);
                    
                    // Vérifier si le créneau chevauche une réservation existante
                    $existingSlot = TimeSlot::where('prestataire_id', $prestataire->id)
                        ->where(function ($query) use ($currentTime, $slotEnd) {
                            $query->whereBetween('start_datetime', [$currentTime, $slotEnd])
                                ->orWhereBetween('end_datetime', [$currentTime, $slotEnd])
                                ->orWhere(function ($q) use ($currentTime, $slotEnd) {
                                    $q->where('start_datetime', '<=', $currentTime)
                                      ->where('end_datetime', '>=', $slotEnd);
                                });
                        })
                        ->exists();
                    
                    if (!$existingSlot) {
                        // Créer le créneau
                        $slot = TimeSlot::create([
                            'prestataire_id' => $prestataire->id,
                            'service_id' => $service->id,
                            'start_datetime' => $currentTime,
                            'end_datetime' => $slotEnd,
                            'status' => 'available',
                            // 'price' => $service->price, // Supprimé pour des raisons de confidentialité
                            'requires_approval' => $service->requires_approval ?? $prestataire->requires_approval ?? false,
                        ]);
                        
                        $generatedSlots[] = $slot;
                    }
                    
                    $currentTime = $slotEnd;
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => count($generatedSlots) . ' créneaux horaires générés avec succès',
            'slots' => $generatedSlots
        ]);
    }
}