<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Prestataire;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
        if ($request->has('title') && $request->title) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Filtrage par prestataire
        if ($request->has('prestataire') && $request->prestataire) {
            $query->whereHas('prestataire.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->prestataire . '%');
            });
        }

        // Filtrage par catégorie
        if ($request->has('category') && $request->category) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }

        // Filtrage par statut
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filtrage par prix
        if ($request->has('price_min') && $request->price_min) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->has('price_max') && $request->price_max) {
            $query->where('price', '<=', $request->price_max);
        }

        // Tri
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $services = $query->paginate($perPage);

        // Catégories pour le filtre
        $categories = Category::orderBy('name')->get();

        return view('admin.services.index-modern', [
            'services' => $services,
            'categories' => $categories,
        ]);
    }

    /**
     * Affiche le formulaire de création d'un service.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = Category::all();
        $prestataires = Prestataire::with('user')->get();

        return view('admin.services.create', [
            'categories' => $categories,
            'prestataires' => $prestataires,
        ]);
    }

    /**
     * Enregistre un nouveau service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'prestataire_id' => 'required|exists:prestataires,id',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $service = new Service();
        $service->title = $request->title;
        $service->description = $request->description;
        $service->prestataire_id = $request->prestataire_id;
        $service->status = 'active';

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('services', 'public');
            $service->image = $imagePath;
        }

        $service->save();

        if ($request->has('categories')) {
            $service->categories()->sync($request->categories);
        }

        return redirect()->route('administrateur.services.index')
            ->with('success', 'Le service a été créé avec succès.');
    }

    /**
     * Affiche les détails d'un service.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $service = Service::with(['prestataire', 'prestataire.user', 'categories'])->findOrFail($id);

        return view('admin.services.show', [
            'service' => $service,
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'un service.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $service = Service::with(['prestataire', 'categories'])->findOrFail($id);
        $categories = Category::all();
        $prestataires = Prestataire::with('user')->get();

        return view('admin.services.edit', [
            'service' => $service,
            'categories' => $categories,
            'prestataires' => $prestataires,
        ]);
    }

    /**
     * Met à jour un service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'prestataire_id' => 'required|exists:prestataires,id',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $service->title = $request->title;
        $service->description = $request->description;
        $service->prestataire_id = $request->prestataire_id;

        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }

            $imagePath = $request->file('image')->store('services', 'public');
            $service->image = $imagePath;
        }

        $service->save();

        if ($request->has('categories')) {
            $service->categories()->sync($request->categories);
        }

        return redirect()->route('administrateur.services.index')
            ->with('success', 'Le service a été mis à jour avec succès.');
    }

    /**
     * Masque ou affiche un service (toggle visibility/status).
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function toggleVisibility($id, Request $request)
    {
        $service = Service::findOrFail($id);

        // Toggle status between active and inactive
        $service->status = $service->status === 'active' ? 'inactive' : 'active';
        $service->save();

        $statusText = $service->status === 'active' ? 'activé' : 'désactivé';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Le service a été {$statusText} avec succès.",
                'status' => $service->status
            ]);
        }

        return redirect()->route('administrateur.services.index')
            ->with('success', "Le service a été {$statusText}.");
    }

    /**
     * Approuve un service.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function approve($id, Request $request)
    {
        $service = Service::findOrFail($id);
        $service->status = 'active';
        $service->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Le service a été approuvé avec succès.'
            ]);
        }

        return redirect()->route('administrateur.services.index')
            ->with('success', 'Le service a été approuvé avec succès.');
    }

    /**
     * Désactive un service.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function deactivate($id, Request $request)
    {
        $service = Service::findOrFail($id);
        $service->status = 'inactive';
        $service->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Le service a été désactivé avec succès.'
            ]);
        }

        return redirect()->route('administrateur.services.index')
            ->with('success', 'Le service a été désactivé avec succès.');
    }

    /**
     * Approuve plusieurs services en lot.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:services,id'
        ]);

        Service::whereIn('id', $request->ids)->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' service(s) approuvé(s) avec succès.'
        ]);
    }

    /**
     * Désactive plusieurs services en lot.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:services,id'
        ]);

        Service::whereIn('id', $request->ids)->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' service(s) désactivé(s) avec succès.'
        ]);
    }

    /**
     * Supprime plusieurs services en lot.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:services,id'
        ]);

        Service::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' service(s) supprimé(s) avec succès.'
        ]);
    }

    /**
     * Duplique un service.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function duplicate($id, Request $request)
    {
        $service = Service::with('categories')->findOrFail($id);

        $newService = $service->replicate();
        $newService->title = $service->title . ' (Copie)';
        $newService->status = 'pending';
        $newService->save();

        // Dupliquer les catégories
        $newService->categories()->sync($service->categories->pluck('id'));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Le service a été dupliqué avec succès.',
                'service_id' => $newService->id
            ]);
        }

        return redirect()->route('administrateur.services.index')
            ->with('success', 'Le service a été dupliqué avec succès.');
    }

    /**
     * Archive un service.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function archive($id, Request $request)
    {
        $service = Service::findOrFail($id);
        $service->status = 'archived';
        $service->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Le service a été archivé avec succès.'
            ]);
        }

        return redirect()->route('administrateur.services.index')
            ->with('success', 'Le service a été archivé avec succès.');
    }

    /**
     * Exporte les services au format CSV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $query = Service::with(['prestataire', 'prestataire.user', 'categories']);

        // Appliquer les mêmes filtres que dans la méthode index
        if ($request->has('title') && $request->title) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $services = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="services.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($services) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Titre', 'Prestataire', 'Catégories', 'Statut', 'Date de création']);

            foreach ($services as $service) {
                $categories = $service->categories->pluck('name')->implode(', ');
                fputcsv($file, [
                    $service->id,
                    $service->title,
                    $service->prestataire->user->name ?? 'N/A',
                    $categories,
                    $service->status,
                    $service->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Supprime un service.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id, Request $request)
    {
        try {
            $service = Service::findOrFail($id);

            // Supprimer l'image si elle existe
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }

            $service->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Le service a été supprimé avec succès.'
                ]);
            }

            return redirect()->route('administrateur.services.index')
                ->with('success', 'Le service a été supprimé avec succès.');
        } catch (\Exception $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression du service.'
                ], 500);
            }

            return redirect()->route('administrateur.services.index')
                ->with('error', 'Erreur lors de la suppression du service.');
        }
    }
}
