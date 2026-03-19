@extends('layouts.app')

@section('title', 'Carnet d\'adresses')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        {{-- En-tête --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">📍 Carnet d'adresses</h1>
                <p class="text-gray-600 mt-1">Gérez vos adresses de livraison et facturation</p>
            </div>
            <div class="mt-4 md:mt-0">
                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle adresse
                </button>
            </div>
        </div>

        {{-- Liste des adresses --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if(isset($addresses) && $addresses->count() > 0)
                @foreach($addresses as $address)
                    <div class="bg-white rounded-xl shadow-sm border {{ $address->is_default ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-200' }} p-6 relative">
                        @if($address->is_default)
                            <span class="absolute top-4 right-4 px-2 py-1 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-full">
                                Par défaut
                            </span>
                        @endif
                        
                        <div class="flex items-start space-x-4">
                            <div class="p-3 bg-gray-100 rounded-full">
                                @if($address->type === 'home')
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                @elseif($address->type === 'work')
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-medium text-gray-900">{{ $address->label ?? ucfirst($address->type) }}</h3>
                                <p class="text-gray-600 mt-1">{{ $address->street }}</p>
                                <p class="text-gray-600">{{ $address->postal_code }} {{ $address->city }}</p>
                                @if($address->country)
                                    <p class="text-gray-500 text-sm">{{ $address->country }}</p>
                                @endif
                                @if($address->phone)
                                    <p class="text-gray-500 text-sm mt-2">📞 {{ $address->phone }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-3 mt-4 pt-4 border-t border-gray-100">
                            @if(!$address->is_default)
                                <form action="{{ route('client.address-book.set-default', $address) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                        Définir par défaut
                                    </button>
                                </form>
                            @endif
                            <button onclick="openEditModal({{ $address->id }})" class="text-gray-600 hover:text-gray-800 text-sm">
                                Modifier
                            </button>
                            <form action="{{ route('client.address-book.destroy', $address) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Supprimer cette adresse ?')">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Aucune adresse enregistrée</h3>
                        <p class="mt-2 text-gray-500">Ajoutez votre première adresse pour faciliter vos commandes.</p>
                        <button onclick="openAddModal()" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                            Ajouter une adresse
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Ajout/Edit Adresse --}}
<div id="addressModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">Nouvelle adresse</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="addressForm" action="{{ route('client.address-book.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type d'adresse</label>
                        <select name="type" id="addressType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="home">Domicile</option>
                            <option value="work">Travail</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Libellé (optionnel)</label>
                        <input type="text" name="label" id="addressLabel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Ex: Chez moi">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                        <input type="text" name="street" id="addressStreet" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Rue, numéro" required>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Code postal</label>
                            <input type="text" name="postal_code" id="addressPostal" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                            <input type="text" name="city" id="addressCity" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                        <input type="text" name="country" id="addressCountry" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" value="France">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone (optionnel)</label>
                        <input type="tel" name="phone" id="addressPhone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="+33 6 00 00 00 00">
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="is_default" id="addressDefault" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="addressDefault" class="ml-2 text-sm text-gray-700">Définir comme adresse par défaut</label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        Annuler
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Nouvelle adresse';
        document.getElementById('addressForm').action = '{{ route("client.address-book.store") }}';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('addressForm').reset();
        document.getElementById('addressModal').classList.remove('hidden');
    }
    
    function openEditModal(id) {
        document.getElementById('modalTitle').textContent = 'Modifier l\'adresse';
        document.getElementById('addressForm').action = '/client/address-book/' + id;
        document.getElementById('formMethod').value = 'PUT';
        // Load address data via AJAX if needed
        document.getElementById('addressModal').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('addressModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
