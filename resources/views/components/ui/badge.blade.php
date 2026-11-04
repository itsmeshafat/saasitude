@props(['variant' => 'default'])

@php
    $variants = [
        'default'   => 'bg-[var(--primary)] text-[var(--primary-foreground)]',
        'secondary' => 'bg-[var(--secondary)] text-[var(--secondary-foreground)] border border-[var(--border)]',
        'outline'   => 'border border-[var(--border)] text-[var(--foreground)] bg-transparent',
        'success'   => 'bg-[var(--success)] text-white',
        'warning'   => 'bg-[var(--warning)] text-white',
        'danger'    => 'bg-[var(--destructive)] text-[var(--destructive-foreground)]',
        'info'      => 'bg-[var(--info)] text-[var(--info-foreground,#fff)]',
        'muted'     => 'bg-[var(--muted)] text-[var(--muted-foreground)]',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ' . ($variants[$variant] ?? $variants['default'])]) }}>
    {{ $slot }}
</span>
