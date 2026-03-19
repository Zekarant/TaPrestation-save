<?php
/**
 * Générateur de sitemap.xml complet
 * Inclut toutes les catégories, sous-catégories et pages publiques
 * URL: https://taprestation.com/generate-sitemap.php?key=tap2026sitemap
 */

// Clé de sécurité - définir SITEMAP_SECRET dans les variables d'environnement serveur OVH
$secretKey = (string) getenv('SITEMAP_SECRET');
if ($secretKey === '' || !isset($_GET['key']) || $_GET['key'] !== $secretKey) {
    http_response_code(403);
    die('Accès non autorisé');
}

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Service;
use App\Models\Prestataire;
use App\Models\Equipment;
use App\Models\UrgentSale;
use App\Models\Category;
use App\Models\Video;

$baseUrl = 'https://taprestation.com';
$urls = [];
$today = date('Y-m-d');

echo "<h2>🗺️ Génération du Sitemap Complet</h2>";

// ============================================================================
// PAGES STATIQUES PRINCIPALES
// ============================================================================
$staticPages = [
    // Pages principales
    ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['url' => '/services', 'priority' => '1.0', 'changefreq' => 'hourly'],
    ['url' => '/prestataires', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['url' => '/equipment', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['url' => '/food', 'priority' => '0.9', 'changefreq' => 'hourly'],
    ['url' => '/urgent-sales', 'priority' => '0.9', 'changefreq' => 'hourly'],
    ['url' => '/videos', 'priority' => '0.8', 'changefreq' => 'daily'],
    
    // Pages légales
    ['url' => '/terms', 'priority' => '0.3', 'changefreq' => 'monthly'],
    ['url' => '/privacy', 'priority' => '0.3', 'changefreq' => 'monthly'],
    ['url' => '/cgu', 'priority' => '0.3', 'changefreq' => 'monthly'],
    ['url' => '/cgv', 'priority' => '0.3', 'changefreq' => 'monthly'],
    ['url' => '/cookies', 'priority' => '0.3', 'changefreq' => 'monthly'],
    ['url' => '/mentions-legales', 'priority' => '0.3', 'changefreq' => 'monthly'],
    ['url' => '/contact', 'priority' => '0.5', 'changefreq' => 'monthly'],
    ['url' => '/faq', 'priority' => '0.5', 'changefreq' => 'monthly'],
];

foreach ($staticPages as $page) {
    $urls[] = [
        'loc' => $baseUrl . $page['url'],
        'lastmod' => $today,
        'changefreq' => $page['changefreq'],
        'priority' => $page['priority'],
    ];
}
echo "✅ Pages statiques: " . count($staticPages) . "<br>";

// ============================================================================
// CATÉGORIES ET SOUS-CATÉGORIES (Toutes depuis la BDD)
// ============================================================================
try {
    // Catégories principales (sans parent)
    $mainCategories = Category::whereNull('parent_id')
        ->where('is_active', true)
        ->get();
    
    $catCount = 0;
    $subCatCount = 0;
    
    foreach ($mainCategories as $category) {
        $slug = $category->slug ?? \Illuminate\Support\Str::slug($category->name);
        $type = $category->type ?? 'service';
        
        // Page catégorie principale
        $categoryUrl = match($type) {
            'equipment' => '/equipment?category=' . $slug,
            'sale' => '/urgent-sales?category=' . $slug,
            default => '/services?category=' . $slug
        };
        
        $urls[] = [
            'loc' => $baseUrl . $categoryUrl,
            'lastmod' => $category->updated_at ? $category->updated_at->format('Y-m-d') : $today,
            'changefreq' => 'daily',
            'priority' => '0.85',
        ];
        $catCount++;
        
        // Sous-catégories
        $subcategories = Category::where('parent_id', $category->id)
            ->where('is_active', true)
            ->get();
        
        foreach ($subcategories as $subcategory) {
            $subSlug = $subcategory->slug ?? \Illuminate\Support\Str::slug($subcategory->name);
            
            $subUrl = match($type) {
                'equipment' => '/equipment?category=' . $slug . '&subcategory=' . $subSlug,
                'sale' => '/urgent-sales?category=' . $slug . '&subcategory=' . $subSlug,
                default => '/services?category=' . $slug . '&subcategory=' . $subSlug
            };
            
            $urls[] = [
                'loc' => $baseUrl . $subUrl,
                'lastmod' => $subcategory->updated_at ? $subcategory->updated_at->format('Y-m-d') : $today,
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
            $subCatCount++;
        }
    }
    echo "✅ Catégories principales: {$catCount}<br>";
    echo "✅ Sous-catégories: {$subCatCount}<br>";
} catch (\Exception $e) {
    echo "❌ Erreur catégories: " . $e->getMessage() . "<br>";
}

// ============================================================================
// PAGES PAR VILLE (SEO Local)
// ============================================================================
$cities = [
    'paris', 'lyon', 'marseille', 'toulouse', 'bordeaux', 
    'nantes', 'nice', 'lille', 'strasbourg', 'montpellier',
    'rennes', 'grenoble', 'rouen', 'toulon', 'dijon',
    'angers', 'le-mans', 'reims', 'saint-etienne', 'le-havre',
    'clermont-ferrand', 'tours', 'amiens', 'limoges', 'perpignan',
    'metz', 'besancon', 'orleans', 'caen', 'mulhouse',
    'nancy', 'brest', 'argenteuil', 'montreuil', 'saint-denis'
];

foreach ($cities as $city) {
    // Services par ville
    $urls[] = [
        'loc' => $baseUrl . '/services?city=' . $city,
        'lastmod' => $today,
        'changefreq' => 'daily',
        'priority' => '0.8',
    ];
    
    // Food par ville
    $urls[] = [
        'loc' => $baseUrl . '/food?city=' . $city,
        'lastmod' => $today,
        'changefreq' => 'daily',
        'priority' => '0.75',
    ];
    
    // Équipements par ville
    $urls[] = [
        'loc' => $baseUrl . '/equipment?city=' . $city,
        'lastmod' => $today,
        'changefreq' => 'daily',
        'priority' => '0.75',
    ];
    
    // Ventes urgentes par ville
    $urls[] = [
        'loc' => $baseUrl . '/urgent-sales?city=' . $city,
        'lastmod' => $today,
        'changefreq' => 'daily',
        'priority' => '0.75',
    ];
}
echo "✅ Pages par ville: " . (count($cities) * 4) . "<br>";

// ============================================================================
// SERVICES ACTIFS
// ============================================================================
try {
    $services = Service::where('status', 'active')
        ->latest('updated_at')
        ->take(2000)
        ->get();

    foreach ($services as $service) {
        $urls[] = [
            'loc' => $baseUrl . '/services/' . $service->id,
            'lastmod' => $service->updated_at->format('Y-m-d'),
            'changefreq' => 'weekly',
            'priority' => '0.7',
        ];
    }
    echo "✅ Services: " . count($services) . "<br>";
} catch (\Exception $e) {
    echo "❌ Erreur services: " . $e->getMessage() . "<br>";
}

// ============================================================================
// PRESTATAIRES (avec toutes leurs pages)
// ============================================================================
try {
    $prestataires = Prestataire::whereHas('user', function ($q) {
        $q->where('is_active', true);
    })
        ->latest('updated_at')
        ->take(2000)
        ->get();

    foreach ($prestataires as $prestataire) {
        // Page profil
        $urls[] = [
            'loc' => $baseUrl . '/prestataires/' . $prestataire->id,
            'lastmod' => $prestataire->updated_at->format('Y-m-d'),
            'changefreq' => 'weekly',
            'priority' => '0.65',
        ];
        
        // Boutique du prestataire
        $urls[] = [
            'loc' => $baseUrl . '/prestataires/' . $prestataire->id . '/boutique',
            'lastmod' => $prestataire->updated_at->format('Y-m-d'),
            'changefreq' => 'weekly',
            'priority' => '0.6',
        ];
        
        // Services du prestataire
        $urls[] = [
            'loc' => $baseUrl . '/prestataires/' . $prestataire->id . '/services',
            'lastmod' => $prestataire->updated_at->format('Y-m-d'),
            'changefreq' => 'weekly',
            'priority' => '0.6',
        ];
        
        // Équipements du prestataire
        $urls[] = [
            'loc' => $baseUrl . '/prestataires/' . $prestataire->id . '/equipements',
            'lastmod' => $prestataire->updated_at->format('Y-m-d'),
            'changefreq' => 'weekly',
            'priority' => '0.55',
        ];
    }
    echo "✅ Prestataires (x4 pages): " . (count($prestataires) * 4) . "<br>";
} catch (\Exception $e) {
    echo "❌ Erreur prestataires: " . $e->getMessage() . "<br>";
}

// ============================================================================
// ÉQUIPEMENTS (Location de matériel)
// ============================================================================
try {
    $equipments = Equipment::where('is_available', true)
        ->latest('updated_at')
        ->take(1000)
        ->get();

    foreach ($equipments as $equipment) {
        $urls[] = [
            'loc' => $baseUrl . '/equipment/' . $equipment->id,
            'lastmod' => $equipment->updated_at->format('Y-m-d'),
            'changefreq' => 'weekly',
            'priority' => '0.6',
        ];
    }
    echo "✅ Équipements: " . count($equipments) . "<br>";
} catch (\Exception $e) {
    echo "❌ Erreur équipements: " . $e->getMessage() . "<br>";
}

// ============================================================================
// VENTES URGENTES (Annonces)
// ============================================================================
try {
    $urgentSales = UrgentSale::where('status', 'active')
        ->latest('updated_at')
        ->take(1000)
        ->get();

    foreach ($urgentSales as $sale) {
        $urls[] = [
            'loc' => $baseUrl . '/urgent-sales/' . $sale->id,
            'lastmod' => $sale->updated_at->format('Y-m-d'),
            'changefreq' => 'hourly',
            'priority' => '0.75',
        ];
    }
    echo "✅ Ventes urgentes: " . count($urgentSales) . "<br>";
} catch (\Exception $e) {
    echo "❌ Erreur ventes urgentes: " . $e->getMessage() . "<br>";
}

// ============================================================================
// VIDÉOS
// ============================================================================
try {
    $videos = Video::where('status', 'approved')
        ->latest('updated_at')
        ->take(500)
        ->get();

    foreach ($videos as $video) {
        $urls[] = [
            'loc' => $baseUrl . '/videos/' . $video->id,
            'lastmod' => $video->updated_at->format('Y-m-d'),
            'changefreq' => 'weekly',
            'priority' => '0.5',
        ];
    }
    echo "✅ Vidéos: " . count($videos) . "<br>";
} catch (\Exception $e) {
    echo "❌ Erreur vidéos: " . $e->getMessage() . "<br>";
}

// ============================================================================
// RESTAURANTS / FOOD (Menus des prestataires)
// ============================================================================
try {
    // Prestataires avec produits food actifs
    $foodPrestataires = Prestataire::whereHas('foodProducts', function($q) {
        $q->where('is_available', true);
    })->get();
    
    foreach ($foodPrestataires as $prestataire) {
        $urls[] = [
            'loc' => $baseUrl . '/food/' . $prestataire->id,
            'lastmod' => $prestataire->updated_at->format('Y-m-d'),
            'changefreq' => 'daily',
            'priority' => '0.7',
        ];
    }
    echo "✅ Restaurants (Food): " . count($foodPrestataires) . "<br>";
} catch (\Exception $e) {
    echo "❌ Erreur food: " . $e->getMessage() . "<br>";
}

// ============================================================================
// PAGES SPÉCIALES SEO (Filtres populaires)
// ============================================================================
$seoPages = [
    // Types de services
    '/services?type=a-domicile' => 0.75,
    '/services?type=en-ligne' => 0.75,
    '/services?type=sur-place' => 0.75,
    
    // Filtres populaires
    '/services?sort=popular' => 0.7,
    '/services?sort=newest' => 0.7,
    '/services?sort=rating' => 0.7,
    
    '/equipment?sort=popular' => 0.65,
    '/equipment?sort=newest' => 0.65,
    '/equipment?available=now' => 0.7,
    
    '/urgent-sales?sort=newest' => 0.7,
    '/urgent-sales?sort=ending-soon' => 0.75,
    
    '/food?sort=popular' => 0.7,
    '/food?sort=rating' => 0.7,
    '/food?delivery=true' => 0.75,
];

foreach ($seoPages as $page => $priority) {
    $urls[] = [
        'loc' => $baseUrl . $page,
        'lastmod' => $today,
        'changefreq' => 'daily',
        'priority' => (string)$priority,
    ];
}
echo "✅ Pages SEO spéciales: " . count($seoPages) . "<br>";

// ============================================================================
// GÉNÉRER LE XML
// ============================================================================
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

// Sauvegarder le fichier
$sitemapPath = __DIR__ . '/sitemap.xml';
if (file_put_contents($sitemapPath, $xml)) {
    echo "<br><h3 style='color:green'>✅ Sitemap généré avec succès!</h3>";
    echo "<strong>Total URLs:</strong> " . count($urls) . "<br>";
    echo "<strong>Fichier:</strong> " . $sitemapPath . "<br>";
    echo "<strong>Taille:</strong> " . round(strlen($xml) / 1024, 2) . " KB<br>";
    echo "<br><a href='/sitemap.xml' target='_blank'>👉 Voir le sitemap</a>";
} else {
    echo "<br><strong style='color:red'>❌ Erreur lors de l'écriture du fichier</strong><br>";
}
