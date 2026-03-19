@extends('layouts.admin-modern')

@section('title', 'Pages')

@section('content')
<div class="container">
    <h1>Pages</h1>
    <a href="{{ route('admin.content.pages.create') }}" class="btn btn-primary">Créer une page</a>
    <ul>
        @forelse($pages as $page)
            <li>{{ $page->title }} — <a href="{{ route('admin.content.pages.edit', $page->id) }}">Edit</a></li>
        @empty
            <li>Aucune page trouvée.</li>
        @endforelse
    </ul>
</div>
@endsection
