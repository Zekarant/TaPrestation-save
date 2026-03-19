@extends('layouts.admin-modern')

@section('title', 'Edit Email Template')

@section('content')
<div class="container">
    <h1>Edit Template: {{ $template->name }}</h1>
    <form method="POST" action="{{ route('admin.content.email-templates.update', $template->id) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Subject</label>
            <input name="subject" class="form-control" value="{{ $template->subject }}" />
        </div>
        <div class="form-group">
            <label>Body</label>
            <textarea name="body" class="form-control" rows="10">{{ $template->body }}</textarea>
        </div>
        <button class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
