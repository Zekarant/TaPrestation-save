@extends('layouts.app')

@section('title', 'Conditions Générales d\'Utilisation')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 lg:p-12">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Conditions Générales d'Utilisation</h1>
                <p class="text-gray-500">Dernière mise à jour : {{ now()->format('d/m/Y') }}</p>
            </div>

            <div class="prose prose-lg max-w-none text-gray-600">
                <!-- Préambule -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">1. Préambule</h2>
                    <p>
                        Les présentes Conditions Générales d'Utilisation (ci-après "CGU") régissent l'utilisation 
                        de la plateforme <strong>TaPrestation</strong> (ci-après "la Plateforme"), accessible via 
                        l'application mobile et le site web.
                    </p>
                    <p>
                        TaPrestation est une plateforme de mise en relation entre des prestataires de services 
                        professionnels et des clients à la recherche de prestations.
                    </p>
                    <p>
                        <strong>L'utilisation de la Plateforme implique l'acceptation sans réserve des présentes CGU.</strong>
                    </p>
                </section>

                <!-- Définitions -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">2. Définitions</h2>
                    <ul class="list-disc list-inside space-y-2">
                        <li><strong>"Utilisateur"</strong> : Toute personne utilisant la Plateforme</li>
                        <li><strong>"Client"</strong> : Utilisateur recherchant une prestation</li>
                        <li><strong>"Prestataire"</strong> : Utilisateur proposant des services professionnels</li>
                        <li><strong>"Service"</strong> : Prestation proposée par un Prestataire</li>
                        <li><strong>"Réservation"</strong> : Demande de prestation effectuée par un Client</li>
                        <li><strong>"Contenu"</strong> : Textes, images, vidéos publiés sur la Plateforme</li>
                    </ul>
                </section>

                <!-- Inscription -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">3. Inscription et Compte Utilisateur</h2>
                    
                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">3.1 Conditions d'inscription</h3>
                    <p>Pour créer un compte, vous devez :</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Être âgé d'au moins 18 ans</li>
                        <li>Fournir des informations exactes et complètes</li>
                        <li>Disposer d'une adresse email valide</li>
                        <li>Accepter les présentes CGU</li>
                    </ul>

                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">3.2 Compte Prestataire</h3>
                    <p>Les Prestataires doivent également :</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Être légalement autorisés à exercer leur activité</li>
                        <li>Disposer des assurances professionnelles requises</li>
                        <li>Fournir les justificatifs demandés pour la vérification</li>
                        <li>Respecter la réglementation applicable à leur profession</li>
                    </ul>

                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">3.3 Sécurité du compte</h3>
                    <p>
                        Vous êtes responsable de la confidentialité de vos identifiants. 
                        Toute activité effectuée depuis votre compte est présumée être de votre fait.
                    </p>
                </section>

                <!-- Fonctionnement -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">4. Fonctionnement de la Plateforme</h2>
                    
                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">4.1 Rôle de TaPrestation</h3>
                    <p>
                        TaPrestation agit uniquement en tant qu'<strong>intermédiaire technique</strong> 
                        pour la mise en relation entre Clients et Prestataires. Nous ne sommes pas partie 
                        au contrat conclu entre eux et ne garantissons pas la qualité des prestations.
                    </p>

                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">4.2 Réservations</h3>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Le Client effectue une demande de réservation</li>
                        <li>Le Prestataire accepte ou refuse la demande</li>
                        <li>En cas d'acceptation, les parties conviennent des modalités</li>
                        <li>Le paiement peut être effectué via la Plateforme</li>
                    </ul>

                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">4.3 Annulation</h3>
                    <p>Les conditions d'annulation sont définies par chaque Prestataire sur sa page de service.</p>
                </section>

                <!-- Obligations -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">5. Obligations des Utilisateurs</h2>
                    
                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">5.1 Obligations générales</h3>
                    <p>Tout Utilisateur s'engage à :</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Utiliser la Plateforme conformément à sa destination</li>
                        <li>Ne pas usurper l'identité d'un tiers</li>
                        <li>Ne pas publier de contenu illicite ou offensant</li>
                        <li>Respecter les droits de propriété intellectuelle</li>
                        <li>Ne pas perturber le fonctionnement de la Plateforme</li>
                    </ul>

                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">5.2 Obligations des Prestataires</h3>
                    <p>Les Prestataires s'engagent à :</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Fournir des informations exactes sur leurs services</li>
                        <li>Respecter leurs engagements envers les Clients</li>
                        <li>Disposer des qualifications nécessaires</li>
                        <li>Être en règle avec leurs obligations fiscales et sociales</li>
                        <li>Répondre rapidement aux demandes de réservation</li>
                    </ul>

                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">5.3 Obligations des Clients</h3>
                    <p>Les Clients s'engagent à :</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Fournir des informations exactes pour la réservation</li>
                        <li>Honorer leurs réservations confirmées</li>
                        <li>Payer les prestations convenues</li>
                        <li>Laisser des avis honnêtes et constructifs</li>
                    </ul>
                </section>

                <!-- Paiements -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">6. Paiements</h2>
                    
                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">6.1 Moyens de paiement</h3>
                    <p>
                        Les paiements sont traités par notre prestataire sécurisé <strong>Stripe</strong>. 
                        Les moyens acceptés incluent les cartes bancaires Visa, Mastercard et American Express.
                    </p>

                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">6.2 Commission</h3>
                    <p>
                        TaPrestation prélève une commission sur les transactions effectuées via la Plateforme. 
                        Le montant est affiché avant confirmation du paiement.
                    </p>

                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">6.3 Remboursements</h3>
                    <p>
                        Les conditions de remboursement dépendent de la politique d'annulation du Prestataire 
                        et des circonstances de l'annulation.
                    </p>
                </section>

                <!-- Contenu -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">7. Contenu et Propriété Intellectuelle</h2>
                    
                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">7.1 Contenu utilisateur</h3>
                    <p>
                        Vous conservez vos droits sur le contenu que vous publiez. En le publiant, 
                        vous accordez à TaPrestation une licence non exclusive pour l'utiliser dans 
                        le cadre du service.
                    </p>

                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">7.2 Propriété de TaPrestation</h3>
                    <p>
                        La Plateforme, son design, son code et ses fonctionnalités sont la propriété 
                        exclusive de TaPrestation et sont protégés par le droit de la propriété intellectuelle.
                    </p>
                </section>

                <!-- Responsabilité -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">8. Responsabilité</h2>
                    
                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">8.1 Limitation de responsabilité</h3>
                    <p>
                        TaPrestation ne peut être tenu responsable des litiges entre Utilisateurs, 
                        de la qualité des prestations fournies, ni des dommages indirects.
                    </p>

                    <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">8.2 Disponibilité</h3>
                    <p>
                        Nous nous efforçons d'assurer la disponibilité de la Plateforme mais ne 
                        garantissons pas un accès ininterrompu.
                    </p>
                </section>

                <!-- Sanctions -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">9. Sanctions</h2>
                    <p>
                        En cas de violation des présentes CGU, TaPrestation se réserve le droit de :
                    </p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Suspendre temporairement le compte</li>
                        <li>Supprimer définitivement le compte</li>
                        <li>Supprimer du contenu</li>
                        <li>Refuser l'inscription d'un utilisateur</li>
                    </ul>
                </section>

                <!-- Modification -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">10. Modification des CGU</h2>
                    <p>
                        Nous pouvons modifier ces CGU à tout moment. Les utilisateurs seront informés 
                        des modifications substantielles. La continuation de l'utilisation après 
                        notification vaut acceptation.
                    </p>
                </section>

                <!-- Droit applicable -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">11. Droit Applicable et Litiges</h2>
                    <p>
                        Les présentes CGU sont régies par le droit français. En cas de litige, 
                        une solution amiable sera recherchée avant toute action judiciaire.
                    </p>
                    <p class="mt-2">
                        Conformément à la réglementation, vous pouvez recourir gratuitement à un 
                        médiateur de la consommation.
                    </p>
                </section>

                <!-- Contact -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">12. Contact</h2>
                    <div class="bg-orange-50 p-6 rounded-lg">
                        <p class="mb-2">Pour toute question concernant ces CGU :</p>
                        <p><strong>Email :</strong> <a href="mailto:contact@taprestation.com" class="text-orange-600">contact@taprestation.com</a></p>
                    </div>
                </section>
            </div>

            <!-- Acceptation -->
            <div class="mt-8 p-6 bg-gray-100 rounded-lg text-center">
                <p class="text-gray-600">
                    En utilisant TaPrestation, vous confirmez avoir lu et accepté ces Conditions Générales d'Utilisation.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
