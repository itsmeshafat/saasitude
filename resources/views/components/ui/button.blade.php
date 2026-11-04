@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-medium rounded-[var(--radius)] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--ring)] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 whitespace-nowrap';

    $sizes = [
        'sm' => 'h-8 px-3 text-sm',
        'md' => 'h-11 px-4 text-sm',
        'lg' => 'h-12 px-6 text-base',
    ];

    $variants = [
        'primary'   => 'bg-[var(--primary)] text-[var(--primary-foreground)] hover:bg-[var(--primary)]/90',
        'secondary' => 'bg-[var(--secondary)] text-[var(--secondary-foreground)] hover:bg-[var(--secondary)]/80 border border-[var(--border)]',
        'outline'   => 'border border-[var(--border)] bg-transparent text-[var(--foreground)] hover:bg-[var(--accent)] hover:text-[var(--accent-foreground)]',
        'ghost'     => 'bg-transparent text-[var(--foreground)] hover:bg-[var(--accent)] hover:text-[var(--accent-foreground)]',
        'danger'    => 'bg-[var(--destructive)] text-[var(--destructive-foreground)] hover:bg-[var(--destructive)]/90',
        'link'      => 'text-[var(--primary)] underline-offset-4 hover:underline p-0 h-auto font-normal',
    ];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $base . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary'])]) }}
>{{ $slot }}</button>
