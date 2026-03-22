@extends('layouts.ambassador')

@section('ambassador-content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Mon profil</h1>

    <div class="bg-white rounded-xl shadow border border-blue-200 p-6">
        <form method="POST" action="{{ route('ambassador.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Nom complet</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">Téléphone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $ambassador->phone) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-semibold text-gray-700 mb-1">Ville</label>
                        <input type="text" name="city" id="city" value="{{ old('city', $ambassador->city) }}"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <hr class="my-4">
                <h3 class="font-semibold text-gray-900">Changer le mot de passe</h3>

                <div>
                    <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-1">Mot de passe actuel</label>
                    <input type="password" name="current_password" id="current_password"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-1">Nouveau mot de passe</label>
                        <input type="password" name="new_password" id="new_password"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Confirmer</label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg">
                    <i class="fas fa-save mr-2"></i>Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
