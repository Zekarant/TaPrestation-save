@extends('layouts.app')

@section('title', 'Contacts - ' . $urgentSale->title)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 to-pink-100">
    <div class="container mx-auto px-4 py-6">
        <!-- En-tête -->
        <div class="bg-white rounded-xl shadow-lg border border-red-200 p-6 mb-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('prestataire.urgent-sales.show', $urgentSale) }}" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-red-900">Demandes de contact</h1>
                        <p class="text-red-700">{{ $urgentSale->title }}</p>
                    </div>
                </div>
                
                <!-- Stock actuel -->
                <div class="bg-{{ $urgentSale->quantity > 0 ? 'green' : 'red' }}-100 border border-{{ $urgentSale->quantity > 0 ? 'green' : 'red' }}-300 rounded-lg px-4 py-2">
                    <span class="text-{{ $urgentSale->quantity > 0 ? 'green' : 'red' }}-800 font-semibold">
                        <i class="fas fa-boxes mr-2"></i>Stock: {{ $urgentSale->quantity }} unité(s)
                    </span>
                    @if($urgentSale->status === 'sold')
                        <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">ÉPUISÉ</span>
                    @endif
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Liste des contacts -->
        @if($contacts->count() > 0)
            <div class="space-y-4">
                @foreach($contacts as $contact)
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                                    <i class="fas fa-user text-red-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $contact->user->name ?? 'Utilisateur' }}</h3>
                                    <p class="text-sm text-gray-500">{{ $contact->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            
                            <!-- Statut -->
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                @if($contact->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($contact->status === 'accepted') bg-green-100 text-green-800
                                @elseif($contact->status === 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                @if($contact->status === 'pending') En attente
                                @elseif($contact->status === 'accepted') Vente confirmée
                                @elseif($contact->status === 'rejected') Refusé
                                @else {{ ucfirst($contact->status) }}
                                @endif
                            </span>
                        </div>
                        
                        <!-- Message -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <p class="text-gray-700">{{ $contact->message }}</p>
                        </div>
                        
                        <!-- Coordonnées -->
                        <div class="flex flex-wrap gap-4 mb-4 text-sm">
                            @if($contact->email)
                                <a href="mailto:{{ $contact->email }}" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-envelope mr-1"></i>{{ $contact->email }}
                                </a>
                            @endif
                            @if($contact->phone)
                                <a href="tel:{{ $contact->phone }}" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-phone mr-1"></i>{{ $contact->phone }}
                                </a>
                            @endif
                        </div>
                        
                        <!-- Réponse existante -->
                        @if($contact->response)
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg mb-4">
                                <p class="text-sm text-blue-700 font-medium mb-1">Votre réponse:</p>
                                <p class="text-blue-800">{{ $contact->response }}</p>
                                <p class="text-xs text-blue-600 mt-1">Envoyée le {{ $contact->responded_at?->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif
                        
                        <!-- Actions -->
                        @if($contact->status === 'pending')
                            <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
                                <!-- Confirmer vente -->
                                @if($urgentSale->quantity > 0)
                                    <form action="{{ route('prestataire.urgent-sales.contacts.accept', $contact) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <label class="text-sm text-gray-600">Qté:</label>
                                        <input type="number" name="quantity" value="1" min="1" max="{{ $urgentSale->quantity }}" 
                                               class="w-16 px-2 py-1 border border-gray-300 rounded text-center text-sm">
                                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition">
                                            <i class="fas fa-check mr-1"></i>Confirmer vente
                                        </button>
                                    </form>
                                @else
                                    <span class="text-red-600 text-sm"><i class="fas fa-exclamation-triangle mr-1"></i>Stock épuisé</span>
                                @endif
                                
                                <!-- Refuser -->
                                <form action="{{ route('prestataire.urgent-sales.contacts.reject', $contact) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm transition">
                                        <i class="fas fa-times mr-1"></i>Refuser
                                    </button>
                                </form>
                                
                                <!-- Répondre -->
                                <button onclick="toggleResponse({{ $contact->id }})" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                                    <i class="fas fa-reply mr-1"></i>Répondre
                                </button>
                            </div>
                            
                            <!-- Formulaire de réponse (masqué par défaut) -->
                            <div id="response-form-{{ $contact->id }}" class="hidden mt-4 pt-4 border-t border-gray-200">
                                <form action="{{ route('prestataire.urgent-sales.contacts.respond', $contact) }}" method="POST">
                                    @csrf
                                    <textarea name="response" rows="3" placeholder="Votre réponse..." 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                                    <div class="mt-2 flex justify-end">
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition">
                                            <i class="fas fa-paper-plane mr-1"></i>Envoyer
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-6">
                {{ $contacts->links() }}
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-8 text-center">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucun contact</h3>
                <p class="text-gray-500">Vous n'avez pas encore reçu de demande pour cette annonce.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function toggleResponse(contactId) {
    const form = document.getElementById('response-form-' + contactId);
    form.classList.toggle('hidden');
}
</script>
@endpush
@endsection
