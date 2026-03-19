{{--
    Skeleton Loader Component - Shows loading placeholder
    Usage: 
    <x-skeleton type="card" />
    <x-skeleton type="text" :lines="3" />
    <x-skeleton type="avatar" />
    <x-skeleton type="table" :rows="5" />
--}}

@props([
    'type' => 'text',
    'lines' => 3,
    'rows' => 3,
    'width' => 'full',
    'height' => 'auto',
    'rounded' => true
])

@php
    $baseClasses = 'animate-pulse bg-gray-200';
    $roundedClass = $rounded ? 'rounded' : '';
@endphp

@switch($type)
    @case('text')
        <div class="space-y-3">
            @for($i = 0; $i < $lines; $i++)
            <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 {{ $i === $lines - 1 && $lines > 1 ? 'w-2/3' : 'w-full' }}"></div>
            @endfor
        </div>
        @break
        
    @case('avatar')
        <div class="{{ $baseClasses }} rounded-full w-12 h-12"></div>
        @break
        
    @case('avatar-lg')
        <div class="{{ $baseClasses }} rounded-full w-20 h-20"></div>
        @break
        
    @case('card')
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-4 space-y-4">
            <div class="flex items-center gap-3">
                <div class="{{ $baseClasses }} rounded-full w-10 h-10"></div>
                <div class="flex-1 space-y-2">
                    <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-3/4"></div>
                    <div class="{{ $baseClasses }} {{ $roundedClass }} h-3 w-1/2"></div>
                </div>
            </div>
            <div class="{{ $baseClasses }} {{ $roundedClass }} h-32 w-full"></div>
            <div class="space-y-2">
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-full"></div>
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-4/5"></div>
            </div>
        </div>
        @break
        
    @case('stat')
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="{{ $baseClasses }} rounded-lg w-10 h-10"></div>
                <div class="{{ $baseClasses }} {{ $roundedClass }} w-12 h-4"></div>
            </div>
            <div class="{{ $baseClasses }} {{ $roundedClass }} h-8 w-20 mb-2"></div>
            <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-24"></div>
        </div>
        @break
        
    @case('table')
        <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
            {{-- Header --}}
            <div class="border-b border-gray-200 p-4 flex gap-4">
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-1/4"></div>
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-1/4"></div>
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-1/4"></div>
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-1/4"></div>
            </div>
            {{-- Rows --}}
            @for($i = 0; $i < $rows; $i++)
            <div class="border-b border-gray-100 p-4 flex gap-4 items-center">
                <div class="{{ $baseClasses }} rounded-full w-8 h-8"></div>
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-1/4"></div>
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-1/4"></div>
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-1/4"></div>
            </div>
            @endfor
        </div>
        @break
        
    @case('list')
        <div class="space-y-3">
            @for($i = 0; $i < $rows; $i++)
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 flex items-center gap-3">
                <div class="{{ $baseClasses }} rounded-lg w-12 h-12"></div>
                <div class="flex-1 space-y-2">
                    <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-3/4"></div>
                    <div class="{{ $baseClasses }} {{ $roundedClass }} h-3 w-1/2"></div>
                </div>
            </div>
            @endfor
        </div>
        @break
        
    @case('image')
        <div class="{{ $baseClasses }} {{ $roundedClass }} w-full h-48"></div>
        @break
        
    @case('button')
        <div class="{{ $baseClasses }} rounded-lg w-32 h-10"></div>
        @break
        
    @case('input')
        <div class="{{ $baseClasses }} {{ $roundedClass }} w-full h-10"></div>
        @break
        
    @case('form')
        <div class="space-y-4">
            <div>
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-24 mb-2"></div>
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-10 w-full"></div>
            </div>
            <div>
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-32 mb-2"></div>
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-10 w-full"></div>
            </div>
            <div>
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-4 w-28 mb-2"></div>
                <div class="{{ $baseClasses }} {{ $roundedClass }} h-24 w-full"></div>
            </div>
            <div class="{{ $baseClasses }} rounded-lg h-10 w-32"></div>
        </div>
        @break
        
    @default
        <div class="{{ $baseClasses }} {{ $roundedClass }}" style="width: {{ $width }}; height: {{ $height }};"></div>
@endswitch
