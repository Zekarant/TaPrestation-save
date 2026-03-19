<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class LegalPagesController extends Controller
{
    /**
     * Afficher la liste des pages légales
     */
    public function index()
    {
        $pages = LegalPage::orderBy('title')->get();
        $availablePages = LegalPage::availablePages();
        
        return view('admin.legal-pages.index', compact('pages', 'availablePages'));
    }

    /**
     * Afficher le formulaire d'édition d'une page
     */
    public function edit(LegalPage $legalPage)
    {
        // Si le contenu est vide, extraire le contenu de la vue statique
        $staticViewExists = $this->staticViewExists($legalPage->slug);
        $hasStaticContent = $staticViewExists && empty($legalPage->content);
        
        // Si pas de contenu en BDD, pré-remplir avec le contenu statique
        if ($hasStaticContent) {
            $staticContent = $this->extractStaticContent($legalPage->slug);
            if ($staticContent) {
                $legalPage->content = $staticContent;
            }
        }
        
        return view('admin.legal-pages.edit', compact('legalPage', 'hasStaticContent', 'staticViewExists'));
    }

    /**
     * Extraire le contenu HTML d'une vue statique
     */
    private function extractStaticContent($slug)
    {
        $viewMap = [
            'terms' => 'legal/terms',
            'privacy' => 'legal/privacy',
            'cookies' => 'legal/cookies',
            'mentions' => 'legal/mentions',
            'faq' => 'pages/faq',
            'contact' => 'pages/contact',
        ];

        $viewPath = $viewMap[$slug] ?? null;
        
        if (!$viewPath) {
            return null;
        }

        $filePath = resource_path("views/{$viewPath}.blade.php");
        
        if (!file_exists($filePath)) {
            return null;
        }

        try {
            $fileContent = file_get_contents($filePath);
            
            // Extraire le contenu entre @section('content') et @endsection
            if (preg_match('/@section\s*\(\s*[\'"]content[\'"]\s*\)(.*?)@endsection/s', $fileContent, $matches)) {
                $content = $matches[1];
                
                // Nettoyer le contenu - retirer les wrappers de layout
                // Garder seulement le contenu à l'intérieur de la div prose ou du contenu principal
                
                // Chercher le contenu dans la div prose
                if (preg_match('/<div class="prose[^"]*"[^>]*>(.*)/s', $content, $proseMatch)) {
                    $innerContent = $proseMatch[1];
                    // Retirer les divs fermantes à la fin
                    $innerContent = preg_replace('/\s*<\/div>\s*<\/div>\s*<\/div>\s*$/s', '', $innerContent);
                    return trim($innerContent);
                }
                
                // Si pas de div prose, chercher après bg-white
                if (preg_match('/<div class="bg-white[^>]*>(.*)/s', $content, $bgMatch)) {
                    $innerContent = $bgMatch[1];
                    // Chercher le header et le contenu
                    if (preg_match('/<!-- Header -->.*?<\/div>(.*)/s', $innerContent, $afterHeader)) {
                        $innerContent = $afterHeader[1];
                    }
                    // Nettoyer les divs de fin
                    $innerContent = preg_replace('/\s*<\/div>\s*<\/div>\s*<\/div>\s*$/s', '', $innerContent);
                    return trim($innerContent);
                }
                
                return trim($content);
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Vérifier si une vue statique existe pour ce slug
     */
    private function staticViewExists($slug)
    {
        $viewMap = [
            'terms' => 'legal.terms',
            'privacy' => 'legal.privacy',
            'cookies' => 'legal.cookies',
            'mentions' => 'legal.mentions',
            'faq' => 'pages.faq',
            'contact' => 'pages.contact',
        ];

        $viewName = $viewMap[$slug] ?? null;
        return $viewName && View::exists($viewName);
    }

    /**
     * Mettre à jour une page
     */
    public function update(Request $request, LegalPage $legalPage)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:10240',
            'is_active' => 'boolean',
        ]);

        $data = [
            'title' => $request->title,
            'content' => HtmlSanitizer::sanitize((string) $request->content),
            'is_active' => $request->boolean('is_active', true),
            'updated_by_user_id' => Auth::id(),
        ];

        // Gérer l'upload de fichier
        if ($request->hasFile('file')) {
            // Supprimer l'ancien fichier
            if ($legalPage->file_path && Storage::disk('public')->exists($legalPage->file_path)) {
                Storage::disk('public')->delete($legalPage->file_path);
            }
            
            $file = $request->file('file');
            $path = $file->store('legal-pages', 'public');
            $data['file_path'] = $path;
        }

        // Supprimer le fichier si demandé
        if ($request->boolean('remove_file') && $legalPage->file_path) {
            if (Storage::disk('public')->exists($legalPage->file_path)) {
                Storage::disk('public')->delete($legalPage->file_path);
            }
            $data['file_path'] = null;
        }

        $legalPage->update($data);

        return redirect()->route('admin.legal-pages.index')
            ->with('success', 'La page "' . $legalPage->title . '" a été mise à jour avec succès.');
    }

    /**
     * Prévisualiser une page
     */
    public function preview(LegalPage $legalPage)
    {
        return view('admin.legal-pages.preview', compact('legalPage'));
    }
}
