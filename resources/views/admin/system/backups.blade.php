@extends('layouts.admin-modern')

@section('title', 'Sauvegardes')

@section('content')
<div class="page-header">
    <h1 class="page-title">💾 Sauvegardes</h1>
    <p class="page-subtitle">Gérez les sauvegardes de votre base de données</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="card-base mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="font-semibold">Créer une sauvegarde</h3>
            <p class="text-sm text-gray-500">Sauvegardez la base de données</p>
        </div>
        <form action="{{ route('admin.system.backups.create') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i> Nouvelle sauvegarde
            </button>
        </form>
    </div>
</div>

<div class="card-base">
    <h3 class="font-semibold mb-4">Sauvegardes existantes</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-3 px-4">Fichier</th>
                    <th class="text-left py-3 px-4">Taille</th>
                    <th class="text-left py-3 px-4">Date</th>
                    <th class="text-right py-3 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($backups ?? [] as $backup)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4">
                            <i class="fas fa-file-archive text-blue-600 mr-2"></i>
                            {{ $backup['filename'] }}
                        </td>
                        <td class="py-3 px-4">{{ $backup['size'] }}</td>
                        <td class="py-3 px-4">{{ $backup['date'] }}</td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('admin.system.backups.download', $backup['filename']) }}" class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-download"></i>
                            </a>
                            <form action="{{ route('admin.system.backups.destroy', $backup['filename']) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ?')">
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
                        <td colspan="4" class="text-center py-8 text-gray-500">
                            Aucune sauvegarde disponible
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
