@extends('layouts.app')

@section('title', 'Politique de Confidentialité')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 lg:p-12">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Politique de Confidentialité</h1>
                <p class="text-gray-500">Dernière mise à jour : {{ now()->format('d/m/Y') }}</p>
            </div>

            <div class="prose prose-lg max-w-none text-gray-600">
                <!-- Introduction -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">1. Introduction</h2>
                    <p>
                        Bienvenue sur <strong>TaPrestation</strong> (ci-après "nous", "notre" ou "l'Application"). 
                        Nous nous engageons à protéger la vie privée de nos utilisateurs et à traiter leurs données 
                        personnelles de manière responsable et transparente, conformément au Règlement Général sur 
                        la Protection des Données (RGPD) et à la loi Informatique et Libertés.
                    </p>
                    <p>
                        Cette politique de confidentialité explique comment nous collectons, utilisons, stockons et 
                        protégeons vos informations personnelles lorsque vous utilisez notre application.
                    </p>
                </section>

                <!-- Responsable du traitement -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">2. Responsable du Traitement</h2>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p><strong>TaPrestation</strong></p>
                        <p>Email : contact@taprestation.com</p>
                        <p>Adresse : [Votre adresse]</p>
                    </div>
                </section>

                <!-- Données collectées -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">3. Données Collectées</h2>
                    <p>Nous collectons les catégories de données suivantes :</p>
                    
                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">3.1 Données d'identification</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Nom et prénom</li>
                        <li>Adresse email</li>
                        <li>Numéro de téléphone</li>
                        <li>Photo de profil (optionnel)</li>
                    </ul>

                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">3.2 Données professionnelles (pour les prestataires)</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Description des services proposés</li>
                        <li>Tarifs et disponibilités</li>
                        <li>Documents de vérification (pièce d'identité, justificatifs)</li>
                        <li>Informations de paiement</li>
                    </ul>

                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">3.3 Données de localisation</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Adresse de l'entreprise/prestation</li>
                        <li>Zone d'intervention</li>
                        <li>Géolocalisation (avec votre consentement)</li>
                    </ul>

                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">3.4 Données techniques</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Adresse IP</li>
                        <li>Type de navigateur et appareil</li>
                        <li>Données de connexion et d'utilisation</li>
                    </ul>
                </section>

                <!-- Finalités -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">4. Finalités du Traitement</h2>
                    <p>Vos données sont utilisées pour :</p>
                    <ul class="list-disc list-inside space-y-2 mt-3">
                        <li><strong>Fourniture du service</strong> : Création de compte, mise en relation prestataires/clients, gestion des réservations</li>
                        <li><strong>Communication</strong> : Notifications de réservation, messages entre utilisateurs, support client</li>
                        <li><strong>Paiement</strong> : Traitement des transactions via notre prestataire de paiement sécurisé (Stripe)</li>
                        <li><strong>Amélioration</strong> : Analyse de l'utilisation pour améliorer nos services</li>
                        <li><strong>Sécurité</strong> : Prévention de la fraude et protection des utilisateurs</li>
                        <li><strong>Légal</strong> : Respect de nos obligations légales</li>
                    </ul>
                </section>

                <!-- Base légale -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">5. Base Légale du Traitement</h2>
                    <table class="w-full border-collapse border border-gray-300 mt-3">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 p-3 text-left">Finalité</th>
                                <th class="border border-gray-300 p-3 text-left">Base légale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-gray-300 p-3">Création de compte</td>
                                <td class="border border-gray-300 p-3">Exécution du contrat</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 p-3">Gestion des réservations</td>
                                <td class="border border-gray-300 p-3">Exécution du contrat</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 p-3">Newsletters marketing</td>
                                <td class="border border-gray-300 p-3">Consentement</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 p-3">Amélioration du service</td>
                                <td class="border border-gray-300 p-3">Intérêt légitime</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 p-3">Obligations fiscales</td>
                                <td class="border border-gray-300 p-3">Obligation légale</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- Partage des données -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">6. Partage des Données</h2>
                    <p>Vos données peuvent être partagées avec :</p>
                    <ul class="list-disc list-inside space-y-2 mt-3">
                        <li><strong>Autres utilisateurs</strong> : Informations de profil visibles pour la mise en relation</li>
                        <li><strong>Prestataires de paiement</strong> : Stripe pour le traitement des paiements</li>
                        <li><strong>Hébergeur</strong> : Nos serveurs sont hébergés en Europe</li>
                        <li><strong>Autorités</strong> : En cas d'obligation légale</li>
                    </ul>
                    <p class="mt-3">
                        <strong>Nous ne vendons jamais vos données personnelles à des tiers.</strong>
                    </p>
                </section>

                <!-- Conservation -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">7. Durée de Conservation</h2>
                    <ul class="list-disc list-inside space-y-2">
                        <li><strong>Compte actif</strong> : Données conservées pendant toute la durée d'utilisation</li>
                        <li><strong>Après suppression du compte</strong> : 3 ans maximum (obligations légales)</li>
                        <li><strong>Données de transaction</strong> : 10 ans (obligations comptables)</li>
                        <li><strong>Logs techniques</strong> : 1 an</li>
                    </ul>
                </section>

                <!-- Vos droits -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">8. Vos Droits (RGPD)</h2>
                    <p>Conformément au RGPD, vous disposez des droits suivants :</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-900">Droit d'accès</h4>
                            <p class="text-sm text-blue-700">Obtenir une copie de vos données</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-900">Droit de rectification</h4>
                            <p class="text-sm text-green-700">Corriger vos données inexactes</p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-red-900">Droit à l'effacement</h4>
                            <p class="text-sm text-red-700">Supprimer vos données</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-yellow-900">Droit à la portabilité</h4>
                            <p class="text-sm text-yellow-700">Récupérer vos données</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-purple-900">Droit d'opposition</h4>
                            <p class="text-sm text-purple-700">Vous opposer au traitement</p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-orange-900">Droit de limitation</h4>
                            <p class="text-sm text-orange-700">Limiter le traitement</p>
                        </div>
                    </div>
                    <p class="mt-4">
                        Pour exercer ces droits, contactez-nous à : <a href="mailto:privacy@taprestation.com" class="text-orange-600 hover:underline">privacy@taprestation.com</a>
                    </p>
                </section>

                <!-- Sécurité -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">9. Sécurité des Données</h2>
                    <p>Nous mettons en œuvre des mesures de sécurité appropriées :</p>
                    <ul class="list-disc list-inside space-y-2 mt-3">
                        <li>Chiffrement SSL/TLS pour toutes les communications</li>
                        <li>Mots de passe hashés avec bcrypt</li>
                        <li>Accès restreint aux données personnelles</li>
                        <li>Sauvegardes régulières et sécurisées</li>
                        <li>Surveillance continue des systèmes</li>
                    </ul>
                </section>

                <!-- Cookies -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">10. Cookies</h2>
                    <p>
                        Notre application utilise des cookies essentiels au fonctionnement du service 
                        (authentification, sécurité). Consultez notre 
                        <a href="{{ route('cookies') }}" class="text-orange-600 hover:underline">Politique des Cookies</a> 
                        pour plus de détails.
                    </p>
                </section>

                <!-- Modifications -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">11. Modifications</h2>
                    <p>
                        Cette politique peut être mise à jour. En cas de modification importante, 
                        nous vous en informerons par email ou notification dans l'application.
                    </p>
                </section>

                <!-- Contact -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">12. Contact</h2>
                    <div class="bg-orange-50 p-6 rounded-lg">
                        <p class="mb-2">Pour toute question concernant cette politique ou vos données :</p>
                        <p><strong>Email :</strong> <a href="mailto:privacy@taprestation.com" class="text-orange-600">privacy@taprestation.com</a></p>
                        <p class="mt-4 text-sm text-gray-600">
                            Vous pouvez également déposer une plainte auprès de la CNIL (www.cnil.fr) 
                            si vous estimez que vos droits ne sont pas respectés.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
