@extends('layouts.app')

@section('title', 'Confirmation de votre demande de location')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="max-w-4xl mx-auto">
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg" role="alert">
                <p class="font-bold text-sm sm:text-base">Succès</p>
                <p class="text-sm sm:text-base">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 sm:p-6">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Détails de votre demande</h1>
                <p class="text-sm sm:text-base text-gray-600 mb-6 leading-relaxed">Votre demande de location a bien été enregistrée. Le prestataire va l'examiner et vous recevrez une notification dès qu'elle sera traitée.</p>

                <div class="border-t border-gray-200 pt-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 sm:gap-x-6 gap-y-4">
                        <div class="sm:col-span-1">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Numéro de demande</dt>
                            <dd class="mt-1 text-sm sm:text-base text-gray-900 font-semibold">#{{ $request->id }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Statut</dt>
                            <dd class="mt-1">
                                @if($request->status === 'pending')
                                    <span class="inline-flex items-center px-2 sm:px-2.5 py-1 sm:py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        En attente
                                    </span>
                                @elseif($request->status === 'approved')
                                    <span class="inline-flex items-center px-2 sm:px-2.5 py-1 sm:py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Approuvée
                                    </span>
                                @elseif($request->status === 'rejected')
                                    <span class="inline-flex items-center px-2 sm:px-2.5 py-1 sm:py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Rejetée
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Équipement</dt>
                            <dd class="mt-1 text-sm sm:text-base text-gray-900 font-medium">{{ $request->equipment->name }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Date de début</dt>
                            <dd class="mt-1 text-sm sm:text-base text-gray-900">{{ $request->start_date->format('d/m/Y') }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Date de fin</dt>
                            <dd class="mt-1 text-sm sm:text-base text-gray-900">{{ $request->end_date->format('d/m/Y') }}</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Durée</dt>
                            <dd class="mt-1 text-sm sm:text-base text-gray-900">{{ $request->duration_days }} jour(s)</dd>
                        </div>
                        <div class="sm:col-span-1">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Montant total estimé</dt>
                            <dd class="mt-1 text-sm sm:text-base font-bold text-gray-900">{{ number_format($request->final_amount, 2) }} €</dd>
                        </div>
                        @if($request->delivery_required)
                        <div class="sm:col-span-2">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Livraison demandée</dt>
                            <dd class="mt-1 text-sm sm:text-base text-gray-900">
                                <span class="inline-flex items-center px-2 sm:px-2.5 py-1 sm:py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i> Oui
                                </span>
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Adresse de livraison</dt>
                            <dd class="mt-1 text-sm sm:text-base text-gray-900 leading-relaxed">{{ $request->delivery_address }}</dd>
                        </div>
                        @endif
                        @if($request->message)
                        <div class="sm:col-span-2">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500">Message</dt>
                            <dd class="mt-1 text-sm sm:text-base text-gray-900 leading-relaxed bg-gray-50 p-3 rounded-lg">{{ $request->message }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
            <div class="bg-gray-50 px-4 sm:px-6 py-4">
                <a href="{{ route('client.equipment-rental-requests.index') }}" class="inline-flex items-center text-sm sm:text-base text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="hidden sm:inline">Retour à mes demandes</span>
                    <span class="sm:hidden">Retour</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection