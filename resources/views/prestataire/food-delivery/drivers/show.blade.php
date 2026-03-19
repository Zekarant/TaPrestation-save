@extends('layouts.app')

@section('title', 'Profil livreur - ' . $driver->full_name)

@push('styles')
<style>
    /* Fix z-index pour le menu */
    .dropdown-menu, .mobile-menu, [class*="dropdown"], nav, header {
        z-index: 9999 !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-amber-50 py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Section d'aide --}}
        <x-help-section page="prestataire.drivers.show" />
        
        {{-- En-tête --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('prestataire.drivers.index') }}" 
               class="inline-flex items-center text-gray-600 hover:text-orange-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour à la liste
            </a>
            @if(isset($driver->employer_prestataire_id) && $driver->employer_prestataire_id === $prestataire->id)
                <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">
                    <i class="fas fa-id-badge mr-1"></i>
                    Livreur interne
                </span>
            @endif
        </div>

        {{-- Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Colonne gauche - Profil --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Carte profil --}}
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-orange-500 to-amber-500 p-6 text-center">
                        <div class="w-24 h-24 bg-white rounded-full mx-auto flex items-center justify-center shadow-lg">
                            @if($driver->photo)
                                <img src="{{ asset('storage/' . $driver->photo) }}" 
                                     alt="{{ $driver->full_name }}" 
                                     class="w-24 h-24 rounded-full object-cover">
                            @else
                                <span class="text-3xl font-bold text-orange-600">
                                    {{ strtoupper(substr($driver->first_name, 0, 1) . substr($driver->last_name, 0, 1)) }}
                                </span>
                            @endif
                        </div>
                        <h1 class="text-xl font-bold text-white mt-4">{{ $driver->full_name }}</h1>
                        <div class="flex items-center justify-center gap-2 mt-2">
                            <span class="text-2xl">{{ $driver->vehicle_icon }}</span>
                            <span class="text-white/80 capitalize">{{ $driver->vehicle_type }}</span>
                        </div>
                    </div>

                    <div class="p-5">
                        {{-- Note globale --}}
                        <div class="text-center mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-center justify-center gap-1 mb-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star text-lg {{ $i <= ($driver->rating ?? 0) ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                @endfor
                            </div>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($driver->rating ?? 0, 1) }}/5</p>
                            <p class="text-sm text-gray-500">Note globale</p>
                        </div>

                        {{-- Stats --}}
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-xl font-bold text-gray-900">{{ $driver->completed_deliveries ?? 0 }}</p>
                                <p class="text-xs text-gray-500">Livraisons totales</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-xl font-bold text-green-600">{{ $driver->success_rate ?? 100 }}%</p>
                                <p class="text-xs text-gray-500">Taux de réussite</p>
                            </div>
                        </div>

                        {{-- Info contact --}}
                        <div class="space-y-2 text-sm">
                            @if($driver->phone)
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-phone w-5 text-gray-400"></i>
                                    <span>{{ $driver->phone }}</span>
                                </div>
                            @endif
                            @if($driver->email)
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-envelope w-5 text-gray-400"></i>
                                    <span class="truncate">{{ $driver->email }}</span>
                                </div>
                            @endif
                            @if($driver->vehicle_plate)
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-id-card w-5 text-gray-400"></i>
                                    <span>{{ $driver->vehicle_plate }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Bio --}}
                        @if($driver->bio)
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-sm text-gray-600 italic">"{{ $driver->bio }}"</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Tarifs --}}
                @if($pricing)
                    <div class="bg-white rounded-2xl shadow-lg p-5">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-euro-sign text-orange-500 mr-2"></i>
                            Tarifs du livreur
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Frais de base</span>
                                <span class="font-semibold">{{ number_format($pricing->base_fee, 2) }} €</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Par kilomètre</span>
                                <span class="font-semibold">{{ number_format($pricing->fee_per_km, 2) }} €/km</span>
                            </div>
                            @if($pricing->surge_multiplier > 1)
                                <div class="flex justify-between items-center text-orange-600">
                                    <span class="text-sm">Heures de pointe</span>
                                    <span class="font-semibold">×{{ number_format($pricing->surge_multiplier, 1) }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs text-gray-500 mb-2">Exemples de tarifs:</p>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                @foreach($pricing->getFeePreview() as $dist => $fee)
                                    <div class="flex justify-between bg-gray-50 rounded px-2 py-1">
                                        <span class="text-gray-600">{{ $dist }}</span>
                                        <span class="font-medium">{{ number_format($fee, 2) }}€</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="bg-white rounded-2xl shadow-lg p-5">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-cogs text-orange-500 mr-2"></i>
                        Actions
                    </h3>

                    <div class="space-y-3">
                        {{-- Préférence --}}
                        <form action="{{ route('prestataire.drivers.preference', $driver) }}" method="POST">
                            @csrf
                            <div class="flex gap-2">
                                <select name="status" class="flex-1 border-gray-300 rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500">
                                    <option value="neutral" {{ ($preference?->status ?? 'neutral') === 'neutral' ? 'selected' : '' }}>Normal</option>
                                    <option value="preferred" {{ $preference?->status === 'preferred' ? 'selected' : '' }}>⭐ Favori</option>
                                    <option value="blocked" {{ $preference?->status === 'blocked' ? 'selected' : '' }}>🚫 Bloqué</option>
                                </select>
                                <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 text-sm">
                                    <i class="fas fa-save"></i>
                                </button>
                            </div>
                            @if($preference?->status === 'blocked' && $preference->block_reason)
                                <p class="text-xs text-red-600 mt-1">
                                    Raison: {{ $preference->block_reason }}
                                </p>
                            @endif
                        </form>

                        {{-- Embauche/Libération --}}
                        @if($driver->employer_prestataire_id === $prestataire->id)
                            <form action="{{ route('prestataire.drivers.release', $driver) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        onclick="return confirm('Retirer ce livreur de votre équipe interne ?')"
                                        class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                                    <i class="fas fa-user-minus mr-2"></i>
                                    Retirer de l'équipe
                                </button>
                            </form>
                        @elseif(!$driver->employer_prestataire_id)
                            <form action="{{ route('prestataire.drivers.hire', $driver) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 text-sm">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    Embaucher (interne)
                                </button>
                            </form>
                        @endif

                        {{-- Notes internes --}}
                        <form action="{{ route('prestataire.drivers.preference', $driver) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="{{ $preference?->status ?? 'neutral' }}">
                            <textarea name="notes" 
                                      rows="2"
                                      placeholder="Notes internes (non visibles par le livreur)..."
                                      class="w-full border-gray-300 rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500 mb-2">{{ $preference?->notes }}</textarea>
                            <button type="submit" class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                                <i class="fas fa-sticky-note mr-2"></i>
                                Enregistrer notes
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Colonne droite - Historique et notes --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Stats avec vous --}}
                <div class="bg-white rounded-2xl shadow-lg p-5">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-chart-bar text-orange-500 mr-2"></i>
                        Statistiques avec vous
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-orange-50 rounded-xl">
                            <p class="text-2xl font-bold text-orange-600">{{ $stats['total_deliveries'] }}</p>
                            <p class="text-sm text-gray-600">Livraisons</p>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-xl">
                            <p class="text-2xl font-bold text-green-600">{{ $stats['successful'] }}</p>
                            <p class="text-sm text-gray-600">Réussies</p>
                        </div>
                        <div class="text-center p-4 bg-blue-50 rounded-xl">
                            <p class="text-2xl font-bold text-blue-600">{{ round($stats['avg_delivery_time'] ?? 0) }} min</p>
                            <p class="text-sm text-gray-600">Temps moyen</p>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-xl">
                            <p class="text-2xl font-bold text-purple-600">
                                @if($stats['my_avg_rating'])
                                    {{ number_format($stats['my_avg_rating'], 1) }}/5
                                @else
                                    -
                                @endif
                            </p>
                            <p class="text-sm text-gray-600">Ma note</p>
                        </div>
                    </div>
                </div>

                {{-- Formulaire notation rapide --}}
                <div class="bg-white rounded-2xl shadow-lg p-5">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-star text-orange-500 mr-2"></i>
                        Donner une note
                    </h3>
                    <form action="{{ route('prestataire.drivers.rate', $driver) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Note globale --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Note globale *</label>
                                <div class="flex gap-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="rating" value="{{ $i }}" class="sr-only peer" required>
                                            <span class="peer-checked:[&>i]:text-orange-500 block p-2 rounded-lg hover:bg-orange-50">
                                                <i class="fas fa-star text-2xl text-gray-300 transition-colors"></i>
                                            </span>
                                        </label>
                                    @endfor
                                </div>
                            </div>

                            {{-- Commande associée --}}
                            @if($deliveryHistory->isNotEmpty())
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Commande (optionnel)</label>
                                    <select name="food_order_id" class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                                        <option value="">-- Note générale --</option>
                                        @foreach($deliveryHistory->take(5) as $order)
                                            <option value="{{ $order->id }}">
                                                #{{ $order->order_number }} - {{ $order->created_at->format('d/m') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        {{-- Notes détaillées --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Ponctualité</label>
                                <select name="punctuality_rating" class="w-full border-gray-300 rounded text-sm">
                                    <option value="">-</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}">{{ $i }} ★</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Professionnalisme</label>
                                <select name="professionalism_rating" class="w-full border-gray-300 rounded text-sm">
                                    <option value="">-</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}">{{ $i }} ★</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Soin commandes</label>
                                <select name="care_rating" class="w-full border-gray-300 rounded text-sm">
                                    <option value="">-</option>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}">{{ $i }} ★</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div>
                            <textarea name="comment" 
                                      rows="2"
                                      placeholder="Commentaire (optionnel)..."
                                      class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"></textarea>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center text-sm text-gray-600">
                                <input type="checkbox" name="is_public" value="1" class="rounded border-gray-300 text-orange-500 mr-2">
                                Visible par le livreur
                            </label>
                            <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Envoyer
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Mes notes précédentes --}}
                @if($myRatings->isNotEmpty())
                    <div class="bg-white rounded-2xl shadow-lg p-5">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-history text-orange-500 mr-2"></i>
                            Mes notes précédentes
                        </h3>
                        <div class="space-y-3">
                            @foreach($myRatings as $rating)
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star text-sm {{ $i <= $rating->rating ? 'text-orange-500' : 'text-gray-300' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $rating->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    @if($rating->comment)
                                        <p class="text-sm text-gray-600">"{{ $rating->comment }}"</p>
                                    @endif
                                    @if($rating->foodOrder)
                                        <p class="text-xs text-gray-400 mt-1">
                                            Commande #{{ $rating->foodOrder->order_number }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Historique livraisons --}}
                @if($deliveryHistory->isNotEmpty())
                    <div class="bg-white rounded-2xl shadow-lg p-5">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-truck text-orange-500 mr-2"></i>
                            Historique des livraisons
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100">
                                        <th class="text-left py-2 text-gray-500 font-medium">Commande</th>
                                        <th class="text-left py-2 text-gray-500 font-medium">Client</th>
                                        <th class="text-left py-2 text-gray-500 font-medium">Date</th>
                                        <th class="text-left py-2 text-gray-500 font-medium">Statut</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($deliveryHistory as $order)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-2 font-medium">#{{ $order->order_number }}</td>
                                            <td class="py-2 text-gray-600">{{ $order->client?->full_name ?? 'Client' }}</td>
                                            <td class="py-2 text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="py-2">
                                                @if($order->status === 'delivered')
                                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">
                                                        Livrée
                                                    </span>
                                                @elseif($order->status === 'cancelled')
                                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">
                                                        Annulée
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Style interactif pour les étoiles de notation
    document.querySelectorAll('input[name="rating"]').forEach((radio, index, radios) => {
        radio.addEventListener('change', function() {
            radios.forEach((r, i) => {
                const icon = r.nextElementSibling.querySelector('i');
                if (i <= index) {
                    icon.classList.remove('text-gray-300');
                    icon.classList.add('text-orange-500');
                } else {
                    icon.classList.add('text-gray-300');
                    icon.classList.remove('text-orange-500');
                }
            });
        });
    });
</script>
@endpush
@endsection
