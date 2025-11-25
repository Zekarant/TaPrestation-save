@extends('layouts.app')

@section('title', 'Certificats de satisfaction')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- En-tête -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Certificats de satisfaction</h1>
                    <p class="text-gray-600">Récompenses obtenues grâce à la qualité du service</p>
                </div>
                
                <div class="text-right">
                    <a href="{{ route('reviews.index', $prestataireId) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour aux avis
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Liste des certificats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($certificates as $certificate)
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border-2 border-yellow-200 rounded-lg p-6 relative overflow-hidden">
                    <!-- Badge de qualité -->
                    <div class="absolute top-4 right-4">
                        <div class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                            {{ $certificate->satisfaction_rate }}% satisfait
                        </div>
                    </div>
                    
                    <!-- Icône de certificat -->
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2L3 7v11l7-5 7 5V7l-7-5zM8 8a2 2 0 114 0 2 2 0 01-4 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Certificat de Satisfaction</h3>
                            <p class="text-gray-600">Année {{ $certificate->year }}</p>
                        </div>
                    </div>
                    
                    <!-- Détails du certificat -->
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Taux de satisfaction :</span>
                            <span class="font-semibold text-gray-900">{{ $certificate->satisfaction_rate }}%</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Nombre d'avis :</span>
                            <span class="font-semibold text-gray-900">{{ $certificate->total_reviews }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Numéro de certificat :</span>
                            <span class="font-mono text-sm text-gray-700">{{ $certificate->certificate_number }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Date d'émission :</span>
                            <span class="font-semibold text-gray-900">{{ $certificate->issued_at->format('d/m/Y') }}</span>
                        </div>
                        
                        @if($certificate->expires_at)
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Valide jusqu'au :</span>
                            <span class="font-semibold text-gray-900">{{ $certificate->expires_at->format('d/m/Y') }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Statut et actions -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            @if($certificate->isValid())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Valide
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    Expiré
                                </span>
                            @endif
                        </div>
                        
                        <a href="{{ route('reviews.download-certificate', $certificate) }}" 
                           class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Télécharger PDF
                        </a>
                    </div>
                    
                    <!-- Motif décoratif -->
                    <div class="absolute bottom-0 right-0 opacity-10">
                        <svg class="w-32 h-32 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2L3 7v11l7-5 7 5V7l-7-5zM8 8a2 2 0 114 0 2 2 0 01-4 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white rounded-lg shadow-md p-8 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        <h3 class="text-xl font-medium text-gray-900 mb-2">Aucun certificat disponible</h3>
                        <p class="text-gray-500 mb-4">Ce prestataire n'a pas encore obtenu de certificat de satisfaction.</p>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-left">
                            <h4 class="font-medium text-blue-900 mb-2">Comment obtenir un certificat ?</h4>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li>• Maintenir un taux de satisfaction d'au moins 90%</li>
                                <li>• Recevoir au minimum 10 avis clients</li>
                                <li>• Les certificats sont générés automatiquement chaque année</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
        
        <!-- Informations sur les certificats -->
        @if($certificates->count() > 0)
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-blue-900 mb-3">À propos des certificats de satisfaction</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
                <div>
                    <h4 class="font-medium mb-2">Critères d'attribution :</h4>
                    <ul class="space-y-1">
                        <li>• Taux de satisfaction ≥ 90%</li>
                        <li>• Minimum 10 avis clients</li>
                        <li>• Période d'évaluation : 1 an</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium mb-2">Validité :</h4>
                    <ul class="space-y-1">
                        <li>• Certificats valides 2 ans</li>
                        <li>• Renouvellement automatique</li>
                        <li>• Vérification possible via le numéro</li>
                    </ul>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection