@props(['path' => null, 'alt' => '', 'class' => 'object-cover w-full h-40', 'placeholder' => '/images/placeholder.svg'])

@php
    $url = null;
    try {
        if ($path) {
            $url = storage_asset_url($path, ltrim($placeholder, '/'));
        }
    } catch (\Exception $e) {
        $url = null;
    }
    $finalSrc = $url ?? asset($placeholder);
@endphp

<img src="{{ $finalSrc }}" alt="{{ $alt }}" {{ $attributes->merge(['class' => $class]) }} data-fallback-src="{{ asset($placeholder) }}" loading="lazy" />
