{{-- resources/views/layouts/client.blade.php --}}
{{-- Layout spécifique pour les pages client --}}
@extends('layouts.app')

@push('styles')
{{-- CSS d'ergonomie pour les pages client --}}
<link rel="stylesheet" href="{{ asset('css/pages-ergonomics.css') }}">
@stack('client-styles')
@endpush

@push('head')
@stack('client-head')
@endpush

@section('content')
<div class="client-layout">
    @yield('client-content')
    @yield('content')
</div>
@overwrite

@push('scripts')
@stack('client-scripts')
@endpush
