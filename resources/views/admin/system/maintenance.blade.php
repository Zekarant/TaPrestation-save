@extends('layouts.admin-modern')

@section('title', 'Mode maintenance')

@section('content')
<div class="page-header">
    <h1 class="page-title">🔧 Mode maintenance</h1>
    <p class="page-subtitle">Activez le mode maintenance pendant les mises à jour</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="card-base max-w-xl">
    <div class="text-center mb-6">
        @if($isMaintenanceMode ?? false)
            <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-hard-hat text-4xl text-yellow-600"></i>
            </div>
            <h3 class="text-xl font-semibold text-yellow-600">Mode maintenance ACTIF</h3>
            <p class="text-gray-600 mt-2">Le site est actuellement en maintenance</p>
        @else
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-circle text-4xl text-green-600"></i>
            </div>
            <h3 class="text-xl font-semibold text-green-600">Site en ligne</h3>
            <p class="text-gray-600 mt-2">Le site est accessible aux utilisateurs</p>
        @endif
    </div>
    
    <form action="{{ route('admin.system.maintenance.toggle') }}" method="POST" class="text-center">
        @csrf
        @if($isMaintenanceMode ?? false)
            <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 text-lg">
                <i class="fas fa-power-off mr-2"></i> Désactiver le mode maintenance
            </button>
        @else
            <button type="submit" class="px-6 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-lg" onclick="return confirm('Activer le mode maintenance ?')">
                <i class="fas fa-hard-hat mr-2"></i> Activer le mode maintenance
            </button>
        @endif
    </form>
</div>
@endsection
