<?php

namespace App\Http\Controllers;

use App\Models\LegalPage;
use Illuminate\Http\Request;

class LegalPageController extends Controller
{
    /**
     * Afficher une page légale dynamique
     */
    public function show($slug)
    {
        // Mapper les slugs des routes aux slugs de la base de données
        $slugMap = [
            'mentions-legales' => 'mentions',
            'cgu' => 'terms',
            'legal' => 'terms',
        ];

        $dbSlug = $slugMap[$slug] ?? $slug;

        $page = LegalPage::where('slug', $dbSlug)
            ->where('is_active', true)
            ->first();

        if (!$page) {
            // Fallback vers les vues statiques si la page n'existe pas en base
            $viewMap = [
                'terms' => 'legal.terms',
                'privacy' => 'legal.privacy',
                'cookies' => 'legal.cookies',
                'mentions' => 'legal.mentions',
                'faq' => 'pages.faq',
                'contact' => 'pages.contact',
                'videos' => 'pages.videos',
            ];

            $viewName = $viewMap[$dbSlug] ?? null;
            
            if ($viewName && view()->exists($viewName)) {
                return view($viewName);
            }

            abort(404, 'Page non trouvée');
        }

        // Si le contenu est défini, afficher la page dynamique
        if ($page->content || $page->file_path) {
            return view('legal.dynamic', compact('page'));
        }

        // Sinon, fallback vers la vue statique
        $staticViews = [
            'terms' => 'legal.terms',
            'privacy' => 'legal.privacy',
            'cookies' => 'legal.cookies',
            'mentions' => 'legal.mentions',
            'faq' => 'pages.faq',
            'contact' => 'pages.contact',
            'videos' => 'pages.videos',
        ];

        $viewName = $staticViews[$dbSlug] ?? null;
        
        if ($viewName && view()->exists($viewName)) {
            return view($viewName, compact('page'));
        }

        // Afficher quand même le contenu dynamique (même vide)
        return view('legal.dynamic', compact('page'));
    }
}
