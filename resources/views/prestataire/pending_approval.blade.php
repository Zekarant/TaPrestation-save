@extends('layouts.app')

@section('content')
<div class="container mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="bg-yellow-50 p-6 border-b border-yellow-100">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-12 w-12 text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-2xl font-bold text-gray-800">Compte en attente d'approbation</h2>
                    <p class="text-gray-600 mt-1">Votre compte prestataire est en cours de vérification par notre équipe.</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="prose max-w-none">
                <p class="text-gray-700 mb-4">Cher(e) {{ auth()->user()->name }},</p>
                
                <p class="text-gray-700 mb-4">Nous vous remercions d'avoir choisi TaPrestation pour proposer vos services. Afin de garantir la qualité et la sécurité de notre plateforme, tous les comptes prestataires font l'objet d'une vérification par notre équipe.</p>
                
                <p class="text-gray-700 mb-4">Votre compte est actuellement <span class="font-semibold text-yellow-600">en attente d'approbation</span>. Dès que votre compte sera validé, vous recevrez une notification par email et pourrez accéder à toutes les fonctionnalités de votre espace prestataire.</p>
                
                <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">Que se passe-t-il maintenant ?</h3>
                
                <ol class="list-decimal pl-6 space-y-2 text-gray-700">
                    <li>Notre équipe examine votre profil et vos informations (généralement sous 24 à 48 heures).</li>
                    <li>Vous recevrez un email de confirmation une fois votre compte approuvé.</li>
                    <li>Vous pourrez alors accéder à votre tableau de bord complet et commencer à proposer vos services.</li>
                </ol>
                
                <div class="bg-blue-50 p-4 rounded-lg mt-6">
                    <p class="text-blue-700">Si vous avez des questions ou si vous souhaitez compléter votre profil avec des informations supplémentaires, n'hésitez pas à nous contacter à <a href="mailto:support@taprestation.com" class="text-blue-600 hover:underline">support@taprestation.com</a>.</p>
                </div>
            </div>
            
            <div class="mt-8 border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Votre profil actuel</h3>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Nom</p>
                            <p class="font-medium">{{ auth()->user()->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium">{{ auth()->user()->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Secteur d'activité</p>
                            <p class="font-medium">{{ auth()->user()->prestataire->secteur_activite ?? 'Non spécifié' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date d'inscription</p>
                            <p class="font-medium">{{ auth()->user()->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Se déconnecter
                    </a>
                    
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection