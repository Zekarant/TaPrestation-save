<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Création des catégories principales (sans parent)
        $plomberie = Category::firstOrCreate(['name' => 'Plomberie'],[
            'name' => 'Plomberie',
            'description' => 'Services de plomberie et installations sanitaires',
        ]);
        
        // Sous-catégories de Plomberie
        Category::firstOrCreate(['name' => 'Installation sanitaire'],[
            'name' => 'Installation sanitaire',
            'description' => 'Installation de sanitaires et équipements de salle de bain',
            'parent_id' => $plomberie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Dépannage plomberie'],[
            'name' => 'Dépannage plomberie',
            'description' => 'Services de dépannage et réparation en plomberie',
            'parent_id' => $plomberie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Chauffage'],[
            'name' => 'Chauffage',
            'description' => 'Installation et entretien de systèmes de chauffage',
            'parent_id' => $plomberie->id,
        ]);
        
        $electricite = Category::firstOrCreate(['name' => 'Électricité'],[
            'name' => 'Électricité',
            'description' => 'Services d\'installation et réparation électrique',
        ]);
        
        // Sous-catégories d'Électricité
        Category::firstOrCreate(['name' => 'Installation électrique'],[
            'name' => 'Installation électrique',
            'description' => 'Installation et mise aux normes de systèmes électriques',
            'parent_id' => $electricite->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Dépannage électrique'],[
            'name' => 'Dépannage électrique',
            'description' => 'Services de dépannage et réparation électrique',
            'parent_id' => $electricite->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Domotique'],[
            'name' => 'Domotique',
            'description' => 'Installation de systèmes domotiques et maison intelligente',
            'parent_id' => $electricite->id,
        ]);
        
        $informatique = Category::firstOrCreate(['name' => 'Informatique'],[
            'name' => 'Informatique',
            'description' => 'Services informatiques et assistance technique',
        ]);
        
        // Sous-catégories d'Informatique
        Category::firstOrCreate(['name' => 'Dépannage informatique'],[
            'name' => 'Dépannage informatique',
            'description' => 'Dépannage et réparation d\'ordinateurs et périphériques',
            'parent_id' => $informatique->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Développement web'],[
            'name' => 'Développement web',
            'description' => 'Création et maintenance de sites web et applications',
            'parent_id' => $informatique->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Réseaux'],[
            'name' => 'Réseaux',
            'description' => 'Installation et maintenance de réseaux informatiques',
            'parent_id' => $informatique->id,
        ]);
        
        $graphisme = Category::firstOrCreate(['name' => 'Graphisme'],[
            'name' => 'Graphisme',
            'description' => 'Services de conception graphique et design',
        ]);
        
        // Sous-catégories de Graphisme
        Category::firstOrCreate(['name' => 'Identité visuelle'],[
            'name' => 'Identité visuelle',
            'description' => 'Création de logos et identités visuelles',
            'parent_id' => $graphisme->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Illustration'],[
            'name' => 'Illustration',
            'description' => 'Création d\'illustrations et dessins',
            'parent_id' => $graphisme->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Print'],[
            'name' => 'Print',
            'description' => 'Conception de supports imprimés (flyers, affiches, etc.)',
            'parent_id' => $graphisme->id,
        ]);
        
        $marketing = Category::firstOrCreate(['name' => 'Marketing'],[
            'name' => 'Marketing',
            'description' => 'Services de marketing et communication',
        ]);
        
        // Sous-catégories de Marketing
        Category::firstOrCreate(['name' => 'Marketing digital'],[
            'name' => 'Marketing digital',
            'description' => 'Stratégies de marketing en ligne et réseaux sociaux',
            'parent_id' => $marketing->id,
        ]);
        
        Category::firstOrCreate(['name' => 'SEO'],[
            'name' => 'SEO',
            'description' => 'Optimisation pour les moteurs de recherche',
            'parent_id' => $marketing->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Relations publiques'],[
            'name' => 'Relations publiques',
            'description' => 'Services de relations publiques et communication',
            'parent_id' => $marketing->id,
        ]);
        
        $menuiserie = Category::firstOrCreate(['name' => 'Menuiserie'],[
            'name' => 'Menuiserie',
            'description' => 'Services de menuiserie et travaux du bois',
        ]);
        
        // Sous-catégories de Menuiserie
        Category::firstOrCreate(['name' => 'Menuiserie intérieure'],[
            'name' => 'Menuiserie intérieure',
            'description' => 'Fabrication et pose de meubles et aménagements intérieurs',
            'parent_id' => $menuiserie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Menuiserie extérieure'],[
            'name' => 'Menuiserie extérieure',
            'description' => 'Fabrication et pose de structures extérieures en bois',
            'parent_id' => $menuiserie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Ébénisterie'],[
            'name' => 'Ébénisterie',
            'description' => 'Création de meubles et objets en bois de qualité',
            'parent_id' => $menuiserie->id,
        ]);
        
        $peinture = Category::firstOrCreate(['name' => 'Peinture'],[
            'name' => 'Peinture',
            'description' => 'Services de peinture intérieure et extérieure',
        ]);
        
        // Sous-catégories de Peinture
        Category::firstOrCreate(['name' => 'Peinture intérieure'],[
            'name' => 'Peinture intérieure',
            'description' => 'Travaux de peinture pour l\'intérieur des bâtiments',
            'parent_id' => $peinture->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Peinture extérieure'],[
            'name' => 'Peinture extérieure',
            'description' => 'Travaux de peinture pour l\'extérieur des bâtiments',
            'parent_id' => $peinture->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Peinture décorative'],[
            'name' => 'Peinture décorative',
            'description' => 'Techniques de peinture décorative et artistique',
            'parent_id' => $peinture->id,
        ]);
        
        $maconnerie = Category::firstOrCreate(['name' => 'Maçonnerie'],[
            'name' => 'Maçonnerie',
            'description' => 'Services de maçonnerie et travaux de construction',
        ]);
        
        // Sous-catégories de Maçonnerie
        Category::firstOrCreate(['name' => 'Construction'],[
            'name' => 'Construction',
            'description' => 'Construction de murs, fondations et structures',
            'parent_id' => $maconnerie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Rénovation maçonnerie'],[
            'name' => 'Rénovation maçonnerie',
            'description' => 'Rénovation et restauration de maçonnerie existante',
            'parent_id' => $maconnerie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Carrelage maçonnerie'],[
            'name' => 'Carrelage maçonnerie',
            'description' => 'Pose de carrelage et revêtements',
            'parent_id' => $maconnerie->id,
        ]);
        
        $jardinage = Category::firstOrCreate(['name' => 'Jardinage'],[
            'name' => 'Jardinage',
            'description' => 'Services d\'aménagement et entretien de jardins',
        ]);
        
        // Sous-catégories de Jardinage
        Category::firstOrCreate(['name' => 'Entretien jardin'],[
            'name' => 'Entretien jardin',
            'description' => 'Entretien régulier des espaces verts',
            'parent_id' => $jardinage->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Aménagement paysager'],[
            'name' => 'Aménagement paysager',
            'description' => 'Conception et aménagement d\'espaces verts',
            'parent_id' => $jardinage->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Élagage'],[
            'name' => 'Élagage',
            'description' => 'Taille et élagage d\'arbres et arbustes',
            'parent_id' => $jardinage->id,
        ]);
        
        $nettoyage = Category::firstOrCreate(['name' => 'Nettoyage'],[
            'name' => 'Nettoyage',
            'description' => 'Services de nettoyage professionnel',
        ]);
        
        // Sous-catégories de Nettoyage
        Category::firstOrCreate(['name' => 'Nettoyage résidentiel'],[
            'name' => 'Nettoyage résidentiel',
            'description' => 'Nettoyage de maisons et appartements',
            'parent_id' => $nettoyage->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Nettoyage commercial'],[
            'name' => 'Nettoyage commercial',
            'description' => 'Nettoyage de bureaux et locaux commerciaux',
            'parent_id' => $nettoyage->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Nettoyage après travaux'],[
            'name' => 'Nettoyage après travaux',
            'description' => 'Nettoyage spécialisé après rénovation',
            'parent_id' => $nettoyage->id,
        ]);
        
        $serrurerie = Category::firstOrCreate(['name' => 'Serrurerie'],[
            'name' => 'Serrurerie',
            'description' => 'Services de serrurerie et sécurité',
        ]);
        
        // Sous-catégories de Serrurerie
        Category::firstOrCreate(['name' => 'Dépannage serrurerie'],[
            'name' => 'Dépannage serrurerie',
            'description' => 'Ouverture de portes et dépannage d\'urgence',
            'parent_id' => $serrurerie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Installation serrures'],[
            'name' => 'Installation serrures',
            'description' => 'Installation et remplacement de serrures',
            'parent_id' => $serrurerie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Blindage'],[
            'name' => 'Blindage',
            'description' => 'Blindage de portes et sécurisation',
            'parent_id' => $serrurerie->id,
        ]);
        
        $carrelage = Category::firstOrCreate(['name' => 'Carrelage'],[
            'name' => 'Carrelage',
            'description' => 'Pose et rénovation de carrelage',
        ]);
        
        // Sous-catégories de Carrelage
        Category::firstOrCreate(['name' => 'Pose carrelage'],[
            'name' => 'Pose carrelage',
            'description' => 'Pose de carrelage sol et mur',
            'parent_id' => $carrelage->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Faïence'],[
            'name' => 'Faïence',
            'description' => 'Pose de faïence et revêtements muraux',
            'parent_id' => $carrelage->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Mosaïque'],[
            'name' => 'Mosaïque',
            'description' => 'Création et pose de mosaïques décoratives',
            'parent_id' => $carrelage->id,
        ]);
        
        $vitrerie = Category::firstOrCreate(['name' => 'Vitrerie'],[
            'name' => 'Vitrerie',
            'description' => 'Services de vitrerie et miroiterie',
        ]);
        
        // Sous-catégories de Vitrerie
        Category::firstOrCreate(['name' => 'Remplacement vitres'],[
            'name' => 'Remplacement vitres',
            'description' => 'Remplacement de vitres cassées',
            'parent_id' => $vitrerie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Double vitrage'],[
            'name' => 'Double vitrage',
            'description' => 'Installation de double vitrage',
            'parent_id' => $vitrerie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Miroiterie'],[
            'name' => 'Miroiterie',
            'description' => 'Pose et découpe de miroirs',
            'parent_id' => $vitrerie->id,
        ]);
        
        $toiture = Category::firstOrCreate(['name' => 'Toiture'],[
            'name' => 'Toiture',
            'description' => 'Services de couverture et toiture',
        ]);
        
        // Sous-catégories de Toiture
        Category::firstOrCreate(['name' => 'Réfection toiture'],[
            'name' => 'Réfection toiture',
            'description' => 'Rénovation complète de toiture',
            'parent_id' => $toiture->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Étanchéité'],[
            'name' => 'Étanchéité',
            'description' => 'Travaux d\'étanchéité de toiture',
            'parent_id' => $toiture->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Gouttières'],[
            'name' => 'Gouttières',
            'description' => 'Installation et entretien de gouttières',
            'parent_id' => $toiture->id,
        ]);
        
        $transport = Category::firstOrCreate(['name' => 'Transport'],[
            'name' => 'Transport',
            'description' => 'Services de transport et déménagement',
        ]);
        
        // Sous-catégories de Transport
        Category::firstOrCreate(['name' => 'Déménagement'],[
            'name' => 'Déménagement',
            'description' => 'Services de déménagement résidentiel et commercial',
            'parent_id' => $transport->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Transport de marchandises'],[
            'name' => 'Transport de marchandises',
            'description' => 'Transport de biens et marchandises',
            'parent_id' => $transport->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Livraison'],[
            'name' => 'Livraison',
            'description' => 'Services de livraison express et programmée',
            'parent_id' => $transport->id,
        ]);
        
        $evenementiel = Category::firstOrCreate(['name' => 'Événementiel'],[
            'name' => 'Événementiel',
            'description' => 'Organisation d\'événements et festivités',
        ]);
        
        // Sous-catégories d'Événementiel
        Category::firstOrCreate(['name' => 'Mariage'],[
            'name' => 'Mariage',
            'description' => 'Organisation de mariages et cérémonies',
            'parent_id' => $evenementiel->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Événements d\'entreprise'],[
            'name' => 'Événements d\'entreprise',
            'description' => 'Organisation d\'événements professionnels',
            'parent_id' => $evenementiel->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Anniversaires'],[
            'name' => 'Anniversaires',
            'description' => 'Organisation de fêtes d\'anniversaire',
            'parent_id' => $evenementiel->id,
        ]);
        
        $coiffure_beaute = Category::firstOrCreate(['name' => 'Coiffure & Beauté'],[
            'name' => 'Coiffure & Beauté',
            'description' => 'Services de coiffure et soins esthétiques',
        ]);
        
        // Sous-catégories de Coiffure & Beauté
        Category::firstOrCreate(['name' => 'Coiffure'],[
            'name' => 'Coiffure',
            'description' => 'Services de coiffure et coupe',
            'parent_id' => $coiffure_beaute->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Esthétique'],[
            'name' => 'Esthétique',
            'description' => 'Soins esthétiques et beauté',
            'parent_id' => $coiffure_beaute->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Manucure'],[
            'name' => 'Manucure',
            'description' => 'Soins des ongles et nail art',
            'parent_id' => $coiffure_beaute->id,
        ]);
        
        $mecanique = Category::firstOrCreate(['name' => 'Mécanique'],[
            'name' => 'Mécanique',
            'description' => 'Services de mécanique automobile et industrielle',
        ]);
        
        // Sous-catégories de Mécanique
        Category::firstOrCreate(['name' => 'Mécanique automobile'],[
            'name' => 'Mécanique automobile',
            'description' => 'Réparation et entretien de véhicules',
            'parent_id' => $mecanique->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Carrosserie'],[
            'name' => 'Carrosserie',
            'description' => 'Réparation de carrosserie automobile',
            'parent_id' => $mecanique->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Mécanique industrielle'],[
            'name' => 'Mécanique industrielle',
            'description' => 'Maintenance d\'équipements industriels',
            'parent_id' => $mecanique->id,
        ]);
        
        $renovation = Category::firstOrCreate(['name' => 'Rénovation'],[
            'name' => 'Rénovation',
            'description' => 'Services de rénovation et restauration',
        ]);
        
        // Sous-catégories de Rénovation
        Category::firstOrCreate(['name' => 'Rénovation intérieure'],[
            'name' => 'Rénovation intérieure',
            'description' => 'Rénovation d\'espaces intérieurs',
            'parent_id' => $renovation->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Rénovation extérieure'],[
            'name' => 'Rénovation extérieure',
            'description' => 'Rénovation de façades et extérieurs',
            'parent_id' => $renovation->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Restauration patrimoine'],[
            'name' => 'Restauration patrimoine',
            'description' => 'Restauration de bâtiments historiques',
            'parent_id' => $renovation->id,
        ]);
        
        $decoration = Category::firstOrCreate(['name' => 'Décoration'],[
            'name' => 'Décoration',
            'description' => 'Services de décoration d\'intérieur',
        ]);
        
        // Sous-catégories de Décoration
        Category::firstOrCreate(['name' => 'Décoration intérieure'],[
            'name' => 'Décoration intérieure',
            'description' => 'Aménagement et décoration d\'espaces',
            'parent_id' => $decoration->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Home staging'],[
            'name' => 'Home staging',
            'description' => 'Mise en valeur immobilière',
            'parent_id' => $decoration->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Feng shui'],[
            'name' => 'Feng shui',
            'description' => 'Harmonisation des espaces selon le feng shui',
            'parent_id' => $decoration->id,
        ]);
        
        $photographie = Category::firstOrCreate(['name' => 'Photographie'],[
            'name' => 'Photographie',
            'description' => 'Services de photographie professionnelle',
        ]);
        
        // Sous-catégories de Photographie
        Category::firstOrCreate(['name' => 'Portrait'],[
            'name' => 'Portrait',
            'description' => 'Photographie de portrait et famille',
            'parent_id' => $photographie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Événementiel photo'],[
            'name' => 'Événementiel photo',
            'description' => 'Photographie d\'événements',
            'parent_id' => $photographie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Immobilier photo'],[
            'name' => 'Immobilier photo',
            'description' => 'Photographie immobilière',
            'parent_id' => $photographie->id,
        ]);
        
        $securite = Category::firstOrCreate(['name' => 'Sécurité'],[
            'name' => 'Sécurité',
            'description' => 'Services de sécurité et surveillance',
        ]);
        
        // Sous-catégories de Sécurité
        Category::firstOrCreate(['name' => 'Surveillance'],[
            'name' => 'Surveillance',
            'description' => 'Services de surveillance et gardiennage',
            'parent_id' => $securite->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Alarme'],[
            'name' => 'Alarme',
            'description' => 'Installation de systèmes d\'alarme',
            'parent_id' => $securite->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Vidéosurveillance'],[
            'name' => 'Vidéosurveillance',
            'description' => 'Installation de caméras de surveillance',
            'parent_id' => $securite->id,
        ]);
        
        $boulangerie_patisserie = Category::firstOrCreate(['name' => 'Boulangerie & Pâtisserie'],[
            'name' => 'Boulangerie & Pâtisserie',
            'description' => 'Services de boulangerie et pâtisserie',
        ]);
        
        // Sous-catégories de Boulangerie & Pâtisserie
        Category::firstOrCreate(['name' => 'Boulangerie'],[
            'name' => 'Boulangerie',
            'description' => 'Fabrication de pain et viennoiseries',
            'parent_id' => $boulangerie_patisserie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Pâtisserie'],[
            'name' => 'Pâtisserie',
            'description' => 'Création de gâteaux et desserts',
            'parent_id' => $boulangerie_patisserie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Traiteur sucré'],[
            'name' => 'Traiteur sucré',
            'description' => 'Services de traiteur pour desserts',
            'parent_id' => $boulangerie_patisserie->id,
        ]);
        
        $traduction = Category::firstOrCreate(['name' => 'Traduction'],[
            'name' => 'Traduction',
            'description' => 'Services de traduction et interprétariat',
        ]);
        
        // Sous-catégories de Traduction
        Category::firstOrCreate(['name' => 'Traduction écrite'],[
            'name' => 'Traduction écrite',
            'description' => 'Traduction de documents écrits',
            'parent_id' => $traduction->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Interprétariat'],[
            'name' => 'Interprétariat',
            'description' => 'Services d\'interprétation orale',
            'parent_id' => $traduction->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Localisation'],[
            'name' => 'Localisation',
            'description' => 'Adaptation culturelle de contenus',
            'parent_id' => $traduction->id,
        ]);
        
        $conseil_juridique = Category::firstOrCreate(['name' => 'Conseil juridique'],[
            'name' => 'Conseil juridique',
            'description' => 'Services de conseil et assistance juridique',
        ]);
        
        // Sous-catégories de Conseil juridique
        Category::firstOrCreate(['name' => 'Droit des affaires'],[
            'name' => 'Droit des affaires',
            'description' => 'Conseil en droit commercial et des sociétés',
            'parent_id' => $conseil_juridique->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Droit immobilier'],[
            'name' => 'Droit immobilier',
            'description' => 'Conseil en transactions immobilières',
            'parent_id' => $conseil_juridique->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Droit de la famille'],[
            'name' => 'Droit de la famille',
            'description' => 'Conseil en droit familial',
            'parent_id' => $conseil_juridique->id,
        ]);
        
        $formation = Category::firstOrCreate(['name' => 'Formation'],[
            'name' => 'Formation',
            'description' => 'Services de formation et enseignement',
        ]);
        
        // Sous-catégories de Formation
        Category::firstOrCreate(['name' => 'Formation professionnelle'],[
            'name' => 'Formation professionnelle',
            'description' => 'Formation continue et développement des compétences',
            'parent_id' => $formation->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Cours particuliers'],[
            'name' => 'Cours particuliers',
            'description' => 'Soutien scolaire et cours individuels',
            'parent_id' => $formation->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Formation en ligne'],[
            'name' => 'Formation en ligne',
            'description' => 'Cours et formations à distance',
            'parent_id' => $formation->id,
        ]);
        
        $consulting_business = Category::firstOrCreate(['name' => 'Consulting business'],[
            'name' => 'Consulting business',
            'description' => 'Services de conseil en entreprise',
        ]);
        
        // Sous-catégories de Consulting business
        Category::firstOrCreate(['name' => 'Stratégie d\'entreprise'],[
            'name' => 'Stratégie d\'entreprise',
            'description' => 'Conseil en stratégie et développement',
            'parent_id' => $consulting_business->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Gestion financière'],[
            'name' => 'Gestion financière',
            'description' => 'Conseil en gestion et finance',
            'parent_id' => $consulting_business->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Ressources humaines'],[
            'name' => 'Ressources humaines',
            'description' => 'Conseil en gestion des ressources humaines',
            'parent_id' => $consulting_business->id,
        ]);
        
        $musique_spectacle = Category::firstOrCreate(['name' => 'Musique et Spectacle'],[
            'name' => 'Musique et Spectacle',
            'description' => 'Services artistiques et spectacles',
        ]);
        
        // Sous-catégories de Musique et Spectacle
        Category::firstOrCreate(['name' => 'Musiciens'],[
            'name' => 'Musiciens',
            'description' => 'Prestations musicales et concerts',
            'parent_id' => $musique_spectacle->id,
        ]);
        
        Category::firstOrCreate(['name' => 'DJ'],[
            'name' => 'DJ',
            'description' => 'Services de DJ pour événements',
            'parent_id' => $musique_spectacle->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Spectacle vivant'],[
            'name' => 'Spectacle vivant',
            'description' => 'Théâtre, danse et performances',
            'parent_id' => $musique_spectacle->id,
        ]);
        
        $sport_coaching = Category::firstOrCreate(['name' => 'Sport et Coaching'],[
            'name' => 'Sport et Coaching',
            'description' => 'Services sportifs et coaching personnel',
        ]);
        
        // Sous-catégories de Sport et Coaching
        Category::firstOrCreate(['name' => 'Coach sportif'],[
            'name' => 'Coach sportif',
            'description' => 'Entraînement personnel et fitness',
            'parent_id' => $sport_coaching->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Cours collectifs'],[
            'name' => 'Cours collectifs',
            'description' => 'Cours de sport en groupe',
            'parent_id' => $sport_coaching->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Coaching de vie'],[
            'name' => 'Coaching de vie',
            'description' => 'Accompagnement personnel et professionnel',
            'parent_id' => $sport_coaching->id,
        ]);
        
        $restauration_traiteur = Category::firstOrCreate(['name' => 'Restauration et Traiteur'],[
            'name' => 'Restauration et Traiteur',
            'description' => 'Services de restauration et traiteur',
        ]);
        
        // Sous-catégories de Restauration et Traiteur
        Category::firstOrCreate(['name' => 'Traiteur salé'],[
            'name' => 'Traiteur salé',
            'description' => 'Services de traiteur pour plats salés',
            'parent_id' => $restauration_traiteur->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Chef à domicile'],[
            'name' => 'Chef à domicile',
            'description' => 'Services de chef cuisinier à domicile',
            'parent_id' => $restauration_traiteur->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Livraison repas'],[
            'name' => 'Livraison repas',
            'description' => 'Services de livraison de repas',
            'parent_id' => $restauration_traiteur->id,
        ]);
        
        $vente_vehicule = Category::firstOrCreate(['name' => 'Vente véhicule'],[
            'name' => 'Vente véhicule',
            'description' => 'Vente et achat de véhicules',
        ]);
        
        // Sous-catégories de Vente véhicule
        Category::firstOrCreate(['name' => 'Voitures d\'occasion'],[
            'name' => 'Voitures d\'occasion',
            'description' => 'Vente de véhicules d\'occasion',
            'parent_id' => $vente_vehicule->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Véhicules neufs'],[
            'name' => 'Véhicules neufs',
            'description' => 'Vente de véhicules neufs',
            'parent_id' => $vente_vehicule->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Motos et scooters'],[
            'name' => 'Motos et scooters',
            'description' => 'Vente de deux-roues motorisés',
            'parent_id' => $vente_vehicule->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Pavage'],[
            'name' => 'Pavage',
            'description' => 'Installation de pavés et dallages',
            'parent_id' => $maconnerie->id,
            'description' => 'Services de nettoyage pour particuliers et résidences',
            'parent_id' => $nettoyage->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Nettoyage commercial'],[
            'name' => 'Nettoyage commercial',
            'description' => 'Services de nettoyage pour entreprises et commerces',
            'parent_id' => $nettoyage->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Nettoyage spécialisé'],[
            'name' => 'Nettoyage spécialisé',
            'description' => 'Services de nettoyage spécifiques (après sinistre, fin de chantier, etc.)',
            'parent_id' => $nettoyage->id,
        ]);
        
        $serrurerie = Category::firstOrCreate(['name' => 'Serrurerie'],[
            'name' => 'Serrurerie',
            'description' => 'Services de serrurerie et sécurité',
        ]);
        
        // Sous-catégories de Serrurerie
        Category::firstOrCreate(['name' => 'Dépannage serrurerie'],[
            'name' => 'Dépannage serrurerie',
            'description' => 'Services d\'urgence et dépannage en serrurerie',
            'parent_id' => $serrurerie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Installation de serrures'],[
            'name' => 'Installation de serrures',
            'description' => 'Installation et remplacement de serrures et systèmes de sécurité',
            'parent_id' => $serrurerie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Coffres-forts'],[
            'name' => 'Coffres-forts',
            'description' => 'Installation et ouverture de coffres-forts',
            'parent_id' => $serrurerie->id,
        ]);
        
        $carrelage = Category::firstOrCreate(['name' => 'Carrelage'],[
            'name' => 'Carrelage',
            'description' => 'Services de pose et réparation de carrelage',
        ]);
        
        // Sous-catégories de Carrelage
        Category::firstOrCreate(['name' => 'Pose de carrelage'],[
            'name' => 'Pose de carrelage',
            'description' => 'Installation de carrelage mural et au sol',
            'parent_id' => $carrelage->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Réparation de carrelage'],[
            'name' => 'Réparation de carrelage',
            'description' => 'Réparation et remplacement de carreaux endommagés',
            'parent_id' => $carrelage->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Mosaïque'],[
            'name' => 'Mosaïque',
            'description' => 'Création et pose de mosaïques décoratives',
            'parent_id' => $carrelage->id,
        ]);
        
        $vitrerie = Category::firstOrCreate(['name' => 'Vitrerie'],[
            'name' => 'Vitrerie',
            'description' => 'Services de vitrerie et miroiterie',
        ]);
        
        // Sous-catégories de Vitrerie
        Category::firstOrCreate(['name' => 'Remplacement de vitres'],[
            'name' => 'Remplacement de vitres',
            'description' => 'Remplacement de vitres cassées ou endommagées',
            'parent_id' => $vitrerie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Installation de fenêtres'],[
            'name' => 'Installation de fenêtres',
            'description' => 'Installation de fenêtres et baies vitrées',
            'parent_id' => $vitrerie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Miroiterie'],[
            'name' => 'Miroiterie',
            'description' => 'Fabrication et installation de miroirs sur mesure',
            'parent_id' => $vitrerie->id,
        ]);
        
        $toiture = Category::firstOrCreate(['name' => 'Toiture'],[
            'name' => 'Toiture',
            'description' => 'Services de couverture et réparation de toiture',
        ]);
        
        // Sous-catégories de Toiture
        Category::firstOrCreate(['name' => 'Réparation de toiture'],[
            'name' => 'Réparation de toiture',
            'description' => 'Réparation de fuites et dommages sur toiture',
            'parent_id' => $toiture->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Installation de toiture'],[
            'name' => 'Installation de toiture',
            'description' => 'Installation de nouvelles toitures',
            'parent_id' => $toiture->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Nettoyage de toiture'],[
            'name' => 'Nettoyage de toiture',
            'description' => 'Nettoyage et démoussage de toitures',
            'parent_id' => $toiture->id,
        ]);
        
        $transport = Category::firstOrCreate(['name' => 'Transport'],[
            'name' => 'Transport',
            'description' => 'Services de transport et livraison',
        ]);
        
        // Sous-catégories de Transport
        Category::firstOrCreate(['name' => 'Transport de personnes'],[
            'name' => 'Transport de personnes',
            'description' => 'Services de transport de personnes et VTC',
            'parent_id' => $transport->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Déménagement'],[
            'name' => 'Déménagement',
            'description' => 'Services de déménagement et transport de mobilier',
            'parent_id' => $transport->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Livraison'],[
            'name' => 'Livraison',
            'description' => 'Services de livraison de colis et marchandises',
            'parent_id' => $transport->id,
        ]);
        
        $evenementiel = Category::firstOrCreate(['name' => 'Événementiel'],[
            'name' => 'Événementiel',
            'description' => 'Services d\'organisation d\'événements',
        ]);
        
        // Sous-catégories d'Événementiel
        Category::firstOrCreate(['name' => 'Mariage'],[
            'name' => 'Mariage',
            'description' => 'Organisation et coordination de mariages',
            'parent_id' => $evenementiel->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Événements d\'entreprise'],[
            'name' => 'Événements d\'entreprise',
            'description' => 'Organisation de séminaires, conférences et événements professionnels',
            'parent_id' => $evenementiel->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Fêtes privées'],[
            'name' => 'Fêtes privées',
            'description' => 'Organisation d\'anniversaires et célébrations privées',
            'parent_id' => $evenementiel->id,
        ]);
        
        $coiffure_beaute = Category::firstOrCreate(['name' => 'Coiffure & Beauté'],[
            'name' => 'Coiffure & Beauté',
            'description' => 'Services de coiffure et soins esthétiques',
        ]);
        
        // Sous-catégories de Coiffure & Beauté
        Category::firstOrCreate(['name' => 'Coiffure'],[
            'name' => 'Coiffure',
            'description' => 'Services de coupe, coloration et coiffure',
            'parent_id' => $coiffure_beaute->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Soins esthétiques'],[
            'name' => 'Soins esthétiques',
            'description' => 'Services de soins du visage et du corps',
            'parent_id' => $coiffure_beaute->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Maquillage'],[
            'name' => 'Maquillage',
            'description' => 'Services de maquillage professionnel',
            'parent_id' => $coiffure_beaute->id,
        ]);
        
        $mecanique = Category::firstOrCreate(['name' => 'Mécanique'],[
            'name' => 'Mécanique',
            'description' => 'Services de mécanique et réparation automobile',
        ]);
        
        // Sous-catégories de Mécanique
        Category::firstOrCreate(['name' => 'Réparation automobile'],[
            'name' => 'Réparation automobile',
            'description' => 'Services de réparation et entretien de véhicules',
            'parent_id' => $mecanique->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Diagnostic'],[
            'name' => 'Diagnostic',
            'description' => 'Services de diagnostic et détection de pannes',
            'parent_id' => $mecanique->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Carrosserie'],[
            'name' => 'Carrosserie',
            'description' => 'Services de réparation de carrosserie et peinture',
            'parent_id' => $mecanique->id,
        ]);
        
        $renovation = Category::firstOrCreate(['name' => 'Rénovation'],[
            'name' => 'Rénovation',
            'description' => 'Services de rénovation et amélioration de l\'habitat',
        ]);
        
        // Sous-catégories de Rénovation
        Category::firstOrCreate(['name' => 'Rénovation complète'],[
            'name' => 'Rénovation complète',
            'description' => 'Services de rénovation totale de l\'habitat',
            'parent_id' => $renovation->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Rénovation partielle'],[
            'name' => 'Rénovation partielle',
            'description' => 'Services de rénovation de pièces spécifiques',
            'parent_id' => $renovation->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Isolation'],[
            'name' => 'Isolation',
            'description' => 'Services d\'isolation thermique et phonique',
            'parent_id' => $renovation->id,
        ]);
        
        $decoration = Category::firstOrCreate(['name' => 'Décoration'],[
            'name' => 'Décoration',
            'description' => 'Services de décoration d\'intérieur et d\'extérieur',
        ]);
        
        // Sous-catégories de Décoration
        Category::firstOrCreate(['name' => 'Décoration intérieure'],[
            'name' => 'Décoration intérieure',
            'description' => 'Services de décoration pour l\'intérieur de la maison',
            'parent_id' => $decoration->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Décoration extérieure'],[
            'name' => 'Décoration extérieure',
            'description' => 'Services de décoration pour l\'extérieur de la maison',
            'parent_id' => $decoration->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Home staging'],[
            'name' => 'Home staging',
            'description' => 'Services de mise en valeur de biens immobiliers',
            'parent_id' => $decoration->id,
        ]);
        
        $photographie = Category::firstOrCreate(['name' => 'Photographie'],[
            'name' => 'Photographie',
            'description' => 'Services de photographie professionnelle',
        ]);
        
        // Sous-catégories de Photographie
        Category::firstOrCreate(['name' => 'Photographie événementielle'],[
            'name' => 'Photographie événementielle',
            'description' => 'Services de photographie pour les événements',
            'parent_id' => $photographie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Photographie de portrait'],[
            'name' => 'Photographie de portrait',
            'description' => 'Services de photographie de portrait',
            'parent_id' => $photographie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Photographie immobilière'],[
            'name' => 'Photographie immobilière',
            'description' => 'Services de photographie pour l\'immobilier',
            'parent_id' => $photographie->id,
        ]);
        
        $securite = Category::firstOrCreate(['name' => 'Sécurité'],[
            'name' => 'Sécurité',
            'description' => 'Services de sécurité et surveillance',
        ]);
        
        // Sous-catégories de Sécurité
        Category::firstOrCreate(['name' => 'Systèmes d\'alarme'],[
            'name' => 'Systèmes d\'alarme',
            'description' => 'Installation et maintenance de systèmes d\'alarme',
            'parent_id' => $securite->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Vidéosurveillance'],[
            'name' => 'Vidéosurveillance',
            'description' => 'Installation et maintenance de systèmes de vidéosurveillance',
            'parent_id' => $securite->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Gardiennage'],[
            'name' => 'Gardiennage',
            'description' => 'Services de gardiennage et de surveillance',
            'parent_id' => $securite->id,
        ]);
        
        $boulangerie = Category::firstOrCreate(['name' => 'Boulangerie & Pâtisserie'],[
            'name' => 'Boulangerie & Pâtisserie',
            'description' => 'Services de boulangerie et pâtisserie',
        ]);
        
        // Sous-catégories de Boulangerie & Pâtisserie
        Category::firstOrCreate(['name' => 'Pains et viennoiseries'],[
            'name' => 'Pains et viennoiseries',
            'description' => 'Services de fabrication de pains et viennoiseries',
            'parent_id' => $boulangerie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Pâtisseries'],[
            'name' => 'Pâtisseries',
            'description' => 'Services de fabrication de pâtisseries',
            'parent_id' => $boulangerie->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Gâteaux sur mesure'],[
            'name' => 'Gâteaux sur mesure',
            'description' => 'Services de création de gâteaux personnalisés',
            'parent_id' => $boulangerie->id,
        ]);
        
        $traduction = Category::firstOrCreate(['name' => 'Traduction'],[
            'name' => 'Traduction',
            'description' => 'Services de traduction et interprétation',
        ]);
        
        // Sous-catégories de Traduction
        Category::firstOrCreate(['name' => 'Traduction de documents'],[
            'name' => 'Traduction de documents',
            'description' => 'Services de traduction de documents écrits',
            'parent_id' => $traduction->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Interprétation'],[
            'name' => 'Interprétation',
            'description' => 'Services d\'interprétation orale',
            'parent_id' => $traduction->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Traduction technique'],[
            'name' => 'Traduction technique',
            'description' => 'Services de traduction de documents techniques',
            'parent_id' => $traduction->id,
        ]);
        
        $conseil_juridique = Category::firstOrCreate(['name' => 'Conseil juridique'],[
            'name' => 'Conseil juridique',
            'description' => 'Services de conseil juridique',
        ]);
        
        // Sous-catégories de Conseil juridique
        Category::firstOrCreate(['name' => 'Droit des affaires'],[
            'name' => 'Droit des affaires',
            'description' => 'Services de conseil en droit des affaires',
            'parent_id' => $conseil_juridique->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Droit immobilier'],[
            'name' => 'Droit immobilier',
            'description' => 'Services de conseil en droit immobilier',
            'parent_id' => $conseil_juridique->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Droit de la famille'],[
            'name' => 'Droit de la famille',
            'description' => 'Services de conseil en droit de la famille',
            'parent_id' => $conseil_juridique->id,
        ]);
        
        $formation = Category::firstOrCreate(['name' => 'Formation'],[
            'name' => 'Formation',
            'description' => 'Services de formation et cours particuliers',
        ]);
        
        // Sous-catégories de Formation
        Category::firstOrCreate(['name' => 'Formation professionnelle'],[
            'name' => 'Formation professionnelle',
            'description' => 'Services de formation pour les professionnels',
            'parent_id' => $formation->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Cours particuliers'],[
            'name' => 'Cours particuliers',
            'description' => 'Services de cours particuliers',
            'parent_id' => $formation->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Coaching scolaire'],[
            'name' => 'Coaching scolaire',
            'description' => 'Services de coaching et soutien scolaire',
            'parent_id' => $formation->id,
        ]);
        
        $consulting = Category::firstOrCreate(['name' => 'Consulting business'],[
            'name' => 'Consulting business',
            'description' => 'Services de conseil en entreprise et stratégie',
        ]);
        
        // Sous-catégories de Consulting business
        Category::firstOrCreate(['name' => 'Stratégie d\'entreprise'],[
            'name' => 'Stratégie d\'entreprise',
            'description' => 'Services de conseil en stratégie d\'entreprise',
            'parent_id' => $consulting->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Gestion financière'],[
            'name' => 'Gestion financière',
            'description' => 'Services de conseil en gestion financière',
            'parent_id' => $consulting->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Ressources humaines'],[
            'name' => 'Ressources humaines',
            'description' => 'Services de conseil en ressources humaines',
            'parent_id' => $consulting->id,
        ]);
        
        $musique = Category::firstOrCreate(['name' => 'Musique et Spectacle'],[
            'name' => 'Musique et Spectacle',
            'description' => 'Services liés à la musique et au spectacle',
        ]);
        
        // Sous-catégories de Musique et Spectacle
        Category::firstOrCreate(['name' => 'Animation musicale'],[
            'name' => 'Animation musicale',
            'description' => 'Services d\'animation musicale pour événements',
            'parent_id' => $musique->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Production musicale'],[
            'name' => 'Production musicale',
            'description' => 'Services de production et enregistrement musical',
            'parent_id' => $musique->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Spectacles vivants'],[
            'name' => 'Spectacles vivants',
            'description' => 'Services d\'organisation de spectacles vivants',
            'parent_id' => $musique->id,
        ]);
        
        $sport = Category::firstOrCreate(['name' => 'Sport et Coaching'],[
            'name' => 'Sport et Coaching',
            'description' => 'Services de coaching sportif et bien-être',
        ]);
        
        // Sous-catégories de Sport et Coaching
        Category::firstOrCreate(['name' => 'Coaching sportif'],[
            'name' => 'Coaching sportif',
            'description' => 'Services de coaching sportif personnalisé',
            'parent_id' => $sport->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Yoga et méditation'],[
            'name' => 'Yoga et méditation',
            'description' => 'Services de cours de yoga et méditation',
            'parent_id' => $sport->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Nutrition sportive'],[
            'name' => 'Nutrition sportive',
            'description' => 'Services de conseil en nutrition pour sportifs',
            'parent_id' => $sport->id,
        ]);
        
        $restauration = Category::firstOrCreate(['name' => 'Restauration et Traiteur'],[
            'name' => 'Restauration et Traiteur',
            'description' => 'Services de restauration et traiteur',
        ]);
        
        // Sous-catégories de Restauration et Traiteur
        Category::firstOrCreate(['name' => 'Traiteur événementiel'],[
            'name' => 'Traiteur événementiel',
            'description' => 'Services de traiteur pour événements',
            'parent_id' => $restauration->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Chef à domicile'],[
            'name' => 'Chef à domicile',
            'description' => 'Services de chef cuisinier à domicile',
            'parent_id' => $restauration->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Food truck'],[
            'name' => 'Food truck',
            'description' => 'Services de restauration mobile',
            'parent_id' => $restauration->id,
        ]);
        
        $dev_mobile = Category::firstOrCreate(['name' => 'Développement Mobile'],[
            'name' => 'Développement Mobile',
            'description' => 'Services de développement d\'applications mobiles',
        ]);
        
        // Sous-catégories de Développement Mobile
        Category::firstOrCreate(['name' => 'Applications iOS'],[
            'name' => 'Applications iOS',
            'description' => 'Développement d\'applications pour iPhone et iPad',
            'parent_id' => $dev_mobile->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Applications Android'],[
            'name' => 'Applications Android',
            'description' => 'Développement d\'applications pour appareils Android',
            'parent_id' => $dev_mobile->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Applications hybrides'],[
            'name' => 'Applications hybrides',
            'description' => 'Développement d\'applications multiplateformes',
            'parent_id' => $dev_mobile->id,
        ]);
        
        $vente_vehicule = Category::firstOrCreate(['name' => 'Vente véhicule'],[
            'name' => 'Vente véhicule',
            'description' => 'Services de vente de véhicules',
        ]);
        
        // Sous-catégories de Vente véhicule
        Category::firstOrCreate(['name' => 'Voitures neuves'],[
            'name' => 'Voitures neuves',
            'description' => 'Services de vente de voitures neuves',
            'parent_id' => $vente_vehicule->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Voitures d\'occasion'],[
            'name' => 'Voitures d\'occasion',
            'description' => 'Services de vente de voitures d\'occasion',
            'parent_id' => $vente_vehicule->id,
        ]);
        
        Category::firstOrCreate(['name' => 'Motos et scooters'],[
            'name' => 'Motos et scooters',
            'description' => 'Services de vente de motos et scooters',
            'parent_id' => $vente_vehicule->id,
        ]);
    }
}