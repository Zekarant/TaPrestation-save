@extends('layouts.app')

@section('content')
<div class="prestataire-dashboard">
    {{-- Hero Section --}}
    <section class="py-8 bg-gradient-to-r from-blue-600 to-indigo-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div class="text-white">
                    <h1 class="text-2xl font-bold">Mode de paiement</h1>
                    <p class="mt-1 text-blue-100">Choisissez comment recevoir vos paiements</p>
                </div>
                <a href="{{ route('prestataire.dashboard') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </section>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('prestataire.settings.payment-mode.update') }}" method="POST" x-data="{ selectedMode: '{{ $currentMode }}' }">
            @csrf

            <div class="space-y-6">
                {{-- Option 1: Paiement Sécurisé (Escrow) --}}
                <label class="block cursor-pointer">
                    <div class="relative border-2 rounded-xl p-6 transition-all"
                         :class="selectedMode === 'escrow' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="payment_mode" value="escrow" x-model="selectedMode" class="sr-only">
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center"
                                     :class="selectedMode === 'escrow' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500'">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        🔒 Paiement Sécurisé
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Recommandé
                                        </span>
                                    </h3>
                                    <div class="flex-shrink-0" x-show="selectedMode === 'escrow'">
                                        <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-2 text-gray-600">
                                    L'argent du client est sécurisé par Stripe (la plateforme ne stocke jamais votre argent) jusqu'à confirmation du client ou libération automatique.
                                </p>
                                
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="ml-2 text-sm text-gray-600">Protection contre les impayés</span>
                                    </div>
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="ml-2 text-sm text-gray-600">Libération auto sous 48h</span>
                                    </div>
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="ml-2 text-sm text-gray-600">Badge "Paiement Sécurisé" visible</span>
                                    </div>
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="ml-2 text-sm text-gray-600">Paiement libéré après confirmation / auto-libération</span>
                                    </div>
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="ml-2 text-sm text-gray-600">Plus de confiance client = plus de ventes</span>
                                    </div>
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="ml-2 text-sm text-gray-600">Caution équipement gérée automatiquement</span>
                                    </div>
                                </div>

                                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-blue-700">
                                        <strong>💡 Comment ça marche :</strong><br>
                                        1. Le client paie → L'argent est sécurisé (Stripe)<br>
                                        2. Vous effectuez la prestation<br>
                                        3. Le client confirme OU 48h s'écoulent<br>
                                        4. Vous recevez votre argent (moins la commission)
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </label>

                {{-- Option 2: Paiement Direct --}}
                <label class="block cursor-pointer">
                    <div class="relative border-2 rounded-xl p-6 transition-all"
                         :class="selectedMode === 'direct' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="payment_mode" value="direct" x-model="selectedMode" class="sr-only">
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center"
                                     :class="selectedMode === 'direct' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-500'">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        ⚡ Paiement Direct
                                    </h3>
                                    <div class="flex-shrink-0" x-show="selectedMode === 'direct'">
                                        <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-2 text-gray-600">
                                    Vous recevez les paiements directement sur votre compte Stripe Connect. Plus rapide, mais moins de protection.
                                </p>
                                
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="ml-2 text-sm text-gray-600">Réception immédiate</span>
                                    </div>
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-orange-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="ml-2 text-sm text-gray-600">Pas de protection contre annulations</span>
                                    </div>
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-orange-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="ml-2 text-sm text-gray-600">Gestion des problèmes à votre charge (entre vous et le client)</span>
                                    </div>
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-orange-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="ml-2 text-sm text-gray-600">Pas de badge sécurisé</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </label>

                {{-- Conditions (pour escrow) --}}
                <div x-show="selectedMode === 'escrow'" x-cloak>
                    @if(!$escrowTermsAccepted)
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Conditions du paiement sécurisé</h3>
                            
                            <div class="prose prose-sm text-gray-600 max-h-48 overflow-y-auto mb-4 p-4 bg-white rounded-lg border">
                                <p><strong>En activant le mode "Paiement Sécurisé", vous acceptez les conditions suivantes :</strong></p>
                                <ul>
                                    <li>Les paiements des clients sont sécurisés par Stripe jusqu'à confirmation du client ou libération automatique.</li>
                                    <li>Vous recevrez le paiement sous 48h maximum après la fin de la prestation (ou confirmation du client).</li>
                                    <li>En cas de problème, vous et le client échangez et vous conservez des preuves (photos, échanges, etc.).</li>
                                    <li>Une commission de {{ get_setting('commission_services', '10') }}% sera prélevée sur chaque transaction lors du transfert sur votre compte Stripe Connect.</li>
                                    <li>Pour les locations d'équipement, la caution du client sera gérée automatiquement.</li>
                                    <li>Vous vous engagez à fournir les prestations conformément aux descriptions annoncées.</li>
                                </ul>
                            </div>

                            <label class="flex items-start">
                                <input type="checkbox" name="accept_terms" value="1" required class="mt-1 rounded border-gray-300 text-green-600 focus:ring-green-500">
                                <span class="ml-3 text-sm text-gray-700">
                                    J'ai lu et j'accepte les conditions du paiement sécurisé. Je comprends que l'argent est sécurisé par Stripe jusqu'à confirmation du client ou libération automatique.
                                </span>
                            </label>
                        </div>
                    @else
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-green-700">Conditions acceptées le {{ \Carbon\Carbon::parse($escrowTermsAccepted)->format('d/m/Y à H:i') }}</span>
                            </div>
                            <input type="hidden" name="accept_terms" value="1">
                        </div>
                    @endif
                </div>
            </div>

            {{-- Bouton de soumission --}}
            <div class="mt-8">
                <button type="submit" 
                        class="w-full flex items-center justify-center px-6 py-4 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors text-lg font-semibold">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer mon choix
                </button>
            </div>
        </form>

        {{-- Info supplémentaire --}}
        <div class="mt-8 p-6 bg-gray-50 rounded-xl">
            <h3 class="font-semibold text-gray-900 mb-3">❓ Besoin d'aide pour choisir ?</h3>
            <p class="text-gray-600 text-sm">
                <strong>Choisissez "Paiement Sécurisé" si :</strong>
            </p>
            <ul class="text-sm text-gray-600 list-disc list-inside mt-2 space-y-1">
                <li>Vous voulez rassurer vos clients et augmenter vos ventes</li>
                <li>Vous proposez des locations d'équipement (gestion de caution automatique)</li>
                <li>Vous souhaitez une protection en cas de litige</li>
                <li>Vous préférez que nous gérions les aspects financiers</li>
            </ul>
            
            <p class="text-gray-600 text-sm mt-4">
                <strong>Choisissez "Paiement Direct" si :</strong>
            </p>
            <ul class="text-sm text-gray-600 list-disc list-inside mt-2 space-y-1">
                <li>Vous avez déjà une clientèle fidèle et de confiance</li>
                <li>Vous préférez recevoir l'argent immédiatement</li>
                <li>Vous gérez vous-même les éventuels litiges</li>
            </ul>
        </div>
    </div>
</div>
@endsection
