@extends('layouts.app')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-red-600 mb-4">500</h1>
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Erreur du serveur</h2>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
            Une erreur inattendue s'est produite. Veuillez réessayer dans quelques instants. Si le problème persiste, contactez le support.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ url()->previous() }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-semibold transition-colors">
                ← Retour
            </a>
            <a href="{{ url('/') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                Accueil
            </a>
        </div>
    </div>
</div>
@endsection
