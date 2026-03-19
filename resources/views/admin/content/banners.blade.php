@extends('layouts.admin-modern')

@section('title', 'Banners')

@section('content')
<div class="container">
    <h1>Banners</h1>
    <ul>
        @forelse($banners as $banner)
            <li>{{ $banner->title }} — {{ $banner->position }}</li>
        @empty
            <li>Aucune bannière trouvée.</li>
        @endforelse
    </ul>
</div>
@endsection
