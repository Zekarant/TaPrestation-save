<?php

/**
 * Configuration du système de livraison
 * Tarifs basés sur le modèle Uber Eats France
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Commission Plateforme
    |--------------------------------------------------------------------------
    | Montant fixe prélevé sur chaque livraison (sur le livreur)
    */
    'platform_fee' => 0.05, // 0.05€ par livraison

    /*
    |--------------------------------------------------------------------------
    | Tarification Livreur (ce que le livreur gagne)
    |--------------------------------------------------------------------------
    */
    'driver' => [
        'base_fee' => 2.00,           // Base par livraison
        'pickup_per_km' => 0.50,       // €/km du livreur vers le restaurant
        'dropoff_per_km' => 0.80,      // €/km du restaurant vers le client
        'min_earning' => 3.00,         // Gain minimum garanti
        'max_earning' => 20.00,        // Gain maximum par course
    ],

    /*
    |--------------------------------------------------------------------------
    | Tarification Client (ce que le client paie)
    |--------------------------------------------------------------------------
    */
    'client' => [
        'base_fee' => 2.49,            // Frais de base
        'per_km' => 0.50,              // €/km
        'min_fee' => 1.99,             // Minimum frais de livraison
        'max_fee' => 7.99,             // Maximum frais de livraison
        'free_delivery_above' => 25.00, // Livraison gratuite au-dessus de X€
    ],

    /*
    |--------------------------------------------------------------------------
    | Majorations (Surge Pricing)
    |--------------------------------------------------------------------------
    */
    'surge' => [
        'enabled' => true,
        
        // Heures de pointe
        'peak_hours' => [
            ['start' => 11, 'end' => 14, 'multiplier' => 1.2],  // Midi
            ['start' => 18, 'end' => 21, 'multiplier' => 1.3],  // Soir
        ],
        
        // Conditions météo
        'weather' => [
            'rain' => 1.15,      // +15% quand il pleut
            'heavy_rain' => 1.30, // +30% pluie forte
            'snow' => 1.50,       // +50% neige
        ],
        
        // Forte demande (ratio commandes/livreurs)
        'demand' => [
            'high' => 1.20,      // > 5 commandes par livreur
            'very_high' => 1.50, // > 10 commandes par livreur
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Système de Batch (multi-commandes)
    |--------------------------------------------------------------------------
    */
    'batch' => [
        'enabled' => true,
        'max_orders' => 3,             // Max commandes par batch
        'time_window' => 10,           // Minutes pour grouper
        'max_client_distance' => 2.0,  // Km max entre clients
        'max_pickup_distance' => 1.5,  // Km max entre restaurants
        'bonus_per_extra_order' => 1.50, // Bonus par commande supplémentaire
    ],

    /*
    |--------------------------------------------------------------------------
    | Géolocalisation Livreur
    |--------------------------------------------------------------------------
    */
    'gps' => [
        'update_interval' => 10,       // Secondes entre mises à jour
        'accuracy_threshold' => 50,    // Mètres - ignorer si moins précis
        'stale_threshold' => 60,       // Secondes - considérer hors ligne après
        'history_retention' => 24,     // Heures - garder l'historique
    ],

    /*
    |--------------------------------------------------------------------------
    | Matching Livreur
    |--------------------------------------------------------------------------
    */
    'matching' => [
        'search_radius' => 5.0,        // Km - rayon de recherche livreurs
        'min_drivers_for_external' => 5, // Nb livreurs min pour afficher resto "externe only"
        'auto_assign_timeout' => 120,  // Secondes avant réassignation auto
        'max_concurrent_orders' => 3,  // Max commandes simultanées par livreur
    ],

    /*
    |--------------------------------------------------------------------------
    | API Routing
    |--------------------------------------------------------------------------
    */
    'routing' => [
        'provider' => 'openrouteservice',
        'api_key' => env('OPENROUTESERVICE_KEY'),
        'cache_duration' => 60,        // Minutes - cache des calculs de route
        'fallback_speed_kmh' => 20,    // Km/h si API échoue (vélo/scooter)
    ],

];
