@props([
    'values' => [],
    'labels' => [],
    'height' => 150,
    'color' => 'var(--primary)',
])

@php
    $heights = \App\Support\SvgChart::barHeights(array_values($values));
    // Tyro pattern: value caption + bar + label live in the SAME grid cell — cannot misalign.
@endphp

<div class="chart-bars" {{ $attributes }}>
    @if($values !== [])
        <div class="rounded-[10px] border p-4" style="border-color: var(--border); background: var(--muted);">
            <div class="grid items-end gap-1.5" style="grid-template-columns: repeat({{ count($values) }}, 1fr); height: {{ $height }}px;">
                @foreach($heights as $i => $pct)
                    <div class="flex flex-col items-center justify-end h-full gap-1" title="{{ ($labels[$i] ?? '').': '.number_format($values[$i]) }}">
                        <span class="text-[10px] leading-none text-[var(--muted-foreground)]">{{ \App\Support\SvgChart::compact($values[$i]) }}</span>
                        <div class="w-full rounded-t-md" style="height: {{ $pct }}%; background: {{ $color }}; opacity: {{ 0.45 + 0.55 * ($pct / 100) }};"></div>
                    </div>
                @endforeach
            </div>
        </div>
        @if($labels)
            <div class="grid gap-1.5 text-xs text-[var(--muted-foreground)] mt-2" style="grid-template-columns: repeat({{ count($labels) }}, 1fr);">
                @foreach($labels as $label)
                    <span class="text-center truncate">{{ $label }}</span>
                @endforeach
            </div>
        @endif
    @else
        <div class="rounded-[10px] border p-4 text-sm text-[var(--muted-foreground)] text-center"
             style="border-color: var(--border); background: var(--muted);">No data yet</div>
    @endif
</div>
