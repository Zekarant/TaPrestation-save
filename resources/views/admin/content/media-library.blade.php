@extends('layouts.admin-modern')

@section('title', 'Media Library')

@section('content')
<div class="container">
    <h1>Media Library</h1>
    <div>
        <h3>Folders</h3>
        <ul>
            @foreach($folders as $f)
                <li><a href="?path={{ $f['path'] }}">{{ $f['name'] }}</a></li>
            @endforeach
        </ul>
    </div>
    <div>
        <h3>Files</h3>
        <ul>
            @foreach($files as $file)
                <li><a href="{{ $file['url'] }}" target="_blank">{{ $file['name'] }}</a> ({{ $file['size'] }} bytes)</li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
