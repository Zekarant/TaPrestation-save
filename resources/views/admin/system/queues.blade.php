@extends('layouts.admin-modern')

@section('title', 'Files d\'attente')

@section('content')
<div class="page-header">
    <h1 class="page-title">📬 Files d'attente</h1>
    <p class="page-subtitle">Gérez les tâches en arrière-plan</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-blue-600">{{ $pendingJobs ?? 0 }}</div>
        <div class="text-sm text-gray-500">En attente</div>
    </div>
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-green-600">{{ $processedJobs ?? 0 }}</div>
        <div class="text-sm text-gray-500">Traités</div>
    </div>
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-red-600">{{ $failedJobs ?? 0 }}</div>
        <div class="text-sm text-gray-500">Échoués</div>
    </div>
</div>

<div class="card-base">
    <div class="flex justify-between items-center mb-4">
        <h3 class="font-semibold">Tâches échouées</h3>
        @if(($failedJobs ?? 0) > 0)
            <form action="{{ route('admin.system.queues.clear-failed') }}" method="POST">
                @csrf
                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                    Vider tout
                </button>
            </form>
        @endif
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-3 px-4">ID</th>
                    <th class="text-left py-3 px-4">Queue</th>
                    <th class="text-left py-3 px-4">Erreur</th>
                    <th class="text-left py-3 px-4">Date</th>
                    <th class="text-right py-3 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($failedJobsList ?? [] as $job)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4 font-mono text-sm">{{ $job->id }}</td>
                        <td class="py-3 px-4">{{ $job->queue }}</td>
                        <td class="py-3 px-4 text-sm text-red-600">{{ Str::limit($job->exception, 50) }}</td>
                        <td class="py-3 px-4">{{ $job->failed_at }}</td>
                        <td class="py-3 px-4 text-right">
                            <form action="{{ route('admin.system.queues.retry', $job->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-blue-600 hover:text-blue-800 mr-2">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.system.queues.delete', $job->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500">
                            Aucune tâche échouée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
