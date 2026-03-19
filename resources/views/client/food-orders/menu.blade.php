@extends('layouts.app')

@section('title', 'Menu - ' . $prestataire->company_name)

@section('content')
<div class="bg-gradient-to-b from-orange-50 to-white min-h-screen">
    <!-- Header du Restaurant -->
    <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center gap-6">
                @if($prestataire->profile_image)
                    <img src="{{ asset('storage/' . $prestataire->profile_image) }}"
                         alt="{{ $prestataire->business_name ?? $prestataire->user->name ?? 'Restaurant' }}"
                         class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
                @else
                    <div class="w-32 h-32 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="bi bi-shop text-5xl"></i>
                    </div>
                @endif
                <div class="text-center md:text-left">
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">{{ $prestataire->company_name ?? $prestataire->user->name }}</h1>
                    @if($prestataire->address)
                        <p class="opacity-90"><i class="bi bi-geo-alt me-2"></i>{{ $prestataire->address }}, {{ $prestataire->city }}</p>
                    @endif
                    @if($prestataire->rating_average)
                        <div class="flex items-center justify-center md:justify-start gap-1 mt-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $prestataire->rating_average ? '-fill' : '' }} text-yellow-300"></i>
                            @endfor
                            <span class="ml-2">({{ $prestataire->total_reviews ?? 0 }} avis)</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Menu Principal -->
            <div class="flex-1">
                <!-- Navigation par catégorie -->
                <div class="sticky top-0 bg-white z-10 pb-4 mb-6 border-b">
                    <div class="flex overflow-x-auto gap-2 py-2" id="categoryNav">
                        @foreach($products as $categoryKey => $categoryProducts)
                            <a href="#category-{{ $categoryKey }}" 
                               class="px-4 py-2 bg-orange-100 text-orange-600 rounded-full whitespace-nowrap hover:bg-orange-200 transition font-medium">
                                {{ $categories[$categoryKey] ?? $categoryKey }}
                                <span class="ml-1 text-xs bg-orange-500 text-white rounded-full px-2">{{ $categoryProducts->count() }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Liste des produits par catégorie -->
                @foreach($products as $categoryKey => $categoryProducts)
                    <div id="category-{{ $categoryKey }}" class="mb-8 scroll-mt-20">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                            <span class="w-2 h-8 bg-orange-500 rounded mr-3"></span>
                            {{ $categories[$categoryKey] ?? $categoryKey }}
                        </h2>
                        
                        <div class="grid gap-4">
                            @foreach($categoryProducts as $product)
                                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-4 flex gap-4 product-card"
                                     data-product-id="{{ $product->id }}"
                                     data-product-name="{{ $product->name }}"
                                     data-product-price="{{ $product->price }}">
                                    
                                    <!-- Image -->
                                    <div class="flex-shrink-0">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}"
                                                 alt="{{ $product->name ?? 'Produit' }}"
                                                 class="w-24 h-24 md:w-32 md:h-32 rounded-lg object-cover">
                                        @else
                                            <div class="w-24 h-24 md:w-32 md:h-32 rounded-lg bg-gray-100 flex items-center justify-center">
                                                <i class="bi bi-image text-gray-400 text-3xl"></i>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Infos -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start gap-2">
                                            <h3 class="font-semibold text-gray-800 text-lg">{{ $product->name }}</h3>
                                            <span class="font-bold text-orange-600 text-lg whitespace-nowrap">{{ number_format($product->price, 2) }} €</span>
                                        </div>
                                        
                                        @if($product->description)
                                            <p class="text-gray-500 text-sm mt-1 line-clamp-2">{{ $product->description }}</p>
                                        @endif
                                        
                                        <div class="flex items-center gap-4 mt-3 text-sm text-gray-400">
                                            @if($product->preparation_time)
                                                <span><i class="bi bi-clock me-1"></i>{{ $product->preparation_time }} min</span>
                                            @endif
                                            @if($product->stock !== null && $product->stock <= 5)
                                                <span class="text-red-500"><i class="bi bi-exclamation-circle me-1"></i>Stock limité</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Add to Cart Button -->
                                        @auth
                                            <form action="{{ route('food.cart.add', [$prestataire, $product]) }}" method="POST" class="add-to-cart-form mt-3">
                                                @csrf
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                                                    <i class="bi bi-plus-lg"></i>
                                                    Ajouter
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('login') }}" class="inline-block mt-3 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium transition">
                                                <i class="bi bi-person me-1"></i>Se connecter pour commander
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Sidebar Panier (Desktop) -->
            @auth
            <div class="lg:w-96">
                <div class="sticky top-4">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="font-bold text-xl mb-4 flex items-center">
                            <i class="bi bi-cart3 text-orange-500 me-2"></i>
                            Votre Panier
                        </h3>
                        
                        <div id="cart-items">
                            @php
                                $cart = session()->get("food_cart.{$prestataire->id}", []);
                                $cartCount = array_sum(array_column($cart, 'quantity'));
                            @endphp
                            
                            @if($cartCount > 0)
                                <p class="text-gray-600 mb-4">{{ $cartCount }} article(s) dans votre panier</p>
                                <a href="{{ route('food.cart', $prestataire) }}" class="block w-full bg-orange-500 hover:bg-orange-600 text-white text-center py-3 rounded-lg font-semibold transition">
                                    <i class="bi bi-cart-check me-2"></i>Voir le panier
                                </a>
                            @else
                                <div class="text-center py-8 text-gray-400">
                                    <i class="bi bi-cart text-5xl"></i>
                                    <p class="mt-2">Votre panier est vide</p>
                                    <p class="text-sm">Ajoutez des articles du menu</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Infos Restaurant -->
                    <div class="bg-white rounded-xl shadow-lg p-6 mt-4">
                        <h4 class="font-semibold mb-3"><i class="bi bi-info-circle text-orange-500 me-2"></i>Informations</h4>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center gap-2">
                                <i class="bi bi-bag-check text-green-500"></i>
                                À emporter disponible
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="bi bi-truck text-blue-500"></i>
                                Livraison possible
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="bi bi-credit-card text-purple-500"></i>
                                Paiement sécurisé
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            @endauth
        </div>
    </div>
</div>

<!-- Mobile Cart Button -->
@auth
@if($cartCount > 0)
<div class="fixed bottom-4 left-4 right-4 lg:hidden z-50">
    <a href="{{ route('food.cart', $prestataire) }}" 
       class="flex items-center justify-between bg-orange-500 text-white p-4 rounded-xl shadow-lg">
        <span class="flex items-center gap-2">
            <span class="bg-white text-orange-500 rounded-full w-8 h-8 flex items-center justify-center font-bold">
                {{ $cartCount }}
            </span>
            Voir le panier
        </span>
        <i class="bi bi-arrow-right text-xl"></i>
    </a>
</div>
@endif
@endauth
@endsection

@push('scripts')
<script>
document.querySelectorAll('.add-to-cart-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const button = this.querySelector('button');
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check-lg"></i> Ajouté !';
        button.classList.add('bg-green-500');
        button.disabled = true;
        
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count
                setTimeout(() => {
                    button.innerHTML = originalContent;
                    button.classList.remove('bg-green-500');
                    button.disabled = false;
                    location.reload();
                }, 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.innerHTML = originalContent;
            button.classList.remove('bg-green-500');
            button.disabled = false;
        });
    });
});

// Smooth scroll for category navigation
document.querySelectorAll('#categoryNav a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});
</script>
@endpush
