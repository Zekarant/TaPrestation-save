{{-- resources/views/layouts/prestataire.blade.php --}}
{{-- Layout spécifique pour les pages prestataire --}}
@extends('layouts.app')

@push('styles')
{{-- CSS d'ergonomie pour les pages prestataire --}}
<link rel="stylesheet" href="{{ asset('css/pages-ergonomics.css') }}">
@stack('prestataire-styles')
@endpush

@push('head')
@stack('prestataire-head')
@endpush

@section('content')
<div class="prestataire-layout">
    @yield('prestataire-content')
    @yield('content')
</div>
@overwrite

@push('scripts')
@stack('prestataire-scripts')
@endpush
