<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Prestataire;

class PrestataireVisibilityController extends Controller
{
    /**
     * Afficher la page de gestion des fonctionnalités de visibilité
     */
    public function index()
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            return redirect()->route('prestataire.dashboard')
                ->with('error', 'Profil prestataire non trouvé.');
        }
        
        return view('prestataire.visibility-features', compact('prestataire'));
    }
    
    /**
     * Mettre à jour le portfolio multimédia
     */
    public function updatePortfolio(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            return redirect()->back()->with('error', 'Profil prestataire non trouvé.');
        }
        
        $validator = Validator::make($request->all(), [
            'portfolio_description' => 'nullable|string|max:2000',
            'portfolio_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
            'portfolio_videos.*' => 'nullable|mimes:mp4,mov,avi,wmv|max:51200', // 50MB
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Mettre à jour la description
        $prestataire->portfolio_description = $request->portfolio_description;
        
        // Traiter les nouvelles images
        if ($request->hasFile('portfolio_images')) {
            $currentImages = $prestataire->portfolio_images ?? [];
            
            // Vérifier la limite de 10 images
            if (count($currentImages) + count($request->file('portfolio_images')) > 10) {
                return redirect()->back()
                    ->with('error', 'Vous ne pouvez avoir que 10 images maximum dans votre portfolio.');
            }
            
            foreach ($request->file('portfolio_images') as $image) {
                $path = $image->store('portfolio/images', 'public');
                $currentImages[] = $path;
            }
            
            $prestataire->portfolio_images = $currentImages;
        }
        
        // Traiter les nouvelles vidéos
        if ($request->hasFile('portfolio_videos')) {
            $currentVideos = $prestataire->portfolio_videos ?? [];
            
            // Vérifier la limite de 5 vidéos
            if (count($currentVideos) + count($request->file('portfolio_videos')) > 5) {
                return redirect()->back()
                    ->with('error', 'Vous ne pouvez avoir que 5 vidéos maximum dans votre portfolio.');
            }
            
            foreach ($request->file('portfolio_videos') as $video) {
                $path = $video->store('portfolio/videos', 'public');
                $currentVideos[] = $path;
            }
            
            $prestataire->portfolio_videos = $currentVideos;
        }
        
        $prestataire->save();
        
        return redirect()->back()
            ->with('success', 'Portfolio mis à jour avec succès.');
    }
    
    /**
     * Ajouter un document (général, certification ou diplôme)
     */
    public function storeDocument(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            return redirect()->back()->with('error', 'Profil prestataire non trouvé.');
        }
        
        $documentType = $request->input('document_type'); // documents, certifications, diplomas
        
        $rules = [
            'document_name' => 'required|string|max:255',
            'document_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB
        ];
        
        // Règles spécifiques selon le type de document
        if ($documentType === 'certifications') {
            $rules['issuer'] = 'nullable|string|max:255';
            $rules['date'] = 'nullable|date';
        } elseif ($documentType === 'diplomas') {
            $rules['institution'] = 'nullable|string|max:255';
            $rules['year'] = 'nullable|integer|min:1950|max:2030';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Stocker le fichier
        $file = $request->file('document_file');
        $path = $file->store("documents/{$documentType}", 'public');
        
        // Préparer les données du document
        $documentData = [
            'name' => $request->document_name,
            'path' => $path,
            'type' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'uploaded_at' => now()->toDateTimeString(),
        ];
        
        // Ajouter des champs spécifiques selon le type
        if ($documentType === 'certifications') {
            $documentData['issuer'] = $request->issuer;
            $documentData['date'] = $request->date;
        } elseif ($documentType === 'diplomas') {
            $documentData['institution'] = $request->institution;
            $documentData['year'] = $request->year;
        }
        
        // Ajouter le document à la liste existante
        $currentDocuments = $prestataire->{$documentType} ?? [];
        $currentDocuments[] = $documentData;
        $prestataire->{$documentType} = $currentDocuments;
        
        $prestataire->save();
        
        $typeLabel = [
            'documents' => 'document',
            'certifications' => 'certification',
            'diplomas' => 'diplôme'
        ][$documentType];
        
        return redirect()->back()
            ->with('success', ucfirst($typeLabel) . ' ajouté(e) avec succès.');
    }
    
    /**
     * Supprimer un document
     */
    public function deleteDocument($type, $index)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            return response()->json(['error' => 'Profil prestataire non trouvé.'], 404);
        }
        
        $documents = $prestataire->{$type} ?? [];
        
        if (!isset($documents[$index])) {
            return response()->json(['error' => 'Document non trouvé.'], 404);
        }
        
        // Supprimer le fichier du stockage
        if (isset($documents[$index]['path'])) {
            Storage::disk('public')->delete($documents[$index]['path']);
        }
        
        // Supprimer le document de la liste
        unset($documents[$index]);
        $prestataire->{$type} = array_values($documents); // Réindexer le tableau
        
        $prestataire->save();
        
        return response()->json(['success' => 'Document supprimé avec succès.']);
    }
    
    /**
     * Supprimer une image du portfolio
     */
    public function deletePortfolioImage($index)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            return response()->json(['error' => 'Profil prestataire non trouvé.'], 404);
        }
        
        $images = $prestataire->portfolio_images ?? [];
        
        if (!isset($images[$index])) {
            return response()->json(['error' => 'Image non trouvée.'], 404);
        }
        
        // Supprimer le fichier du stockage
        Storage::disk('public')->delete($images[$index]);
        
        // Supprimer l'image de la liste
        unset($images[$index]);
        $prestataire->portfolio_images = array_values($images); // Réindexer le tableau
        
        $prestataire->save();
        
        return response()->json(['success' => 'Image supprimée avec succès.']);
    }
    
    /**
     * Supprimer une vidéo du portfolio
     */
    public function deletePortfolioVideo($index)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            return response()->json(['error' => 'Profil prestataire non trouvé.'], 404);
        }
        
        $videos = $prestataire->portfolio_videos ?? [];
        
        if (!isset($videos[$index])) {
            return response()->json(['error' => 'Vidéo non trouvée.'], 404);
        }
        
        // Supprimer le fichier du stockage
        Storage::disk('public')->delete($videos[$index]);
        
        // Supprimer la vidéo de la liste
        unset($videos[$index]);
        $prestataire->portfolio_videos = array_values($videos); // Réindexer le tableau
        
        $prestataire->save();
        
        return response()->json(['success' => 'Vidéo supprimée avec succès.']);
    }
}