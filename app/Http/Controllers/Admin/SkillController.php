<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Skill;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SkillController extends Controller
{
    /**
     * Affiche la liste des compétences.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Skill::query();
        
        // Filtrage par nom
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        // Filtrage par description
        if ($request->has('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }
        
        // Tri
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);
        
        $skills = $query->paginate(15);
        
        return view('admin.skills.index-modern', [
            'skills' => $skills,
        ]);
    }
    
    /**
     * Affiche le formulaire de création d'une compétence.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.skills.create');
    }
    
    /**
     * Enregistre une nouvelle compétence.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:skills',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $skill = Skill::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        
        return redirect()->route('administrateur.skills.index')
            ->with('success', 'La compétence a été créée avec succès.');
    }
    
    /**
     * Affiche les détails d'une compétence.
     *
     * @param  \App\Models\Skill  $skill
     * @return \Illuminate\View\View
     */
    public function show(Skill $skill)
    {
        $prestataires = $skill->prestataires()->with('user')->paginate(10);
        
        return view('admin.skills.show', [
            'skill' => $skill,
            'prestataires' => $prestataires,
        ]);
    }
    
    /**
     * Affiche le formulaire d'édition d'une compétence.
     *
     * @param  \App\Models\Skill  $skill
     * @return \Illuminate\View\View
     */
    public function edit(Skill $skill)
    {
        return view('admin.skills.edit', [
            'skill' => $skill,
        ]);
    }
    
    /**
     * Met à jour une compétence.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Skill  $skill
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Skill $skill)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:skills,name,' . $skill->id,
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $skill->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        
        return redirect()->route('administrateur.skills.index')
            ->with('success', 'La compétence a été mise à jour avec succès.');
    }
    
    /**
     * Supprime une compétence.
     *
     * @param  \App\Models\Skill  $skill
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Skill $skill)
    {
        // Vérifier si la compétence est utilisée par des prestataires
        if ($skill->prestataires()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer cette compétence car elle est utilisée par des prestataires.');
        }
        
        $skill->delete();
        
        return redirect()->route('administrateur.skills.index')
            ->with('success', 'La compétence a été supprimée avec succès.');
    }
    
    /**
     * Exporte les compétences au format CSV.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export()
    {
        $skills = Skill::orderBy('name')->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="skills.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        
        $callback = function() use ($skills) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Description', 'Nombre de prestataires', 'Date de création']);
            
            foreach ($skills as $skill) {
                fputcsv($file, [
                    $skill->id,
                    $skill->name,
                    $skill->description,
                    $skill->prestataires()->count(),
                    $skill->created_at->format('d/m/Y H:i'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}