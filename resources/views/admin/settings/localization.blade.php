@extends('layouts.admin-modern')

@section('title', 'Localisation')
@section('page-title', 'Localisation')

@section('content')
<div class="page-header">
    <h1 class="page-title">🌍 Paramètres de localisation</h1>
    <p class="page-subtitle">Configurez la langue et le fuseau horaire</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="card-base">
    <form action="{{ route('admin.settings.localization.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Langue par défaut</label>
                <select name="locale" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @foreach($locales as $code => $name)
                        <option value="{{ $code }}" {{ $currentLocale == $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fuseau horaire</label>
                <select name="timezone" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    @foreach($timezones as $tz)
                        <option value="{{ $tz }}" {{ $currentTimezone == $tz ? 'selected' : '' }}>
                            {{ $tz }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Format de date</label>
                <select name="date_format" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="d/m/Y">DD/MM/YYYY (31/12/2025)</option>
                    <option value="m/d/Y">MM/DD/YYYY (12/31/2025)</option>
                    <option value="Y-m-d">YYYY-MM-DD (2025-12-31)</option>
                    <option value="d M Y">DD MMM YYYY (31 Dec 2025)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Symbole monétaire</label>
                <select name="currency_symbol" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="€">€ (Euro)</option>
                    <option value="$">$ (Dollar)</option>
                    <option value="£">£ (Livre)</option>
                    <option value="DH">DH (Dirham)</option>
                </select>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-save mr-2"></i> Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
