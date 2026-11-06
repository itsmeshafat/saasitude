@props(['variant' => 'default'])

@php
    // Design-system classes from the Tyro stylesheet (.badge*) — NOT inline utilities.
    $variants = [
        'default'   => 'badge-primary',
        'primary'   => 'badge-primary',
        'secondary' => 'badge-secondary',
        'success'   => 'badge-success',
        'warning'   => 'badge-warning',
        'danger'    => 'badge-danger',
        'info'      => 'badge-info',
        // Tyro has no muted/outline badge variants — closest system equivalents:
        'muted'     => 'badge-secondary',
        'outline'   => 'badge-secondary',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . ($variants[$variant] ?? $variants['default'])]) }}>
    {{ $slot }}
</span>
