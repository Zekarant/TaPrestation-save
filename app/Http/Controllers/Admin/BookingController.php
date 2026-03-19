<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Prestataire;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Affiche la liste des réservations.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Booking::with(['client.user', 'prestataire.user', 'service']);
        
        // Filtrage par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtrage par date
        if ($request->filled('date_from')) {
            $query->whereDate('booking_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('booking_date', '<=', $request->date_to);
        }
        
        // Filtrage par prestataire
        if ($request->filled('prestataire')) {
            $query->whereHas('prestataire.user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->prestataire . '%');
            });
        }
        
        // Filtrage par client
        if ($request->filled('client')) {
            $query->whereHas('client.user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->client . '%');
            });
        }
        
        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Statistiques
        $stats = [
            'total' => Booking::count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
        ];
        
        return view('admin.bookings.index-modern', [
            'bookings' => $bookings,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Affiche les détails d'une réservation.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $booking = Booking::with(['client.user', 'prestataire.user', 'service'])->findOrFail($id);
        
        return view('admin.bookings.show', [
            'booking' => $booking,
        ]);
    }
    
    /**
     * Met à jour le statut d'une réservation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'admin_notes' => 'nullable|string|max:1000',
        ]);
        
        $booking = Booking::findOrFail($id);
        $booking->status = $request->status;
        $booking->admin_notes = $request->admin_notes;
        $booking->save();
        
        return redirect()->route('administrateur.bookings.show', $booking->id)
            ->with('success', 'Le statut de la réservation a été mis à jour avec succès.');
    }
    
    /**
     * Supprime une réservation.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();
        
        return redirect()->route('administrateur.bookings.index')
            ->with('success', 'La réservation a été supprimée avec succès.');
    }
    
    /**
     * Export des réservations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $query = Booking::with(['client.user', 'prestataire.user', 'service']);
        
        // Appliquer les mêmes filtres que l'index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('booking_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('booking_date', '<=', $request->date_to);
        }
        
        $bookings = $query->get();
        
        $filename = 'reservations_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($bookings) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Client',
                'Prestataire',
                'Service',
                'Date de réservation',
                'Statut',
                'Prix',
                'Créé le',
                'Mis à jour le'
            ]);
            
            // Données
            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->id,
                    $booking->client->user->name ?? 'N/A',
                    $booking->prestataire->user->name ?? 'N/A',
                    $booking->service->title ?? 'N/A',
                    $booking->booking_date,
                    $booking->status,
                    $booking->price ?? 'N/A',
                    $booking->created_at,
                    $booking->updated_at
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}