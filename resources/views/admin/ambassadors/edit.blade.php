@extends('layouts.admin-modern')

@section('title', 'Modifier l\'Ambassadeur')

@section('content')
<div class="bg-blue-50 min-h-screen">
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-2xl mx-auto">
            <div class="mb-6">
                <a href="{{ route('admin.ambassadors.show', $ambassador) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Retour au profil
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
                <h1 class="text-2xl font-bold text-blue-900 mb-6">
                    <i class="fas fa-edit mr-2"></i>Modifier : {{ $ambassador->user->name }}
                </h1>

                @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <ul class="text-sm text-red-700">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('admin.ambassadors.update', $ambassador) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nom complet *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $ambassador->user->name) }}" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email *</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $ambassador->user->email) }}" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Nouveau mot de passe</label>
                            <input type="text" name="password" id="password"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                                placeholder="Laisser vide pour ne pas changer">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">Téléphone</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $ambassador->phone) }}"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="city" class="block text-sm font-semibold text-gray-700 mb-1">Ville</label>
                                <input type="text" name="city" id="city" value="{{ old('city', $ambassador->city) }}"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">Statut *</label>
                            <select name="status" id="status" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="active" {{ old('status', $ambassador->status) === 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="suspended" {{ old('status', $ambassador->status) === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                                <option value="inactive" {{ old('status', $ambassador->status) === 'inactive' ? 'selected' : '' }}>Inactif</option>
                            </select>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-1">Notes (admin uniquement)</label>
                            <textarea name="notes" id="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('notes', $ambassador->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition duration-200 shadow-lg">
                            <i class="fas fa-save mr-2"></i>Enregistrer
                        </button>
                        <a href="{{ route('admin.ambassadors.show', $ambassador) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2.5 px-6 rounded-lg transition duration-200">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
