@extends('layouts.app')

@section('title', 'FAQ - Questions fréquentes')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-slate-900 mb-4">Questions fréquentes</h1>
            <p class="text-lg text-slate-600">Trouvez rapidement les réponses à vos questions</p>
        </div>

        <div class="space-y-4" x-data="{ open: null }">
            <!-- Question 1 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <button @click="open = open === 1 ? null : 1" class="w-full px-6 py-5 text-left flex justify-between items-center hover:bg-slate-50 transition-colors">
                    <span class="font-semibold text-slate-800">Comment créer un compte sur TaPrestation ?</span>
                    <i class="fas fa-chevron-down text-blue-600 transition-transform" :class="{ 'rotate-180': open === 1 }"></i>
                </button>
                <div x-show="open === 1" x-collapse class="px-6 pb-5">
                    <p class="text-slate-600">
                        Pour créer un compte, cliquez sur "Inscription" en haut de la page. Remplissez le formulaire avec vos informations 
                        (nom, email, mot de passe) et validez. Vous recevrez un email de confirmation pour activer votre compte.
                    </p>
                </div>
            </div>

            <!-- Question 2 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <button @click="open = open === 2 ? null : 2" class="w-full px-6 py-5 text-left flex justify-between items-center hover:bg-slate-50 transition-colors">
                    <span class="font-semibold text-slate-800">Comment devenir prestataire ?</span>
                    <i class="fas fa-chevron-down text-blue-600 transition-transform" :class="{ 'rotate-180': open === 2 }"></i>
                </button>
                <div x-show="open === 2" x-collapse class="px-6 pb-5">
                    <p class="text-slate-600">
                        Après avoir créé votre compte client, rendez-vous dans votre profil et cliquez sur "Devenir prestataire". 
                        Complétez votre profil professionnel, ajoutez vos services et attendez la validation de notre équipe. 
                        Une fois approuvé, vous pourrez commencer à recevoir des demandes de clients.
                    </p>
                </div>
            </div>

            <!-- Question 3 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <button @click="open = open === 3 ? null : 3" class="w-full px-6 py-5 text-left flex justify-between items-center hover:bg-slate-50 transition-colors">
                    <span class="font-semibold text-slate-800">Comment réserver un service ?</span>
                    <i class="fas fa-chevron-down text-blue-600 transition-transform" :class="{ 'rotate-180': open === 3 }"></i>
                </button>
                <div x-show="open === 3" x-collapse class="px-6 pb-5">
                    <p class="text-slate-600">
                        Parcourez notre catalogue de services ou recherchez un prestataire spécifique. Une fois que vous avez trouvé 
                        le service souhaité, sélectionnez une date et un créneau disponible, puis confirmez votre réservation. 
                        Vous recevrez une confirmation par email une fois la réservation acceptée par le prestataire.
                    </p>
                </div>
            </div>

            <!-- Question 4 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <button @click="open = open === 4 ? null : 4" class="w-full px-6 py-5 text-left flex justify-between items-center hover:bg-slate-50 transition-colors">
                    <span class="font-semibold text-slate-800">Les paiements sont-ils sécurisés ?</span>
                    <i class="fas fa-chevron-down text-blue-600 transition-transform" :class="{ 'rotate-180': open === 4 }"></i>
                </button>
                <div x-show="open === 4" x-collapse class="px-6 pb-5">
                    <p class="text-slate-600">
                        Oui, tous les paiements sur TaPrestation sont 100% sécurisés. Nous utilisons Stripe, le leader mondial 
                        du paiement en ligne. Vos informations bancaires ne sont jamais stockées sur nos serveurs. 
                        De plus, vous bénéficiez d'une protection acheteur en cas de problème avec votre prestation.
                    </p>
                </div>
            </div>

            <!-- Question 5 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <button @click="open = open === 5 ? null : 5" class="w-full px-6 py-5 text-left flex justify-between items-center hover:bg-slate-50 transition-colors">
                    <span class="font-semibold text-slate-800">Comment annuler une réservation ?</span>
                    <i class="fas fa-chevron-down text-blue-600 transition-transform" :class="{ 'rotate-180': open === 5 }"></i>
                </button>
                <div x-show="open === 5" x-collapse class="px-6 pb-5">
                    <p class="text-slate-600">
                        Rendez-vous dans votre espace client, section "Mes réservations". Sélectionnez la réservation à annuler 
                        et cliquez sur "Annuler". Les conditions de remboursement dépendent de la politique d'annulation 
                        du prestataire et du délai avant la date de prestation.
                    </p>
                </div>
            </div>

            <!-- Question 6 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <button @click="open = open === 6 ? null : 6" class="w-full px-6 py-5 text-left flex justify-between items-center hover:bg-slate-50 transition-colors">
                    <span class="font-semibold text-slate-800">Comment contacter un prestataire ?</span>
                    <i class="fas fa-chevron-down text-blue-600 transition-transform" :class="{ 'rotate-180': open === 6 }"></i>
                </button>
                <div x-show="open === 6" x-collapse class="px-6 pb-5">
                    <p class="text-slate-600">
                        Vous pouvez contacter un prestataire via notre messagerie intégrée. Sur la page du prestataire ou 
                        du service, cliquez sur "Contacter" pour envoyer un message. Vous recevrez une notification 
                        lorsque le prestataire vous répondra.
                    </p>
                </div>
            </div>

            <!-- Question 7 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <button @click="open = open === 7 ? null : 7" class="w-full px-6 py-5 text-left flex justify-between items-center hover:bg-slate-50 transition-colors">
                    <span class="font-semibold text-slate-800">Comment laisser un avis ?</span>
                    <i class="fas fa-chevron-down text-blue-600 transition-transform" :class="{ 'rotate-180': open === 7 }"></i>
                </button>
                <div x-show="open === 7" x-collapse class="px-6 pb-5">
                    <p class="text-slate-600">
                        Après chaque prestation terminée, vous recevrez une invitation à laisser un avis. Vous pouvez 
                        également accéder à vos réservations passées et cliquer sur "Laisser un avis". Votre retour 
                        aide les autres utilisateurs à faire leur choix et permet aux prestataires de s'améliorer.
                    </p>
                </div>
            </div>

            <!-- Question 8 -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <button @click="open = open === 8 ? null : 8" class="w-full px-6 py-5 text-left flex justify-between items-center hover:bg-slate-50 transition-colors">
                    <span class="font-semibold text-slate-800">Quels sont les frais de la plateforme ?</span>
                    <i class="fas fa-chevron-down text-blue-600 transition-transform" :class="{ 'rotate-180': open === 8 }"></i>
                </button>
                <div x-show="open === 8" x-collapse class="px-6 pb-5">
                    <p class="text-slate-600">
                        L'inscription et la navigation sur TaPrestation sont gratuites pour les clients. 
                        Une commission est prélevée sur chaque transaction pour couvrir les frais de fonctionnement 
                        de la plateforme. Les détails sont disponibles dans nos conditions générales d'utilisation.
                    </p>
                </div>
            </div>
        </div>

        <!-- Contact section -->
        <div class="mt-12 bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-8 text-center text-white">
            <h2 class="text-2xl font-bold mb-4">Vous n'avez pas trouvé votre réponse ?</h2>
            <p class="mb-6 text-blue-100">Notre équipe est disponible pour répondre à toutes vos questions.</p>
            <a href="{{ route('contact') }}" class="inline-flex items-center bg-white text-blue-600 font-semibold px-6 py-3 rounded-xl hover:bg-blue-50 transition-colors">
                <i class="fas fa-envelope mr-2"></i>
                Contactez-nous
            </a>
        </div>
    </div>
</div>
@endsection
