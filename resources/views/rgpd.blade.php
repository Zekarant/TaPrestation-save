@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- En-tête -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    Politique de Confidentialité & RGPD
                </h1>
                <p class="text-gray-600">
                    Dernière mise à jour : {{ date('d/m/Y') }}
                </p>
            </div>

            <!-- Contenu RGPD -->
            <div class="bg-white rounded-lg shadow-lg p-8 space-y-8">

                <!-- Section 1 -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Introduction</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore
                        et dolore magna aliqua.
                        Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                        consequat.
                        Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
                        pariatur.
                    </p>
                </section>

                <!-- Section 2 -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Données Collectées</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque habitant morbi tristique
                        senectus et netus et malesuada fames ac turpis egestas.
                    </p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li>Lorem ipsum dolor sit amet</li>
                        <li>Consectetur adipiscing elit</li>
                        <li>Sed do eiusmod tempor incididunt</li>
                        <li>Ut labore et dolore magna aliqua</li>
                    </ul>
                </section>

                <!-- Section 3 -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Utilisation des Données</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id
                        est laborum.
                        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium,
                        totam rem aperiam,
                        eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.
                    </p>
                </section>

                <!-- Section 4 -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Cookies et Technologies Similaires</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur
                        magni dolores eos qui ratione voluptatem sequi nesciunt.
                    </p>
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                        <p class="text-blue-900">
                            <strong>Note :</strong> Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet,
                            consectetur, adipisci velit.
                        </p>
                    </div>
                </section>

                <!-- Section 5 -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Vos Droits</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum
                        deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non
                        provident.
                    </p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                        <li><strong>Droit d'accès :</strong> Lorem ipsum dolor sit amet</li>
                        <li><strong>Droit de rectification :</strong> Consectetur adipiscing elit</li>
                        <li><strong>Droit à l'effacement :</strong> Sed do eiusmod tempor</li>
                        <li><strong>Droit à la portabilité :</strong> Ut labore et dolore magna</li>
                        <li><strong>Droit d'opposition :</strong> Quis nostrud exercitation</li>
                    </ul>
                </section>

                <!-- Section 6 -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Sécurité des Données</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et
                        voluptates repudiandae sint et molestiae non recusandae.
                        Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias
                        consequatur aut perferendis doloribus asperiores repellat.
                    </p>
                </section>

                <!-- Section 7 -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Conservation des Données</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod
                        maxime placeat facere possimus,
                        omnis voluptas assumenda est, omnis dolor repellendus.
                    </p>
                </section>

                <!-- Section 8 -->
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Modifications de la Politique</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae
                        consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                    </p>
                </section>

                <!-- Section Contact -->
                <section class="border-t pt-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Contact</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Pour toute question concernant cette politique de confidentialité ou pour exercer vos droits RGPD :
                    </p>
                    <div class="bg-gray-50 rounded-lg p-6">
                        <p class="text-gray-700"><strong>Email :</strong> rgpd@taprestation.fr</p>
                        <p class="text-gray-700 mt-2"><strong>Adresse :</strong> Lorem ipsum dolor sit amet, 75000 Paris,
                            France</p>
                    </div>
                </section>

            </div>

            <!-- Bouton retour -->
            <div class="mt-8 text-center">
                <a href="{{ route('home') }}"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
@endsection