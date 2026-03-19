@extends('layouts.app')

@section('title', 'Mon Menu - Produits')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-amber-50">
    <!-- Message de succès -->
    @if(session('success'))
    <div class="container mx-auto px-4 py-4">
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
            <div class="flex items-center">
                <span class="text-2xl mr-3">✅</span>
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Header -->
    <div class="bg-white shadow-lg border-b-4 border-orange-500">
        <div class="container mx-auto px-4 py-4 sm:py-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center">
                        <span class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center mr-3 shadow-lg">
                            <span class="text-2xl">🍽️</span>
                        </span>
                        Mon Menu
                    </h1>
                    <p class="text-gray-500 mt-1 ml-14">Gérez vos produits alimentaires</p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="{{ route('prestataire.food-delivery.index') }}" class="px-4 py-2 bg-gradient-to-r from-orange-100 to-amber-100 hover:from-orange-200 hover:to-amber-200 text-orange-700 rounded-lg transition flex items-center border border-orange-200">
                        <span class="mr-2">🚚</span> Livraison
                    </a>
                    <a href="{{ route('prestataire.food-orders.dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition flex items-center">
                        <span class="mr-2">🔥</span> Cuisine
                    </a>
                    <a href="{{ route('prestataire.food-products.create') }}" class="px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition flex items-center shadow-md font-semibold">
                        <span class="mr-2">➕</span> Ajouter un produit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="container mx-auto px-4 py-4">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-orange-400">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-100 rounded-lg mr-3">
                        <span class="text-xl">🍕</span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $products->count() }}</div>
                        <div class="text-sm text-gray-500">Produits</div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-green-400">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg mr-3">
                        <span class="text-xl">✅</span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $products->where('is_available', true)->count() }}</div>
                        <div class="text-sm text-gray-500">Disponibles</div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-red-400">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg mr-3">
                        <span class="text-xl">⏸️</span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $products->where('is_available', false)->count() }}</div>
                        <div class="text-sm text-gray-500">Indisponibles</div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-purple-400">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg mr-3">
                        <span class="text-xl">📂</span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-900">{{ $products->pluck('category')->unique()->count() }}</div>
                        <div class="text-sm text-gray-500">Catégories</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des produits -->
    <div class="container mx-auto px-4 pb-8">
        {{-- Guide pour créer un menu --}}
        @if($products->isEmpty())
        <div class="mb-6 p-5 bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-200 rounded-2xl">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="text-3xl">🎯</span>
                </div>
                <div>
                    <h4 class="font-bold text-orange-900 text-lg mb-2">Créez votre premier menu !</h4>
                    <div class="space-y-2 text-sm text-orange-700">
                        <p><strong>1.</strong> Ajoutez vos produits avec photo, description et prix</p>
                        <p><strong>2.</strong> Organisez-les par catégories (Entrées, Plats, Desserts...)</p>
                        <p><strong>3.</strong> Configurez vos options de livraison</p>
                        <p><strong>4.</strong> Votre menu sera visible par tous les clients !</p>
                    </div>
                    <a href="{{ route('prestataire.food-products.create') }}" class="inline-flex items-center mt-4 px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl transition shadow-lg">
                        <span class="mr-2">➕</span> Créer mon premier produit
                    </a>
                </div>
            </div>
        </div>
        @else
        {{-- Astuce dismissible --}}
        <div x-data="persistentVisibility('hideFoodTip', '1')" x-show="visible" x-transition class="mb-4 p-2 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-2 text-xs">
            <span>💡</span>
            <p class="text-blue-700 flex-1">Ajoutez de belles photos et descriptions pour attirer plus de clients.</p>
            <button @click="dismiss()" class="text-blue-400 hover:text-blue-600 flex-shrink-0">&times;</button>
        </div>
        @endif
        
        @if($products->isEmpty())
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="text-6xl mb-4">🍽️</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Aucun produit</h3>
                <p class="text-gray-500 mb-6">Commencez par ajouter vos premiers produits à votre menu</p>
                <a href="{{ route('prestataire.food-products.create') }}" class="inline-flex items-center px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg shadow-md transition">
                    <span class="mr-2">➕</span> Créer mon premier produit
                </a>
            </div>
        @else
            <!-- Grouper par catégorie -->
            @php
                $groupedProducts = $products->groupBy('category');
                $categoryLabels = [
                    'entree' => '🥗 Entrées',
                    'plat' => '🍖 Plats',
                    'dessert' => '🍰 Desserts',
                    'boisson' => '🥤 Boissons',
                    'amuse_bouche' => '🍢 Amuse-bouches',
                    'gateau' => '🎂 Gâteaux',
                    'pizza' => '🍕 Pizzas',
                    'sandwich' => '🥪 Sandwichs',
                    'salade' => '🥗 Salades',
                    'autre' => '📦 Autres',
                ];
            @endphp

            @foreach($groupedProducts as $category => $categoryProducts)
                <div class="mb-5">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <span class="text-2xl mr-2">{{ explode(' ', $categoryLabels[$category] ?? '📦 ' . ucfirst($category))[0] }}</span>
                        {{ explode(' ', $categoryLabels[$category] ?? $category, 2)[1] ?? ucfirst($category) }}
                        <span class="ml-2 px-2 py-0.5 bg-gray-200 rounded-full text-sm font-normal">{{ $categoryProducts->count() }}</span>
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
                        @foreach($categoryProducts as $product)
                            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col {{ !$product->is_available ? 'opacity-60' : '' }}">
                                <!-- Image -->
                                <div class="relative h-40 flex-shrink-0 bg-gradient-to-br from-orange-100 to-amber-100 overflow-hidden">
                                    @if($product->image)
                                        <img src="{{ storage_asset_url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-5xl">
                                            {{ explode(' ', $categoryLabels[$product->category] ?? '🍽️')[0] }}
                                        </div>
                                    @endif
                                    
                                    <!-- Badge disponibilité -->
                                    <div class="absolute top-2 right-2">
                                        @if($product->is_available)
                                            <span class="px-2 py-1 bg-green-500 text-white text-xs font-semibold rounded-full">Disponible</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-500 text-white text-xs font-semibold rounded-full">Indisponible</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Prix -->
                                    <div class="absolute bottom-2 right-2">
                                        <span class="px-3 py-1 bg-white/90 backdrop-blur text-orange-600 font-bold rounded-lg shadow">
                                            {{ number_format($product->price, 2) }} €
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Contenu -->
                                <div class="p-4 flex flex-col flex-grow">
                                    <h3 class="font-bold text-gray-900 text-lg mb-1 truncate">{{ $product->name }}</h3>
                                    @if($product->description)
                                        <p class="text-gray-500 text-sm line-clamp-2 mb-3">{{ $product->description }}</p>
                                    @endif
                                    
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        @if($product->preparation_time)
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-lg">
                                                ⏱️ {{ $product->preparation_time }} min
                                            </span>
                                        @endif
                                        @if($product->stock !== null)
                                            <span class="px-2 py-1 {{ $product->stock <= 5 ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }} text-xs rounded-lg">
                                                📦 Stock: {{ $product->stock }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Actions - toujours en bas -->
                                    <div class="flex gap-2 mt-auto pt-2">
                                        <form action="{{ route('prestataire.food-products.toggle', $product) }}" method="POST" class="flex-1 min-w-0">
                                            @csrf
                                            <button type="submit" class="w-full py-2 text-sm font-medium rounded-lg transition {{ $product->is_available ? 'bg-gray-100 hover:bg-red-100 text-gray-600 hover:text-red-600' : 'bg-green-100 hover:bg-green-200 text-green-600' }}">
                                                {{ $product->is_available ? '⏸️ Pause' : '▶️ Activer' }}
                                            </button>
                                        </form>
                                        <a href="{{ route('prestataire.food-products.edit', $product) }}" class="flex-shrink-0 px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-600 text-sm font-medium rounded-lg transition flex items-center justify-center" title="Modifier">
                                            ✏️
                                        </a>
                                        <form action="{{ route('prestataire.food-products.destroy', $product) }}" method="POST" onsubmit="return confirm('Supprimer ce produit ?')" class="flex-shrink-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-2 bg-red-100 hover:bg-red-200 text-red-600 text-sm font-medium rounded-lg transition" title="Supprimer">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection
