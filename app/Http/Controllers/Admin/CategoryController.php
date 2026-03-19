<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Affiche la liste des catégories.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Category::query();
        
        // Filtrage par nom
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        // Filtrage par catégorie parente
        if ($request->has('parent_id') && $request->parent_id != '') {
            $query->where('parent_id', $request->parent_id);
        } elseif ($request->has('parent') && $request->parent == 'none') {
            $query->whereNull('parent_id');
        }
        
        // Tri
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);
        
        $categories = $query->paginate(15);
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();
        
        return view('admin.categories.index-modern', [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
        ]);
    }
    
    /**
     * Affiche le formulaire de création d'une catégorie.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();
        
        return view('admin.categories.create', [
            'parentCategories' => $parentCategories,
        ]);
    }
    
    /**
     * Enregistre une nouvelle catégorie.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
        ]);
        
        return redirect()->route('administrateur.categories.index')
            ->with('success', 'La catégorie a été créée avec succès.');
    }
    
    /**
     * Affiche les détails d'une catégorie.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\View\View
     */
    public function show(Category $category)
    {
        return view('admin.categories.show', [
            'category' => $category,
        ]);
    }
    
    /**
     * Affiche le formulaire d'édition d'une catégorie.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\View\View
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();
        
        return view('admin.categories.edit', [
            'category' => $category,
            'parentCategories' => $parentCategories,
        ]);
    }
    
    /**
     * Met à jour une catégorie.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Vérifier que la catégorie parente n'est pas la catégorie elle-même ou une de ses enfants
        if ($request->parent_id && $request->parent_id == $category->id) {
            return redirect()->back()
                ->withErrors(['parent_id' => 'Une catégorie ne peut pas être sa propre catégorie parente.'])
                ->withInput();
        }
        
        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
        ]);
        
        return redirect()->route('administrateur.categories.index')
            ->with('success', 'La catégorie a été mise à jour avec succès.');
    }
    
    /**
     * Supprime une catégorie.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Category $category)
    {
        // Vérifier si la catégorie a des enfants
        if ($category->children()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer cette catégorie car elle contient des sous-catégories.');
        }
        
        // Vérifier si la catégorie est utilisée par des services
        if ($category->services()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer cette catégorie car elle est utilisée par des services.');
        }
        
        $category->delete();
        
        return redirect()->route('administrateur.categories.index')
            ->with('success', 'La catégorie a été supprimée avec succès.');
    }
    
    /**
     * Exporte les catégories au format CSV.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export()
    {
        $categories = Category::with('parent')->orderBy('name')->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="categories.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        
        $callback = function() use ($categories) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Description', 'Catégorie parente', 'Date de création']);
            
            foreach ($categories as $category) {
                fputcsv($file, [
                    $category->id,
                    $category->name,
                    $category->description,
                    $category->parent ? $category->parent->name : 'Aucune',
                    $category->created_at->format('d/m/Y H:i'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}