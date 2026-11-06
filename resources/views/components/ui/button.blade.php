@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'full' => false,
    'loading' => false,
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

    // States (design system): default, hover, focus-visible, active, disabled,
    // loading (spinner + aria-busy, also disables), error (via .btn-error hook).
    $classes = 'btn ' . ($variants[$variant] ?? $variants['primary'])
        . ' ' . ($sizes[$size] ?? '')
        . ($full ? ' w-full' : '')
        . ($loading ? ' is-loading' : '');
@endphp

@if($href)
    <a href="{{ $href }}"
       @if($loading) aria-busy="true" aria-disabled="true" @endif
       @if($loading) onclick="return false;" @endif
       {{ $attributes->merge(['class' => $classes . ($loading ? ' disabled' : '')]) }}>
        @if($loading)<span class="btn-spinner" aria-hidden="true"></span>@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}"
        @if($loading) aria-busy="true" disabled @endif
        {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)<span class="btn-spinner" aria-hidden="true"></span>@endif
        {{ $slot }}
    </button>
@endif
