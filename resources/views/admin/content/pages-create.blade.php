@extends('layouts.admin-modern')

@section('title', 'Create Page')

@section('content')
<div class="container">
    <h1>Create Page</h1>
    <form method="POST" action="{{ route('admin.content.pages.store') }}">
        @csrf
        <div class="form-group">
            <label>Title</label>
            <input name="title" class="form-control" />
        </div>
        <div class="form-group">
            <label>Slug</label>
            <input name="slug" class="form-control" />
        </div>
        <div class="form-group">
            <label>Content</label>
            <textarea name="content" class="form-control" rows="6"></textarea>
        </div>
        <button class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
