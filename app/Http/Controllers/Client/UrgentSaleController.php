<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\UrgentSale;
use App\Models\UrgentSaleContact;
use App\Models\UrgentSaleReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UrgentSaleController extends Controller
{
    /**
     * Afficher la liste des annonces
     */
    public function index(Request $request)
    {
        $query = UrgentSale::active()
            ->with(['prestataire.user'])
            ->latest();
        
        // Filtres
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }
        
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }
        
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }
        
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
        
        if ($request->filled('urgent_only')) {
            $query->urgent();
        }
        
        // Tri
        $sortBy = $request->get('sort', 'recent');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;

            default:
                $query->orderBy('created_at', 'desc');
        }
        
        $urgentSales = $query->paginate(12);
        
        // Statistiques pour les filtres
        $conditions = UrgentSale::active()->distinct()->pluck('condition');
        $locations = UrgentSale::active()->whereNotNull('location')->distinct()->pluck('location');
        
        return view('client.urgent-sales.index', compact('urgentSales', 'conditions', 'locations'));
    }
    
    /**
     * Afficher une vente urgente spécifique
     */
    public function show(UrgentSale $urgentSale)
    {
        if (!$urgentSale->canBeContacted()) {
            abort(404);
        }
        
        // Incrémenter le nombre de vues
        $urgentSale->incrementViews();
        
        // Charger les relations
        $urgentSale->load(['prestataire.user']);
        
        // Ventes similaires du même prestataire
        $similarSales = UrgentSale::active()
            ->where('prestataire_id', $urgentSale->prestataire_id)
            ->where('id', '!=', $urgentSale->id)
            ->limit(4)
            ->get();
        
        return view('client.urgent-sales.show', compact('urgentSale', 'similarSales'));
    }
    
    /**
     * Contacter le vendeur
     */
    public function contact(Request $request, UrgentSale $urgentSale)
    {
        if (!$urgentSale->canBeContacted()) {
            return back()->with('error', 'Ce produit n\'est plus disponible.');
        }
        
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Vérifier si l'utilisateur n'a pas déjà contacté récemment
        if (Auth::check()) {
            $recentContact = UrgentSaleContact::where('urgent_sale_id', $urgentSale->id)
                ->where('user_id', Auth::id())
                ->where('created_at', '>', now()->subHours(24))
                ->exists();
            
            if ($recentContact) {
                return back()->with('error', 'Vous avez déjà contacté ce vendeur dans les dernières 24h.');
            }
        }
        
        $contact = new UrgentSaleContact();
        $contact->urgent_sale_id = $urgentSale->id;
        $contact->user_id = Auth::id();
        $contact->message = $request->message;
        $contact->phone = $request->phone;
        $contact->email = $request->email ?: (Auth::check() ? Auth::user()->email : null);
        $contact->save();
        
        // Incrémenter le compteur de contacts
        $urgentSale->incrementContacts();
        
        return back()->with('success', 'Votre message a été envoyé au vendeur!');
    }
    
    /**
     * Signaler une vente
     */
    public function report(Request $request, UrgentSale $urgentSale)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|in:inappropriate,fake,spam,fraud,other',
            'description' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Vérifier si l'utilisateur n'a pas déjà signalé
        if (Auth::check()) {
            $existingReport = UrgentSaleReport::where('urgent_sale_id', $urgentSale->id)
                ->where('user_id', Auth::id())
                ->exists();
            
            if ($existingReport) {
                return back()->with('error', 'Vous avez déjà signalé ce produit.');
            }
        }
        
        $report = new UrgentSaleReport();
        $report->urgent_sale_id = $urgentSale->id;
        $report->user_id = Auth::id();
        $report->reason = $request->reason;
        $report->description = $request->description;
        $report->save();
        
        return back()->with('success', 'Signalement envoyé. Merci de nous aider à maintenir la qualité de la plateforme.');
    }
}
