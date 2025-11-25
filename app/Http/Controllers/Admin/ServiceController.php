<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Prestataire;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    /**
     * Affiche la liste des services pour modération.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Service::with(['prestataire', 'prestataire.user', 'categories']);
        
        // Filtrage par titre
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        
        // Filtrage par prestataire
        if ($request->has('prestataire')) {
            $query->whereHas('prestataire.user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->prestataire . '%');
            });
        }
        
        // Filtrage par catégorie
        if ($request->has('category')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->category . '%');
            });
        }
        
        // Tri
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        $services = $query->paginate(15);
        
        return view('admin.services.index-modern', [
            'services' => $services,
        ]);
    }

    // /**
    //  * Affiche le formulaire de création d'un service.
    //  *
    //  * @return \Illuminate\View\View
    //  */
    // public function create()
    // {
    //     $categories = Category::all();
    //     $prestataires = Prestataire::with('user')->get();
        
    //     return view('admin.services.create', [
    //         'categories' => $categories,
    //         'prestataires' => $prestataires,
    //     ]);
    // }

    // /**
    //  * Enregistre un nouveau service.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'title' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         // 'price' => 'required|numeric|min:0', // Supprimé pour confidentialité
    //         'prestataire_id' => 'required|exists:prestataires,id',
    //         'categories' => 'array',
    //         'categories.*' => 'exists:categories,id',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
    //     ]);

    //     $service = new Service();
    //     $service->title = $request->title;
    //     $service->description = $request->description;
    //     // $service->price = $request->price; // Supprimé pour confidentialité
    //     $service->prestataire_id = $request->prestataire_id;
    //     $service->is_visible = true;

    //     if ($request->hasFile('image')) {
    //         $imagePath = $request->file('image')->store('services', 'public');
    //         $service->image = $imagePath;
    //     }

    //     $service->save();

    //     if ($request->has('categories')) {
    //         $service->categories()->sync($request->categories);
    //     }

    //     return redirect()->route('administrateur.services.index')
    //         ->with('success', 'Le service a été créé avec succès.');
    // }

    // /**
    //  * Affiche les détails d'un service.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\View\View
    //  */
    // public function show($id)
    // {
    //     $service = Service::with(['prestataire', 'prestataire.user', 'categories'])->findOrFail($id);
        
    //     return view('admin.services.show', [
    //         'service' => $service,
    //     ]);
    // }

    // /**
    //  * Affiche le formulaire d'édition d'un service.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\View\View
    //  */
    // public function edit($id)
    // {
    //     $service = Service::with(['prestataire', 'categories'])->findOrFail($id);
    //     $categories = Category::all();
    //     $prestataires = Prestataire::with('user')->get();
        
    //     return view('admin.services.edit', [
    //         'service' => $service,
    //         'categories' => $categories,
    //         'prestataires' => $prestataires,
    //     ]);
    // }

    // /**
    //  * Met à jour un service.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  int  $id
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function update(Request $request, $id)
    // {
    //     $service = Service::findOrFail($id);
        
    //     $request->validate([
    //         'title' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         // 'price' => 'required|numeric|min:0', // Supprimé pour confidentialité
    //         'prestataire_id' => 'required|exists:prestataires,id',
    //         'categories' => 'array',
    //         'categories.*' => 'exists:categories,id',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
    //     ]);

    //     $service->title = $request->title;
    //     $service->description = $request->description;
    //     // $service->price = $request->price; // Supprimé pour confidentialité
    //     $service->prestataire_id = $request->prestataire_id;

    //     if ($request->hasFile('image')) {
    //         // Supprimer l'ancienne image si elle existe
    //         if ($service->image) {
    //             Storage::disk('public')->delete($service->image);
    //         }
            
    //         $imagePath = $request->file('image')->store('services', 'public');
    //         $service->image = $imagePath;
    //     }

    //     $service->save();

    //     if ($request->has('categories')) {
    //         $service->categories()->sync($request->categories);
    //     }

    //     return redirect()->route('administrateur.services.index')
    //         ->with('success', 'Le service a été mis à jour avec succès.');
    // }

    // /**
    //  * Masque ou affiche un service.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function toggleVisibility($id)
    // {
    //     $service = Service::findOrFail($id);
    //     $service->is_visible = !$service->is_visible;
    //     $service->save();
        
    //     $status = $service->is_visible ? 'visible' : 'masqué';
        
    //     return redirect()->route('administrateur.services.index')
    //         ->with('success', "Le service est maintenant {$status}.");
    //     }

    // /**
    //  * Supprime un service.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        
        return redirect()->route('administrateur.services.index')
            ->with('success', 'Le service a été supprimé avec succès.');
    }
}