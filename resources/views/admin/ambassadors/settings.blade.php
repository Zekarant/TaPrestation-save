@extends('layouts.admin-modern')

@section('title', 'Paramètres Ambassadeurs')

@section('content')
<div class="bg-blue-50 min-h-screen">
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-2xl mx-auto">
            <div class="mb-6">
                <a href="{{ route('admin.ambassadors.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Retour aux ambassadeurs
                </a>
                <h1 class="text-2xl font-bold text-blue-900 mt-2">
                    <i class="fas fa-cog mr-2"></i>Paramètres Ambassadeurs
                </h1>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
                <div class="bg-blue-50 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-blue-900 mb-2"><i class="fas fa-info-circle mr-1"></i>Fonctionnement des commissions</h3>
                    <p class="text-sm text-blue-700">
                        Les ambassadeurs touchent la commission de la plateforme sur les transactions de leurs prestataires affiliés.
                        Les taux sont les mêmes que ceux configurés dans les paramètres de commission de la plateforme :
                    </p>
                    <ul class="mt-2 text-sm text-blue-700 space-y-1">
                        <li><strong>Services :</strong> {{ get_setting('commission_services', '10') }}%</li>
                        <li><strong>Locations :</strong> {{ get_setting('commission_rentals', '8') }}%</li>
                        <li><strong>Food :</strong> {{ get_setting('commission_food', '15') }}%</li>
                        <li><strong>Ventes urgentes :</strong> {{ get_setting('commission_urgent_sales', get_setting('commission_services', '10')) }}%</li>
                    </ul>
                    <p class="text-xs text-blue-600 mt-2">
                        Pour modifier ces taux, rendez-vous dans les <a href="{{ route('admin.settings.commissions') }}" class="underline font-semibold">paramètres de commission</a>.
                    </p>
                </div>

                <div class="text-sm text-gray-600">
                    <p>Les paramètres avancés des ambassadeurs seront disponibles dans une prochaine mise à jour.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
