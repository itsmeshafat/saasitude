@props([
    'values' => [],
    'labels' => [],
    'height' => 140,
    'color' => 'var(--primary)',
])

@php
    $heights = \App\Support\SvgChart::barHeights(array_values($values));
@endphp

<div class="chart-bars" {{ $attributes }}>
    @if($values !== [])
        <div class="flex items-end gap-1.5" style="height: {{ $height }}px;">
            @foreach($heights as $i => $pct)
                <div class="flex-1 rounded-t-md transition-all" style="height: {{ $pct }}%; background: {{ $color }}; opacity: {{ 0.45 + 0.55 * ($pct / 100) }};" title="{{ $labels[$i] ?? '' }}: {{ $values[$i] }}"></div>
            @endforeach
        </div>
        @if($labels)
            <div class="flex gap-1.5 text-xs text-[var(--muted-foreground)] mt-2">
                @foreach($labels as $label)
                    <span class="flex-1 text-center truncate">{{ $label }}</span>
                @endforeach
            </div>
        @endif
    @else
        <div class="text-sm text-[var(--muted-foreground)] py-8 text-center">No data yet</div>
    @endif
</div>
