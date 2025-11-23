<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\EquipmentRental;
use App\Models\EquipmentRentalRequest;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Affiche la liste des commandes (réservations de services et équipements).
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Récupérer les réservations de services
        $bookings = Booking::with(['client.user', 'prestataire.user', 'service'])
            ->select('id', 'client_id', 'prestataire_id', 'service_id', 'start_datetime', 'end_datetime', 'total_amount', 'status', 'created_at')
            ->selectRaw("'service' as type")
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'type' => 'service',
                    'client_name' => $booking->client->user->name ?? 'N/A',
                    'prestataire_name' => $booking->prestataire->user->name ?? 'N/A',
                    'service_name' => $booking->service->title ?? 'N/A',
                    'amount' => $booking->total_amount,
                    'status' => $booking->status,
                    'date' => $booking->start_datetime,
                    'created_at' => $booking->created_at,
                ];
            });

        // Récupérer les locations d'équipements confirmées
        $equipmentRentals = EquipmentRental::with(['rentalRequest.client.user', 'rentalRequest.prestataire.user', 'equipment'])
            ->select('id', 'rental_request_id', 'equipment_id', 'start_date', 'end_date', 'total_amount', 'status', 'created_at')
            ->selectRaw("'equipment' as type")
            ->get()
            ->map(function ($rental) {
                return [
                    'id' => $rental->id,
                    'type' => 'equipment',
                    'client_name' => $rental->rentalRequest->client->user->name ?? 'N/A',
                    'prestataire_name' => $rental->rentalRequest->prestataire->user->name ?? 'N/A',
                    'service_name' => $rental->equipment->name ?? 'N/A',
                    'amount' => $rental->total_amount,
                    'status' => $rental->status,
                    'date' => $rental->start_date,
                    'created_at' => $rental->created_at,
                ];
            });

        // Combiner les deux collections
        $orders = $bookings->concat($equipmentRentals);

        // Appliquer les filtres
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $orders = $orders->filter(function ($order) use ($search) {
                return str_contains(strtolower($order['client_name']), $search) ||
                       str_contains(strtolower($order['prestataire_name']), $search) ||
                       str_contains(strtolower($order['service_name']), $search);
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $orders = $orders->filter(function ($order) use ($request) {
                return $order['type'] === $request->type;
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $orders = $orders->filter(function ($order) use ($request) {
                return $order['status'] === $request->status;
            });
        }

        // Trier par date de création (plus récent en premier)
        $orders = $orders->sortByDesc('created_at');

        // Pagination manuelle
        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedOrders = $orders->slice($offset, $perPage)->values();
        
        // Créer un objet de pagination
        $total = $orders->count();
        $lastPage = ceil($total / $perPage);
        
        // Statistiques
        $stats = [
            'total_orders' => $orders->count(),
            'total_services' => $bookings->count(),
            'total_equipment' => $equipmentRentals->count(),
            'total_revenue' => $orders->sum('amount'),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'confirmed_orders' => $orders->whereIn('status', ['confirmed', 'accepted'])->count(),
        ];

        return view('admin.orders.index-modern', [
            'orders' => $paginatedOrders,
            'stats' => $stats,
            'pagination' => [
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'total' => $total,
                'per_page' => $perPage,
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total),
            ],
            'filters' => [
                'search' => $request->search,
                'type' => $request->type,
                'status' => $request->status,
            ]
        ]);
    }

    /**
     * Affiche les détails d'une commande.
     */
    public function show($id)
    {
        // Cette méthode sera implémentée selon les besoins
        return redirect()->route('administrateur.orders.index');
    }

    /**
     * Affiche le formulaire d'édition d'une commande.
     */
    public function edit($id)
    {
        // Cette méthode sera implémentée selon les besoins
        return redirect()->route('administrateur.orders.index');
    }
}