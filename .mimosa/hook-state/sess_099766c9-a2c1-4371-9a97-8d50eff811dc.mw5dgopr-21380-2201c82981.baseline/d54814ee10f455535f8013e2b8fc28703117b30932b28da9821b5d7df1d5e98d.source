@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'full' => false,
])

@php
    // Design-system classes from the Tyro stylesheet (.btn*) — NOT inline utilities.
    $variants = [
        'primary'   => 'btn-primary',
        'secondary' => 'btn-secondary',
        'default'   => 'btn-default',
        'outline'   => 'btn-outline-btn',
        'ghost'     => 'btn-ghost-btn',
        'danger'    => 'btn-destructive',
        'link'      => 'btn-link-btn',
    ];

    $sizes = ['sm' => 'btn-sm', 'md' => '', 'lg' => 'btn-lg'];

    $classes = 'btn ' . ($variants[$variant] ?? $variants['primary'])
        . ' ' . ($sizes[$size] ?? '')
        . ($full ? ' w-full' : '');
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
