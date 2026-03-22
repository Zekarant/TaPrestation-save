@extends('layouts.app')

@push('styles')
<style>
    .ambassador-sidebar {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
        min-height: calc(100vh - 80px);
    }
    .ambassador-sidebar .nav-link {
        color: rgba(255,255,255,0.7);
        padding: 0.75rem 1.25rem;
        border-radius: 0.5rem;
        margin: 0.15rem 0.5rem;
        transition: all 0.2s;
        font-size: 0.875rem;
    }
    .ambassador-sidebar .nav-link:hover,
    .ambassador-sidebar .nav-link.active {
        color: #fff;
        background: rgba(255,255,255,0.15);
    }
    .ambassador-sidebar .nav-link i {
        width: 24px;
        text-align: center;
        margin-right: 0.5rem;
    }
    .ambassador-sidebar .sidebar-header {
        padding: 1.5rem 1.25rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .ambassador-main {
        background: #f0f4f8;
        min-height: calc(100vh - 80px);
    }
</style>
@stack('ambassador-styles')
@endpush

@section('content')
<div class="flex">
    <!-- Sidebar -->
    <aside class="ambassador-sidebar hidden lg:block w-64 flex-shrink-0">
        <div class="sidebar-header">
            <h2 class="text-white font-bold text-lg">Espace Ambassadeur</h2>
            <p class="text-blue-200 text-xs mt-1">{{ Auth::user()->name }}</p>
        </div>
        <nav class="py-4">
            <a href="{{ route('ambassador.dashboard') }}" class="nav-link flex items-center {{ request()->routeIs('ambassador.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>Tableau de bord
            </a>
            <a href="{{ route('ambassador.prestataires.index') }}" class="nav-link flex items-center {{ request()->routeIs('ambassador.prestataires.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>Mes prestataires
            </a>
            <a href="{{ route('ambassador.commissions.index') }}" class="nav-link flex items-center {{ request()->routeIs('ambassador.commissions.*') ? 'active' : '' }}">
                <i class="fas fa-coins"></i>Commissions
            </a>
            <a href="{{ route('ambassador.referral.index') }}" class="nav-link flex items-center {{ request()->routeIs('ambassador.referral.*') ? 'active' : '' }}">
                <i class="fas fa-link"></i>Parrainage
            </a>
            <a href="{{ route('ambassador.stripe.index') }}" class="nav-link flex items-center {{ request()->routeIs('ambassador.stripe.*') ? 'active' : '' }}">
                <i class="fab fa-stripe-s"></i>Stripe
            </a>

            <div class="border-t border-white/10 my-3 mx-3"></div>

            <a href="{{ route('ambassador.profile.edit') }}" class="nav-link flex items-center {{ request()->routeIs('ambassador.profile.*') ? 'active' : '' }}">
                <i class="fas fa-user-cog"></i>Mon profil
            </a>

            <div class="border-t border-white/10 my-3 mx-3"></div>
            <p class="text-blue-300 text-xs px-4 mb-2 font-semibold uppercase">Naviguer comme</p>
            <a href="{{ route('client.dashboard') }}" class="nav-link flex items-center">
                <i class="fas fa-shopping-bag"></i>Espace Client
            </a>
            <a href="{{ route('prestataire.dashboard') }}" class="nav-link flex items-center">
                <i class="fas fa-briefcase"></i>Espace Prestataire
            </a>
        </nav>
    </aside>

    <!-- Mobile nav toggle -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
        <div class="flex justify-around py-2">
            <a href="{{ route('ambassador.dashboard') }}" class="text-center text-xs {{ request()->routeIs('ambassador.dashboard') ? 'text-blue-600' : 'text-gray-500' }}">
                <i class="fas fa-tachometer-alt block text-lg mb-0.5"></i>Dashboard
            </a>
            <a href="{{ route('ambassador.prestataires.index') }}" class="text-center text-xs {{ request()->routeIs('ambassador.prestataires.*') ? 'text-blue-600' : 'text-gray-500' }}">
                <i class="fas fa-users block text-lg mb-0.5"></i>Prestas
            </a>
            <a href="{{ route('ambassador.commissions.index') }}" class="text-center text-xs {{ request()->routeIs('ambassador.commissions.*') ? 'text-blue-600' : 'text-gray-500' }}">
                <i class="fas fa-coins block text-lg mb-0.5"></i>Commissions
            </a>
            <a href="{{ route('ambassador.referral.index') }}" class="text-center text-xs {{ request()->routeIs('ambassador.referral.*') ? 'text-blue-600' : 'text-gray-500' }}">
                <i class="fas fa-link block text-lg mb-0.5"></i>Parrainage
            </a>
            <a href="{{ route('ambassador.profile.edit') }}" class="text-center text-xs {{ request()->routeIs('ambassador.profile.*') ? 'text-blue-600' : 'text-gray-500' }}">
                <i class="fas fa-user block text-lg mb-0.5"></i>Profil
            </a>
        </div>
    </div>

    <!-- Main content -->
    <main class="ambassador-main flex-1 p-4 sm:p-6 lg:p-8 pb-20 lg:pb-8">
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-4 text-sm">
            {{ session('success') }}
        </div>
        @endif
        @if(session('warning'))
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg px-4 py-3 mb-4 text-sm">
            {{ session('warning') }}
        </div>
        @endif
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-4 text-sm">
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        @yield('ambassador-content')
    </main>
</div>
@overwrite

@push('scripts')
@stack('ambassador-scripts')
@endpush
