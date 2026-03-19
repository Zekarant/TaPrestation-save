@extends('layouts.admin-modern')

@section('title', 'Email Templates')

@section('content')
<div class="container">
    <h1>Modèles d'e-mails</h1>
    <ul>
        @forelse($templates as $template)
            <li>{{ $template->name }} — <a href="{{ route('admin.content.email-templates.edit', $template->id) }}">Edit</a> | <a href="{{ route('admin.content.email-templates.preview', $template->id) }}">Preview</a></li>
        @empty
            <li>Aucun modèle trouvé.</li>
        @endforelse
    </ul>
</div>
@endsection
