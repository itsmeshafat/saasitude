@props(['plan', 'price', 'featured' => false, 'current' => false])

@php
    $cardCls = $featured
        ? 'border-2 border-[var(--primary)] bg-[var(--primary)] text-[var(--primary-foreground)] shadow-lg'
        : 'border border-[var(--border)] bg-[var(--card)] text-[var(--card-foreground)] shadow-[var(--card-shadow,0_1px_2px_0_rgb(0_0_0/0.05))]';
    $mutedCls = $featured
        ? 'text-[var(--primary-foreground)]/70'
        : 'text-[var(--muted-foreground)]';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex flex-col gap-5 rounded-[calc(var(--radius)+2px)] p-6 ' . $cardCls]) }}>
    @if($featured)
        <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-white px-3 py-0.5 text-xs font-semibold text-[var(--primary)] shadow">
            Most Popular
        </span>
    @endif

    <div>
        <p class="text-xs font-semibold uppercase tracking-widest {{ $mutedCls }}">{{ $plan->name }}</p>
        <p class="mt-2 flex items-baseline gap-1">
            <span class="text-4xl font-bold">{{ $price }}</span>
            <span class="text-sm {{ $mutedCls }}">/mo</span>
        </p>
    </div>

    <div class="flex-1 text-sm {{ $mutedCls }}">
        {{ $slot }}
    </div>

    @if($current)
        <span class="text-center text-sm font-medium {{ $mutedCls }}">Current plan</span>
    @endif
</div>
