<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UrgentSale;
use Illuminate\Http\Request;

class UrgentSaleController extends Controller
{
    /**
     * Display a listing of urgent sales for admin.
     */
    public function index(Request $request)
    {
        $query = UrgentSale::with(['prestataire.user'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('prestataire.user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }



        if ($request->filled('reported') && $request->reported === 'yes') {
            $query->where('status', 'reported');
        }

        // Apply sorting
        if ($request->filled('sort')) {
            $sortField = $request->sort;
            $sortOrder = $request->get('order', 'desc');
            
            switch ($sortField) {
                case 'title':
                    $query->orderBy('title', $sortOrder);
                    break;
                case 'price':
                    $query->orderBy('price', $sortOrder);
                    break;
                case 'views':
                    $query->orderBy('views_count', $sortOrder);
                    break;

                default:
                    $query->orderBy('created_at', $sortOrder);
            }
        }

        $urgentSales = $query->paginate(20);

        // Calculate statistics
        $stats = [
            'total' => UrgentSale::count(),
            'active' => UrgentSale::where('status', 'active')->count(),
            'reported' => UrgentSale::where('status', 'reported')->count(),
        ];

        // Get categories - create a simple array for now
        $categories = collect([
            (object)['id' => 1, 'name' => 'Électronique'],
            (object)['id' => 2, 'name' => 'Mobilier'],
            (object)['id' => 3, 'name' => 'Vêtements'],
            (object)['id' => 4, 'name' => 'Véhicules'],
            (object)['id' => 5, 'name' => 'Immobilier'],
            (object)['id' => 6, 'name' => 'Autres'],
        ]);

        return view('admin.urgent-sales.index', compact('urgentSales', 'stats', 'categories'));
    }
    
    /**
     * Display the specified urgent sale.
     */
    public function show(UrgentSale $urgentSale)
    {
        $urgentSale->load(['prestataire.user']);
        
        return view('admin.urgent-sales.show', compact('urgentSale'));
    }
    
    /**
     * Suspend the specified urgent sale.
     */
    public function suspend(Request $request, UrgentSale $urgentSale)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $urgentSale->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspension_reason' => $request->reason
        ]);

        return redirect()->back()->with('success', 'Vente urgente suspendue avec succès.');
    }
    
    /**
     * Reactivate the specified urgent sale.
     */
    public function reactivate(UrgentSale $urgentSale)
    {
        $urgentSale->update([
            'status' => 'active',
            'suspended_at' => null,
            'suspension_reason' => null
        ]);

        return redirect()->back()->with('success', 'Vente urgente réactivée avec succès.');
    }
    
    /**
     * Remove the specified urgent sale from storage.
     */
    public function destroy(UrgentSale $urgentSale)
    {
        $urgentSale->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Vente urgente supprimée avec succès.');
    }
    
    /**
     * Display urgent sales dashboard.
     */
    public function dashboard()
    {
        $stats = [
            'total' => UrgentSale::count(),
            'active' => UrgentSale::where('status', 'active')->count(),
            'reported' => UrgentSale::where('status', 'reported')->count(),
            'suspended' => UrgentSale::where('status', 'suspended')->count(),
            'sold' => UrgentSale::where('status', 'sold')->count(),
        ];

        $recentSales = UrgentSale::with(['prestataire.user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();



        $reportedSales = UrgentSale::with(['prestataire.user'])
            ->where('status', 'reported')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.urgent-sales.dashboard', compact(
            'stats', 'recentSales', 'reportedSales'
        ));
    }
}