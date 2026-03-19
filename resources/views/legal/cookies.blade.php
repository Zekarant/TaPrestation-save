@extends('layouts.app')

@section('title', 'Politique des Cookies')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 lg:p-12">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Politique des Cookies</h1>
                <p class="text-gray-500">Dernière mise à jour : {{ now()->format('d/m/Y') }}</p>
            </div>

            <div class="prose prose-lg max-w-none text-gray-600">
                <!-- Introduction -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">1. Qu'est-ce qu'un Cookie ?</h2>
                    <p>
                        Un cookie est un petit fichier texte stocké sur votre appareil (ordinateur, smartphone, tablette) 
                        lorsque vous visitez un site web. Les cookies permettent au site de reconnaître votre appareil 
                        et de mémoriser certaines informations sur vos préférences ou actions passées.
                    </p>
                </section>

                <!-- Types de cookies -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">2. Types de Cookies Utilisés</h2>
                    
                    <div class="space-y-4">
                        <!-- Cookies essentiels -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-green-900 mb-2">
                                🔐 Cookies Essentiels (Obligatoires)
                            </h3>
                            <p class="text-green-700 text-sm mb-3">
                                Ces cookies sont nécessaires au fonctionnement de la plateforme. 
                                Ils ne peuvent pas être désactivés.
                            </p>
                            <table class="w-full text-sm">
                                <thead class="bg-green-100">
                                    <tr>
                                        <th class="p-2 text-left">Nom</th>
                                        <th class="p-2 text-left">Finalité</th>
                                        <th class="p-2 text-left">Durée</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-t border-green-200">
                                        <td class="p-2 font-mono">taprestation_session</td>
                                        <td class="p-2">Session utilisateur</td>
                                        <td class="p-2">2 heures</td>
                                    </tr>
                                    <tr class="border-t border-green-200">
                                        <td class="p-2 font-mono">XSRF-TOKEN</td>
                                        <td class="p-2">Protection CSRF</td>
                                        <td class="p-2">2 heures</td>
                                    </tr>
                                    <tr class="border-t border-green-200">
                                        <td class="p-2 font-mono">remember_web_*</td>
                                        <td class="p-2">Se souvenir de moi</td>
                                        <td class="p-2">30 jours</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Cookies fonctionnels -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-blue-900 mb-2">
                                ⚙️ Cookies Fonctionnels
                            </h3>
                            <p class="text-blue-700 text-sm mb-3">
                                Ces cookies permettent d'améliorer votre expérience en mémorisant vos préférences.
                            </p>
                            <table class="w-full text-sm">
                                <thead class="bg-blue-100">
                                    <tr>
                                        <th class="p-2 text-left">Nom</th>
                                        <th class="p-2 text-left">Finalité</th>
                                        <th class="p-2 text-left">Durée</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-t border-blue-200">
                                        <td class="p-2 font-mono">locale</td>
                                        <td class="p-2">Langue préférée</td>
                                        <td class="p-2">1 an</td>
                                    </tr>
                                    <tr class="border-t border-blue-200">
                                        <td class="p-2 font-mono">theme</td>
                                        <td class="p-2">Thème (clair/sombre)</td>
                                        <td class="p-2">1 an</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Cookies analytiques -->
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-purple-900 mb-2">
                                📊 Cookies Analytiques (avec consentement)
                            </h3>
                            <p class="text-purple-700 text-sm mb-3">
                                Ces cookies nous aident à comprendre comment les visiteurs utilisent notre site.
                            </p>
                            <table class="w-full text-sm">
                                <thead class="bg-purple-100">
                                    <tr>
                                        <th class="p-2 text-left">Nom</th>
                                        <th class="p-2 text-left">Finalité</th>
                                        <th class="p-2 text-left">Durée</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-t border-purple-200">
                                        <td class="p-2 font-mono">_ga</td>
                                        <td class="p-2">Google Analytics</td>
                                        <td class="p-2">2 ans</td>
                                    </tr>
                                    <tr class="border-t border-purple-200">
                                        <td class="p-2 font-mono">_gid</td>
                                        <td class="p-2">Google Analytics</td>
                                        <td class="p-2">24 heures</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Cookies tiers -->
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-orange-900 mb-2">
                                💳 Cookies Tiers
                            </h3>
                            <p class="text-orange-700 text-sm mb-3">
                                Cookies déposés par nos partenaires pour des services spécifiques.
                            </p>
                            <table class="w-full text-sm">
                                <thead class="bg-orange-100">
                                    <tr>
                                        <th class="p-2 text-left">Service</th>
                                        <th class="p-2 text-left">Finalité</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-t border-orange-200">
                                        <td class="p-2">Stripe</td>
                                        <td class="p-2">Paiement sécurisé</td>
                                    </tr>
                                    <tr class="border-t border-orange-200">
                                        <td class="p-2">Google Maps</td>
                                        <td class="p-2">Affichage des cartes</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- Gestion des cookies -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">3. Gérer vos Cookies</h2>
                    
                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">3.1 Via notre plateforme</h3>
                    <p>
                        Vous pouvez gérer vos préférences de cookies à tout moment en cliquant sur 
                        "Paramètres des cookies" dans le pied de page de notre site.
                    </p>

                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">3.2 Via votre navigateur</h3>
                    <p>
                        Vous pouvez également configurer votre navigateur pour accepter ou refuser les cookies :
                    </p>
                    <ul class="list-disc list-inside space-y-1 mt-2">
                        <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" class="text-orange-600 hover:underline">Google Chrome</a></li>
                        <li><a href="https://support.mozilla.org/fr/kb/activer-desactiver-cookies" target="_blank" class="text-orange-600 hover:underline">Mozilla Firefox</a></li>
                        <li><a href="https://support.apple.com/fr-fr/guide/safari/sfri11471/mac" target="_blank" class="text-orange-600 hover:underline">Safari</a></li>
                        <li><a href="https://support.microsoft.com/fr-fr/microsoft-edge/supprimer-les-cookies-dans-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" class="text-orange-600 hover:underline">Microsoft Edge</a></li>
                    </ul>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
                        <p class="text-yellow-800 text-sm">
                            <strong>⚠️ Attention :</strong> La désactivation de certains cookies peut affecter 
                            le fonctionnement de la plateforme (connexion, paiement, etc.).
                        </p>
                    </div>
                </section>

                <!-- LocalStorage -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">4. LocalStorage</h2>
                    <p>
                        En plus des cookies, nous utilisons le stockage local (localStorage) de votre navigateur 
                        pour mémoriser certaines préférences d'interface (bannières fermées, préférences d'affichage). 
                        Ces données restent sur votre appareil et ne sont pas transmises à nos serveurs.
                    </p>
                </section>

                <!-- Modifications -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">5. Modifications</h2>
                    <p>
                        Cette politique peut être mise à jour pour refléter les changements dans notre 
                        utilisation des cookies. La date de dernière mise à jour est indiquée en haut de cette page.
                    </p>
                </section>

                <!-- Contact -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">6. Contact</h2>
                    <div class="bg-orange-50 p-6 rounded-lg">
                        <p class="mb-2">Pour toute question concernant les cookies :</p>
                        <p><strong>Email :</strong> <a href="mailto:privacy@taprestation.com" class="text-orange-600">privacy@taprestation.com</a></p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
