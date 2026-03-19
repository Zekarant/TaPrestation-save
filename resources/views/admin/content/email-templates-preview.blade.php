@extends('layouts.admin-modern')

@section('title', 'Preview Email Template')

@section('content')
<div class="container">
    <h1>Preview: {{ $template->name }}</h1>
    <div class="card">
        <div class="card-body">
            @php
                $safePreview = \App\Support\HtmlSanitizer::sanitize((string) $preview);
            @endphp
            {!! $safePreview !!}
        </div>
    </div>
</div>
@endsection
