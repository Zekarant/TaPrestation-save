<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Models\Service;
use App\Models\Prestataire;
use App\Models\Equipment;
use App\Models\UrgentSale;
use App\Models\Category;

class SitemapController extends Controller
{
    /**
     * Génère le sitemap XML dynamique pour le SEO
     * Optimisé pour Google, Bing, Yandex
     */
    public function index(): Response
    {
        $urls = collect();
        $baseUrl = config('app.url', 'https://taprestation.com');

        // ========== PAGES STATIQUES PRINCIPALES ==========
        $staticPages = [
            ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => '/services', 'priority' => '1.0', 'changefreq' => 'hourly'],
            ['url' => '/prestataires', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => '/equipment', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => '/food', 'priority' => '0.9', 'changefreq' => 'hourly'],
            ['url' => '/urgent-sales', 'priority' => '0.9', 'changefreq' => 'hourly'],
            ['url' => '/videos', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['url' => '/register', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => '/login', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => '/terms', 'priority' => '0.3', 'changefreq' => 'monthly'],
            ['url' => '/privacy', 'priority' => '0.3', 'changefreq' => 'monthly'],
            ['url' => '/cgu', 'priority' => '0.3', 'changefreq' => 'monthly'],
            ['url' => '/cgv', 'priority' => '0.3', 'changefreq' => 'monthly'],
        ];

        foreach ($staticPages as $page) {
            $urls->push([
                'loc' => $baseUrl . $page['url'],
                'lastmod' => now()->toDateString(),
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority'],
            ]);
        }

        // ========== PAGES PAR VILLE (SEO LOCAL) ==========
        $cities = [
            'paris', 'lyon', 'marseille', 'toulouse', 'bordeaux', 
            'nantes', 'nice', 'lille', 'strasbourg', 'montpellier',
            'rennes', 'grenoble', 'rouen', 'toulon', 'dijon',
            'angers', 'le-mans', 'reims', 'saint-etienne', 'le-havre',
            'clermont-ferrand', 'tours', 'amiens', 'limoges', 'perpignan'
        ];

        foreach ($cities as $city) {
            $urls->push([
                'loc' => $baseUrl . '/services?city=' . $city,
                'lastmod' => now()->toDateString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ]);
        }

        // ========== PAGES PAR CATÉGORIE DE SERVICE ==========
        $serviceCategories = [
            'coiffure', 'menage', 'bricolage', 'plomberie', 'electricite',
            'peinture', 'jardinage', 'traiteur', 'photographe', 'dj',
            'decoration', 'coach-sportif', 'cours-particuliers', 'garde-enfants',
            'demenagement', 'chauffeur', 'esthetique', 'massage', 'informatique'
        ];

        foreach ($serviceCategories as $category) {
            $urls->push([
                'loc' => $baseUrl . '/services?category=' . $category,
                'lastmod' => now()->toDateString(),
                'changefreq' => 'daily',
                'priority' => '0.85',
            ]);
        }

        // ========== SERVICES ACTIFS ==========
        try {
            $services = Service::where('status', 'active')
                ->latest('updated_at')
                ->take(1000)
                ->get();

            foreach ($services as $service) {
                $urls->push([
                    'loc' => $baseUrl . '/services/' . $service->id,
                    'lastmod' => $service->updated_at->toDateString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ]);
            }
        } catch (\Exception $e) {
            // Model might not exist or have different structure
        }

        // ========== PRESTATAIRES VÉRIFIÉS ==========
        try {
            $prestataires = Prestataire::whereHas('user', function ($q) {
                $q->where('is_active', true);
            })
                ->latest('updated_at')
                ->take(1000)
                ->get();

            foreach ($prestataires as $prestataire) {
                $urls->push([
                    'loc' => $baseUrl . '/prestataires/' . $prestataire->id,
                    'lastmod' => $prestataire->updated_at->toDateString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.65',
                ]);
            }
        } catch (\Exception $e) {
            // Model might not exist
        }

        // ========== ÉQUIPEMENTS DISPONIBLES ==========
        try {
            $equipments = Equipment::where('is_available', true)
                ->latest('updated_at')
                ->take(500)
                ->get();

            foreach ($equipments as $equipment) {
                $urls->push([
                    'loc' => $baseUrl . '/equipment/' . $equipment->id,
                    'lastmod' => $equipment->updated_at->toDateString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.6',
                ]);
            }
        } catch (\Exception $e) {
            // Model might not exist
        }

        // ========== VENTES URGENTES ACTIVES ==========
        try {
            $urgentSales = UrgentSale::where('status', 'active')
                ->latest('updated_at')
                ->take(300)
                ->get();

            foreach ($urgentSales as $sale) {
                $urls->push([
                    'loc' => $baseUrl . '/urgent-sales/' . $sale->id,
                    'lastmod' => $sale->updated_at->toDateString(),
                    'changefreq' => 'hourly',
                    'priority' => '0.75',
                ]);
            }
        } catch (\Exception $e) {
            // Model might not exist
        }

        // ========== CATÉGORIES DYNAMIQUES ==========
        try {
            $categories = Category::where('is_active', true)->get();

            foreach ($categories as $category) {
                $slug = $category->slug ?? str_replace(' ', '-', strtolower($category->name));
                $urls->push([
                    'loc' => $baseUrl . '/services?category=' . $slug,
                    'lastmod' => $category->updated_at->toDateString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ]);
            }
        } catch (\Exception $e) {
            // Model might not exist
        }

        // ========== GÉNÉRER LE XML ==========
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
        $xml .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
        $xml .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

        foreach ($urls as $url) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . PHP_EOL;
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=3600'); // Cache 1h
    }
}
