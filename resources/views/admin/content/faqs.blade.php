@extends('layouts.admin-modern')

@section('title', 'FAQs')

@section('content')
<div class="container">
    <h1>FAQs</h1>
    <ul>
        @forelse($faqs as $faq)
            <li>{{ $faq->question }} — {{ $faq->answer }}</li>
        @empty
            <li>Aucune FAQ trouvée.</li>
        @endforelse
    </ul>
</div>
@endsection
