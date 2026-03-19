@extends('layouts.app')

@section('title', 'Mentions Légales')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-slate-800 to-blue-900 px-8 py-6">
                <h1 class="text-3xl font-bold text-white mb-2">Mentions Légales</h1>
                <p class="text-blue-200">Informations légales sur TaPrestation</p>
            </div>
            
            <div class="p-8 space-y-8">
                <!-- Éditeur du site -->
                <section>
                    <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center">
                        <i class="fas fa-building text-blue-600 mr-3"></i>
                        Éditeur du site
                    </h2>
                    <div class="bg-slate-50 rounded-xl p-6 space-y-2">
                        <p class="text-slate-700"><strong>Nom :</strong> TaPrestation</p>
                        <p class="text-slate-700"><strong>Forme juridique :</strong> [À compléter]</p>
                        <p class="text-slate-700"><strong>Capital social :</strong> [À compléter]</p>
                        <p class="text-slate-700"><strong>Siège social :</strong> [Adresse à compléter]</p>
                        <p class="text-slate-700"><strong>SIRET :</strong> [À compléter]</p>
                        <p class="text-slate-700"><strong>RCS :</strong> [À compléter]</p>
                        <p class="text-slate-700"><strong>Numéro TVA :</strong> [À compléter]</p>
                    </div>
                </section>

                <!-- Directeur de publication -->
                <section>
                    <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center">
                        <i class="fas fa-user-tie text-blue-600 mr-3"></i>
                        Directeur de la publication
                    </h2>
                    <div class="bg-slate-50 rounded-xl p-6">
                        <p class="text-slate-700">[Nom du directeur de publication à compléter]</p>
                    </div>
                </section>

                <!-- Hébergement -->
                <section>
                    <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center">
                        <i class="fas fa-server text-blue-600 mr-3"></i>
                        Hébergement
                    </h2>
                    <div class="bg-slate-50 rounded-xl p-6 space-y-2">
                        <p class="text-slate-700"><strong>Hébergeur :</strong> OVH</p>
                        <p class="text-slate-700"><strong>Adresse :</strong> 2 rue Kellermann - 59100 Roubaix - France</p>
                        <p class="text-slate-700"><strong>Téléphone :</strong> 1007</p>
                    </div>
                </section>

                <!-- Contact -->
                <section>
                    <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center">
                        <i class="fas fa-envelope text-blue-600 mr-3"></i>
                        Contact
                    </h2>
                    <div class="bg-slate-50 rounded-xl p-6 space-y-2">
                        <p class="text-slate-700"><strong>Email :</strong> contact@taprestation.com</p>
                        <p class="text-slate-700"><strong>Téléphone :</strong> [À compléter]</p>
                    </div>
                </section>

                <!-- Propriété intellectuelle -->
                <section>
                    <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center">
                        <i class="fas fa-copyright text-blue-600 mr-3"></i>
                        Propriété intellectuelle
                    </h2>
                    <div class="bg-slate-50 rounded-xl p-6">
                        <p class="text-slate-600 leading-relaxed">
                            L'ensemble du contenu de ce site (textes, images, vidéos, logos, icônes, etc.) est protégé 
                            par le droit d'auteur et le droit des marques. Toute reproduction, représentation, modification, 
                            publication ou adaptation de tout ou partie des éléments du site est interdite sans autorisation 
                            écrite préalable de TaPrestation.
                        </p>
                    </div>
                </section>

                <!-- Crédits -->
                <section>
                    <h2 class="text-xl font-semibold text-slate-800 mb-4 flex items-center">
                        <i class="fas fa-palette text-blue-600 mr-3"></i>
                        Crédits
                    </h2>
                    <div class="bg-slate-50 rounded-xl p-6 space-y-2">
                        <p class="text-slate-700"><strong>Conception et développement :</strong> TaPrestation</p>
                        <p class="text-slate-700"><strong>Icônes :</strong> Font Awesome</p>
                        <p class="text-slate-700"><strong>Polices :</strong> Bunny Fonts</p>
                    </div>
                </section>

                <div class="mt-8 pt-6 border-t border-slate-200 text-center">
                    <p class="text-slate-500 text-sm">Dernière mise à jour : {{ date('d/m/Y') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
