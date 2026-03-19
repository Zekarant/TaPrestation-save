@extends('layouts.admin-modern')

@section('title', 'Testimonials')

@section('content')
<div class="container">
    <h1>Testimonials</h1>
    <ul>
        @forelse($testimonials as $t)
            <li>{{ $t->name }} — {{ 
                    \Illuminate\Support\Str::limit($t->content, 100) }}</li>
        @empty
            <li>Aucun témoignage trouvé.</li>
        @endforelse
    </ul>
</div>
@endsection
