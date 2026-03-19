@extends('layouts.admin-modern')

@section('title', 'Gestion des catégories')
@section('page-title', 'Gestion des catégories')

@section('content')
<div class="page-header">
    <h1 class="page-title">📂 Gestion des catégories</h1>
    <p class="page-subtitle">Gérez les catégories et sous-catégories de services</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Formulaire d'ajout -->
    <div class="card-base">
        <h3 class="font-semibold text-gray-900 mb-4">Ajouter une catégorie</h3>
        <form action="{{ route('admin.settings.categories.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie parente</label>
                    <select name="parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">-- Catégorie principale --</option>
                        @foreach($categories ?? [] as $cat)
                            @if(!$cat->parent_id)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Icône (classe Font Awesome)</label>
                    <input type="text" name="icon" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="fas fa-tools">
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Ajouter
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des catégories -->
    <div class="lg:col-span-2 card-base">
        <h3 class="font-semibold text-gray-900 mb-4">Catégories existantes</h3>
        <div class="space-y-2">
            @forelse($categories ?? [] as $category)
                @if(!$category->parent_id)
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="{{ $category->icon ?? 'fas fa-folder' }} text-blue-600 mr-3"></i>
                                <span class="font-medium">{{ $category->name }}</span>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="editCategory({{ $category->id }})" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.settings.categories.destroy', $category->id) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette catégorie ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <!-- Sous-catégories -->
                        @foreach($categories->where('parent_id', $category->id) as $sub)
                            <div class="ml-8 mt-2 p-2 bg-white rounded flex items-center justify-between">
                                <span class="text-sm text-gray-600">↳ {{ $sub->name }}</span>
                                <form action="{{ route('admin.settings.categories.destroy', $sub->id) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 text-sm"><i class="fas fa-times"></i></button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            @empty
                <p class="text-gray-500">Aucune catégorie</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
