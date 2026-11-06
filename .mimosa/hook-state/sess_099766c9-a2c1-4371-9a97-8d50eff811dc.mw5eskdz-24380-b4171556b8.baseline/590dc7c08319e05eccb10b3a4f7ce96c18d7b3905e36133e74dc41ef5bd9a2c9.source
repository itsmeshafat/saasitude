@props(['header' => null, 'footer' => null])

<div {{ $attributes->merge(['class' => 'rounded-[calc(var(--radius)+2px)] border border-[var(--border)] bg-[var(--card)] text-[var(--card-foreground)] shadow-[var(--card-shadow,0_1px_2px_0_rgb(0_0_0/0.05))]']) }}>
    @if($header)
        <div class="flex items-center border-b border-[var(--border)] px-6 py-4">
            {{ $header }}
        </div>
    @endif
    <div class="px-6 py-5">
        {{ $slot }}
    </div>
    @if($footer)
        <div class="flex items-center border-t border-[var(--border)] px-6 py-4 bg-[var(--muted)] rounded-b-[calc(var(--radius)+2px)]">
            {{ $footer }}
        </div>
    @endif
</div>
