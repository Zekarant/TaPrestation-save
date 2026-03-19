@extends('layouts.admin-modern')

@section('title', 'Logs système')

@section('content')
<div class="page-header">
    <h1 class="page-title">📋 Logs système</h1>
    <p class="page-subtitle">Consultez les journaux d'erreurs et d'activité</p>
</div>

<div class="card-base">
    <div class="flex justify-between items-center mb-4">
        <h3 class="font-semibold">Derniers logs</h3>
        <form action="{{ route('admin.system.logs.clear') }}" method="POST" onsubmit="return confirm('Effacer tous les logs ?')">
            @csrf
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                <i class="fas fa-trash mr-1"></i> Vider les logs
            </button>
        </form>
    </div>
    
    <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm overflow-x-auto max-h-96 overflow-y-auto">
        @if(!empty($logs))
            @foreach($logs as $log)
                <div class="mb-1">{{ $log }}</div>
            @endforeach
        @else
            <p class="text-gray-500">Aucun log disponible</p>
        @endif
    </div>
</div>
@endsection
