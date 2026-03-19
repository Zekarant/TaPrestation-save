@extends('layouts.admin-modern')

@section('title', 'Gestion des rôles')

@section('content')
<div class="page-header">
    <h1 class="page-title">👥 Gestion des rôles</h1>
    <p class="page-subtitle">Gérez les rôles et permissions des utilisateurs</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($roles ?? [] as $role)
        <div class="card-base">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-lg">{{ $role->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $role->users_count ?? 0 }} utilisateur(s)</p>
                </div>
                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                    {{ $role->guard_name ?? 'web' }}
                </span>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Permissions:</p>
                <div class="flex flex-wrap gap-1">
                    @if(is_array($role->permissions ?? null) || is_object($role->permissions ?? null))
                        @forelse($role->permissions ?? [] as $permission)
                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">{{ is_string($permission) ? $permission : ($permission->name ?? $permission) }}</span>
                        @empty
                            <span class="text-gray-400 text-sm">Aucune permission</span>
                        @endforelse
                    @else
                        <span class="text-gray-400 text-sm">Aucune permission</span>
                    @endif
                </div>
            </div>
            
            <div class="flex gap-2">
                @if(!in_array($role->name, ['admin', 'super-admin']))
                    <form action="{{ route('admin.security.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Supprimer ce rôle ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                            <i class="fas fa-trash mr-1"></i> Supprimer
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-3 card-base text-center py-8 text-gray-500">
            <i class="fas fa-user-shield text-4xl mb-4 text-gray-400"></i>
            <p>Aucun rôle défini. Les rôles sont gérés via le système de permissions Laravel.</p>
        </div>
    @endforelse
</div>

<div class="mt-6 card-base">
    <h3 class="font-semibold mb-4">Ajouter un nouveau rôle</h3>
    <form action="{{ route('admin.security.roles.store') }}" method="POST" class="flex flex-wrap gap-4">
        @csrf
        <input type="text" name="name" placeholder="Nom du rôle (ex: editor)" class="px-4 py-2 border border-gray-300 rounded-lg flex-1" required>
        <input type="text" name="display_name" placeholder="Nom d'affichage (ex: Éditeur)" class="px-4 py-2 border border-gray-300 rounded-lg flex-1">
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i> Créer
        </button>
    </form>
</div>
@endsection
