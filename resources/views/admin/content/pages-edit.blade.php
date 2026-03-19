@extends('layouts.admin-modern')

@section('title', 'Edit Page')

@section('content')
<div class="container">
    <h1>Edit Page</h1>
    <form method="POST" action="{{ route('admin.content.pages.update', $page->id) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Title</label>
            <input name="title" class="form-control" value="{{ $page->title }}" />
        </div>
        <div class="form-group">
            <label>Slug</label>
            <input name="slug" class="form-control" value="{{ $page->slug }}" />
        </div>
        <div class="form-group">
            <label>Content</label>
            <textarea name="content" class="form-control" rows="6">{{ $page->content }}</textarea>
        </div>
        <button class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
