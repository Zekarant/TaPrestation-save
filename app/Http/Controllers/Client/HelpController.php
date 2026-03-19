<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Afficher la page d'aide pour le client
     */
    public function index()
    {
        // Sections d'aide organisées par catégories
        $helpSections = [
            'getting_started' => [
                'title' => 'Premiers pas',
                'icon' => 'rocket',
                'articles' => [
                    [
                        'title' => 'Comment créer votre première demande',
                        'description' => 'Guide étape par étape pour publier votre première demande de service',
                        'url' => '#getting-started-request'
                    ],
                    [
                        'title' => 'Rechercher et contacter des prestataires',
                        'description' => 'Apprenez à utiliser notre système de recherche et à communiquer avec les prestataires',
                        'url' => '#search-providers'
                    ],
                    [
                        'title' => 'Comprendre le processus de réservation',
                        'description' => 'De la demande à la réalisation : tout ce que vous devez savoir',
                        'url' => '#booking-process'
                    ]
                ]
            ],
            'managing_projects' => [
                'title' => 'Gestion de projets',
                'icon' => 'clipboard-list',
                'articles' => [
                    [
                        'title' => 'Suivre l\'avancement de vos demandes',
                        'description' => 'Utilisez votre tableau de bord pour monitorer vos projets en cours',
                        'url' => '#track-requests'
                    ],
                    [
                        'title' => 'Gérer vos réservations',
                        'description' => 'Modifier, annuler ou confirmer vos réservations',
                        'url' => '#manage-bookings'
                    ],
                    [
                        'title' => 'Évaluer les prestataires',
                        'description' => 'Comment laisser des avis constructifs après une prestation',
                        'url' => '#leave-reviews'
                    ]
                ]
            ],
            'communication' => [
                'title' => 'Communication',
                'icon' => 'chat',
                'articles' => [
                    [
                        'title' => 'Utiliser la messagerie intégrée',
                        'description' => 'Communiquez efficacement avec les prestataires via notre plateforme',
                        'url' => '#messaging'
                    ],
                    [
                        'title' => 'Négocier les tarifs et conditions',
                        'description' => 'Conseils pour obtenir le meilleur rapport qualité-prix',
                        'url' => '#negotiate'
                    ]
                ]
            ],
            'account_settings' => [
                'title' => 'Paramètres du compte',
                'icon' => 'cog',
                'articles' => [
                    [
                        'title' => 'Modifier votre profil',
                        'description' => 'Mettre à jour vos informations personnelles et préférences',
                        'url' => '#edit-profile'
                    ],
                    [
                        'title' => 'Gérer vos notifications',
                        'description' => 'Personnaliser les alertes que vous recevez',
                        'url' => '#notifications'
                    ],
                    [
                        'title' => 'Sécurité et confidentialité',
                        'description' => 'Protéger votre compte et vos données personnelles',
                        'url' => '#security'
                    ]
                ]
            ]
        ];
        
        // FAQ les plus fréquentes
        $frequentQuestions = [
            [
                'question' => 'Comment puis-je annuler une réservation ?',
                'answer' => 'Vous pouvez annuler une réservation depuis votre tableau de bord, section "Réservations". Les conditions d\'annulation dépendent du prestataire et du délai.'
            ],
            [
                'question' => 'Que faire si un prestataire ne répond pas ?',
                'answer' => 'Si un prestataire ne répond pas dans les 48h, vous pouvez contacter notre support ou rechercher d\'autres prestataires disponibles.'
            ],
            [
                'question' => 'Comment modifier ma demande après publication ?',
                'answer' => 'Vous pouvez modifier votre demande tant qu\'aucune offre n\'a été acceptée. Rendez-vous dans "Mes demandes" et cliquez sur "Modifier".'
            ],
            [
                'question' => 'Les tarifs affichés sont-ils définitifs ?',
                'answer' => 'Les tarifs peuvent être négociables selon le prestataire. N\'hésitez pas à discuter des conditions via la messagerie.'
            ]
        ];
        
        return view('client.help.index', compact('helpSections', 'frequentQuestions'));
    }
}