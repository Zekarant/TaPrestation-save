<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Skill;

class SkillsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Compétences de développement
        $skills = [
            ['name' => 'PHP', 'description' => 'Langage de programmation côté serveur pour le développement web'],
            ['name' => 'Laravel', 'description' => 'Framework PHP moderne pour le développement web'],
            ['name' => 'JavaScript', 'description' => 'Langage de programmation pour le développement web côté client'],
            ['name' => 'React', 'description' => 'Bibliothèque JavaScript pour créer des interfaces utilisateur'],
            ['name' => 'Vue.js', 'description' => 'Framework JavaScript progressif pour construire des interfaces utilisateur'],
            ['name' => 'Angular', 'description' => 'Framework JavaScript pour le développement d\'applications web'],
            ['name' => 'Node.js', 'description' => 'Environnement d\'exécution JavaScript côté serveur'],
            ['name' => 'Python', 'description' => 'Langage de programmation polyvalent'],
            ['name' => 'Django', 'description' => 'Framework web Python de haut niveau'],
            ['name' => 'Ruby on Rails', 'description' => 'Framework d\'application web écrit en Ruby'],
            
            // Compétences de design
            ['name' => 'Photoshop', 'description' => 'Logiciel d\'édition d\'images et de design graphique'],
            ['name' => 'Illustrator', 'description' => 'Logiciel de création graphique vectorielle'],
            ['name' => 'Figma', 'description' => 'Outil de conception d\'interfaces utilisateur basé sur le web'],
            ['name' => 'Adobe XD', 'description' => 'Outil de conception et de prototypage d\'expérience utilisateur'],
            ['name' => 'Sketch', 'description' => 'Application de design numérique pour macOS'],
            
            // Compétences de marketing
            ['name' => 'SEO', 'description' => 'Optimisation pour les moteurs de recherche'],
            ['name' => 'Google Ads', 'description' => 'Plateforme publicitaire de Google'],
            ['name' => 'Facebook Ads', 'description' => 'Plateforme publicitaire de Facebook'],
            ['name' => 'Content Marketing', 'description' => 'Stratégie de marketing axée sur la création et la distribution de contenu'],
            ['name' => 'Email Marketing', 'description' => 'Stratégie de marketing utilisant l\'email comme canal de communication'],
            
            // Compétences de rédaction
            ['name' => 'Copywriting', 'description' => 'Rédaction persuasive pour la publicité et le marketing'],
            ['name' => 'Rédaction Web', 'description' => 'Création de contenu optimisé pour le web'],
            ['name' => 'Traduction Français-Anglais', 'description' => 'Traduction entre le français et l\'anglais'],
            ['name' => 'Traduction Français-Espagnol', 'description' => 'Traduction entre le français et l\'espagnol'],
            
            // Compétences vidéo et audio
            ['name' => 'Montage Vidéo', 'description' => 'Édition et assemblage de séquences vidéo'],
            ['name' => 'After Effects', 'description' => 'Logiciel de montage vidéo et d\'effets visuels'],
            ['name' => 'Premiere Pro', 'description' => 'Logiciel de montage vidéo professionnel'],
            ['name' => 'Motion Design', 'description' => 'Animation graphique et design en mouvement'],
            ['name' => 'Production Audio', 'description' => 'Enregistrement et mixage audio'],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(['name' => $skill['name']], $skill);
        }
    }
}