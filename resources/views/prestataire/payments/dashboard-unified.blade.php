@extends('layouts.prestataire')

@section('title', 'Dashboard Paiements')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- En-tête --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    💰 Dashboard Paiements
                </h1>
                <p class="mt-1 text-gray-600">Gérez tous vos revenus en un seul endroit</p>
            </div>
            <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                {{-- Connexion Stripe --}}
                @if($stripeConnected)
                    <span class="inline-flex items-center px-3 py-2 rounded-lg bg-green-100 text-green-800 text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Stripe Connecté
                    </span>
                @else
                    <a href="{{ route('prestataire.payments.connect') }}" 
                       class="inline-flex items-center px-4 py-2 rounded-lg bg-purple-600 text-white hover:bg-purple-700 transition text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        Connecter Stripe
                    </a>
                @endif

                {{-- Export CSV --}}
                <a href="{{ route('prestataire.payments.unified.export', request()->query()) }}" 
                   class="inline-flex items-center px-4 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition text-sm font-medium shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exporter CSV
                </a>
            </div>
        </div>

        {{-- Navigation Finances (dans la page, pas dans la tuile dashboard) --}}
        <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-2">
            <a href="{{ \Illuminate\Support\Facades\Route::has('prestataire.payments.index') ? route('prestataire.payments.index') : '#' }}"
               class="inline-flex items-center justify-center rounded-lg bg-white border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Paiements
            </a>
            <a href="{{ \Illuminate\Support\Facades\Route::has('prestataire.invoices.index') ? route('prestataire.invoices.index') : '#' }}"
               class="inline-flex items-center justify-center rounded-lg bg-white border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Factures
            </a>
            <a href="{{ \Illuminate\Support\Facades\Route::has('prestataire.payments.connect') ? route('prestataire.payments.connect') : '#' }}"
               class="inline-flex items-center justify-center rounded-lg bg-white border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Comptes
            </a>
            <a href="{{ \Illuminate\Support\Facades\Route::has('prestataire.escrow.index') ? route('prestataire.escrow.index') : '#' }}"
               class="inline-flex items-center justify-center rounded-lg bg-white border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Escrow
            </a>
        </div>

        {{-- Cartes statistiques --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
            {{-- Solde Stripe --}}
            @if($stats['available_balance'])
                <div class="col-span-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-indigo-100 text-sm font-medium">Solde Stripe</span>
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold">{{ number_format($stats['available_balance']['available'], 2) }} €</p>
                    <p class="text-indigo-200 text-sm mt-1">
                        + {{ number_format($stats['available_balance']['pending'], 2) }} € en transit
                    </p>
                </div>
            @endif

            {{-- Total gagné --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-500 text-sm">Total gagné</span>
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_earned'], 2) }} €</p>
                <p class="text-xs text-gray-400 mt-1">Net après commission</p>
            </div>

            {{-- Ce mois --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-500 text-sm">Ce mois</span>
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['this_month'], 2) }} €</p>
                @if($stats['evolution'] != 0)
                    <p class="text-xs mt-1 {{ $stats['evolution'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $stats['evolution'] > 0 ? '↑' : '↓' }} {{ abs($stats['evolution']) }}% vs mois dernier
                    </p>
                @endif
            </div>

            {{-- En attente --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-500 text-sm">En attente</span>
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['pending_amount'], 2) }} €</p>
                <p class="text-xs text-gray-400 mt-1">Paiements en cours</p>
            </div>

            {{-- Aujourd'hui --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-500 text-sm">Aujourd'hui</span>
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['today'], 2) }} €</p>
                <p class="text-xs text-gray-400 mt-1">Reçu aujourd'hui</p>
            </div>

            {{-- Transactions --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-500 text-sm">Transactions</span>
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['completed_transactions'] }}</p>
                <p class="text-xs text-gray-400 mt-1">Sur {{ $stats['total_transactions'] }} totales</p>
            </div>
        </div>

        {{-- Répartition par type --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Services</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($stats['by_type']['booking'], 2) }} €</p>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Équipements</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($stats['by_type']['equipment'], 2) }} €</p>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center">
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Ventes Flash</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($stats['by_type']['urgent_sale'], 2) }} €</p>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Food</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($stats['by_type']['food'], 2) }} €</p>
                </div>
            </div>
        </div>

        {{-- Filtres --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <form method="GET" action="{{ route('prestataire.payments.unified') }}" class="space-y-4">
                <div class="flex flex-wrap items-center gap-4">
                    {{-- Type --}}
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Type</label>
                        <select name="type" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all" {{ $filters['type'] == 'all' ? 'selected' : '' }}>Tous les types</option>
                            <option value="booking" {{ $filters['type'] == 'booking' ? 'selected' : '' }}>📅 Réservations</option>
                            <option value="equipment" {{ $filters['type'] == 'equipment' ? 'selected' : '' }}>🔧 Équipements</option>
                            <option value="urgent_sale" {{ $filters['type'] == 'urgent_sale' ? 'selected' : '' }}>🔥 Ventes Flash</option>
                            <option value="food" {{ $filters['type'] == 'food' ? 'selected' : '' }}>🍽️ Food</option>
                        </select>
                    </div>

                    {{-- Statut --}}
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Statut</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all" {{ $filters['status'] == 'all' ? 'selected' : '' }}>Tous les statuts</option>
                            <option value="completed" {{ $filters['status'] == 'completed' ? 'selected' : '' }}>✅ Complété</option>
                            <option value="pending" {{ $filters['status'] == 'pending' ? 'selected' : '' }}>⏳ En attente</option>
                            <option value="processing" {{ $filters['status'] == 'processing' ? 'selected' : '' }}>🔄 En cours</option>
                            <option value="refunded" {{ $filters['status'] == 'refunded' ? 'selected' : '' }}>↩️ Remboursé</option>
                        </select>
                    </div>

                    {{-- Période --}}
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Période</label>
                        <select name="period" id="periodSelect" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all" {{ $filters['period'] == 'all' ? 'selected' : '' }}>Toutes les périodes</option>
                            <option value="today" {{ $filters['period'] == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                            <option value="week" {{ $filters['period'] == 'week' ? 'selected' : '' }}>Cette semaine</option>
                            <option value="month" {{ $filters['period'] == 'month' ? 'selected' : '' }}>Ce mois</option>
                            <option value="quarter" {{ $filters['period'] == 'quarter' ? 'selected' : '' }}>Ce trimestre</option>
                            <option value="year" {{ $filters['period'] == 'year' ? 'selected' : '' }}>Cette année</option>
                            <option value="custom" {{ $filters['period'] == 'custom' ? 'selected' : '' }}>Personnalisé</option>
                        </select>
                    </div>

                    {{-- Dates personnalisées --}}
                    <div id="customDates" class="flex-1 min-w-[300px] {{ $filters['period'] != 'custom' ? 'hidden' : '' }}">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Dates</label>
                        <div class="flex gap-2">
                            <input type="date" name="date_from" value="{{ $filters['date_from'] }}" 
                                   class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Du">
                            <input type="date" name="date_to" value="{{ $filters['date_to'] }}" 
                                   class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Au">
                        </div>
                    </div>

                    {{-- Boutons --}}
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Filtrer
                        </button>
                        <a href="{{ route('prestataire.payments.unified') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                            Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Liste des transactions --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">
                    Transactions
                    <span class="text-sm font-normal text-gray-500">({{ $payments->total() }} résultats)</span>
                </h2>
            </div>

            @if($payments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($payments as $payment)
                                <tr class="hover:bg-gray-50 transition">
                                    {{-- Type --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $typeConfig = [
                                                'booking' => ['icon' => '📅', 'bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                                                'equipment' => ['icon' => '🔧', 'bg' => 'bg-purple-100', 'text' => 'text-purple-800'],
                                                'urgent_sale' => ['icon' => '🔥', 'bg' => 'bg-orange-100', 'text' => 'text-orange-800'],
                                                'food' => ['icon' => '🍽️', 'bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                            ];
                                            $config = $typeConfig[$payment['type']] ?? ['icon' => '💰', 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }}">
                                            {{ $config['icon'] }} {{ $payment['type_label'] }}
                                        </span>
                                    </td>

                                    {{-- Référence --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-mono text-gray-900">{{ $payment['reference'] }}</span>
                                    </td>

                                    {{-- Client --}}
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $payment['client_name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $payment['client_email'] }}</div>
                                    </td>

                                    {{-- Description --}}
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs truncate">{{ $payment['title'] }}</div>
                                        @if($payment['payment_type'] === 'deposit')
                                            <span class="text-xs text-yellow-600">⚡ Acompte</span>
                                        @elseif($payment['payment_type'] === 'balance')
                                            <span class="text-xs text-green-600">✓ Solde</span>
                                        @endif
                                        @if(($payment['type'] ?? '') === 'equipment' && !empty($payment['rental']))
                                            @php
                                                $rentalRow = $payment['rental'];
                                                $escrowRow = is_object($payment['escrow'] ?? null) ? (array) $payment['escrow'] : [];
                                                $escrowMeta = [];
                                                try {
                                                    $escrowMeta = !empty($escrowRow['metadata'] ?? null)
                                                        ? (json_decode((string) $escrowRow['metadata'], true) ?: [])
                                                        : [];
                                                } catch (\Throwable $e) {
                                                    $escrowMeta = [];
                                                }
                                                $depositAmount = (float) ($rentalRow->equipment->security_deposit ?? $rentalRow->security_deposit ?? ($escrowRow['deposit_amount'] ?? 0));
                                                $depositStatus = strtolower((string) (($rentalRow->deposit_status ?? null) ?: ($escrowMeta['deposit_status'] ?? 'pending')));
                                            @endphp
                                            @if($depositAmount > 0)
                                                <div class="text-xs mt-1 {{ $depositStatus === 'returned' ? 'text-emerald-700' : ($depositStatus === 'partial' ? 'text-amber-700' : ($depositStatus === 'retained' ? 'text-red-700' : 'text-gray-500')) }}">
                                                    Caution: {{ $depositStatus === 'returned' ? 'remboursée' : ($depositStatus === 'partial' ? 'partielle' : ($depositStatus === 'retained' ? 'retenue' : 'en attente')) }}
                                                </div>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Montant avec détail déductions --}}
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        @php
                                            $stripeFeePercent = (float) get_setting('stripe_fee_percent', '1.4');
                                            $stripeFeeFixed = (float) get_setting('stripe_fee_fixed', '0.25');
                                            $securityDeposit = (float) ($payment['security_deposit'] ?? 0);
                                            $grossAmount = (float) ($payment['gross_amount'] ?? $payment['amount']);
                                        @endphp
                                        <div class="text-sm font-bold text-green-600">+{{ number_format($payment['net_amount'], 2) }} €</div>
                                        <div class="text-xs text-gray-400 leading-relaxed">
                                            @if(($payment['type'] ?? '') === 'equipment' && $securityDeposit > 0)
                                                <span title="Montant location hors caution">Location: {{ number_format($payment['amount'], 2) }}€</span><br>
                                                <span title="Montant séquestré et restitué selon état">Caution: {{ number_format($securityDeposit, 2) }}€</span><br>
                                                <span title="Montant total débité">Débité: {{ number_format($grossAmount, 2) }}€</span><br>
                                            @else
                                                <span title="Prix de vente">Brut: {{ number_format($payment['amount'], 2) }}€</span><br>
                                            @endif
                                            @if(($payment['platform_commission'] ?? 0) >= 0)
                                                <span class="text-red-500" title="Commission TaPrestation">-{{ number_format($payment['platform_commission'], 2) }}€</span><br>
                                            @else
                                                <span class="text-green-500" title="TaPrestation absorbe">+{{ number_format(abs($payment['platform_commission']), 2) }}€ absorbé</span><br>
                                            @endif
                                            <span class="text-orange-500" title="Frais Stripe ({{ rtrim(rtrim(number_format($stripeFeeFixed, 2, '.', ''), '0'), '.') }}€ + {{ rtrim(rtrim(number_format($stripeFeePercent, 2, '.', ''), '0'), '.') }}%)">-{{ number_format($payment['stripe_fee'] ?? 0, 2) }}€ Stripe</span>
                                        </div>
                                    </td>

                                    {{-- Statut --}}
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @php
                                            $statusConfig = [
                                                'completed' => ['label' => 'Complété', 'bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                                'pending' => ['label' => 'En attente', 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                                                'processing' => ['label' => 'En cours', 'bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                                                'refunded' => ['label' => 'Remboursé', 'bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                                'cancelled' => ['label' => 'Annulé', 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                                            ];
                                            $sConfig = $statusConfig[$payment['status']] ?? ['label' => ucfirst($payment['status']), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $sConfig['bg'] }} {{ $sConfig['text'] }}">
                                            {{ $sConfig['label'] }}
                                        </span>
                                    </td>

                                    {{-- Date --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($payment['date'])->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($payment['date'])->format('H:i') }}</div>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        @php
                                            // Pour Stripe, garder l'ID complet (py_xxx), sinon extraire le numérique
                                            if ($payment['type'] === 'stripe') {
                                                $itemId = $payment['id'];
                                            } else {
                                                $parts = explode('_', $payment['id']);
                                                $itemId = end($parts);
                                            }
                                        @endphp
                                        <a href="{{ route('prestataire.payments.unified.show', ['type' => $payment['type'], 'id' => $itemId]) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            Détails →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $payments->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Aucune transaction</h3>
                    <p class="mt-2 text-sm text-gray-500">Aucune transaction ne correspond à vos critères de recherche.</p>
                </div>
            @endif
        </div>

        {{-- Info commission --}}
        @php
            $stripeFeePercentInfo = (float) get_setting('stripe_fee_percent', '1.4');
            $stripeFeeFixedInfo = (float) get_setting('stripe_fee_fixed', '0.25');
        @endphp
        <div class="mt-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-4 border border-indigo-100">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-indigo-900">Détail des déductions</h4>
                    <p class="mt-1 text-sm text-indigo-700">
                        Sur chaque transaction, les déductions suivantes sont appliquées :
                    </p>
                    <ul class="mt-2 text-sm text-indigo-700 space-y-1">
                        <li>🏢 <strong>Commission TaPrestation</strong> : variable selon le type de service</li>
                        <li>💳 <strong>Frais Stripe</strong> : {{ number_format($stripeFeeFixedInfo, 2, ',', ' ') }}€ + {{ rtrim(rtrim(number_format($stripeFeePercentInfo, 2, '.', ''), '0'), '.') }}% par transaction</li>
                        <li>💬 <em>Pour les petits montants, TaPrestation peut absorber une partie des frais Stripe</em></li>
                    </ul>
                    <p class="mt-2 text-sm text-indigo-600">
                        Total des déductions prélevées : <strong>{{ number_format($stats['total_commission'], 2) }} €</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('periodSelect').addEventListener('change', function() {
    const customDates = document.getElementById('customDates');
    if (this.value === 'custom') {
        customDates.classList.remove('hidden');
    } else {
        customDates.classList.add('hidden');
    }
});
</script>
@endsection
