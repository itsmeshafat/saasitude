@props([
    'values' => [],
    'labels' => [],
    'height' => 200,
    'width' => 600,
    'area' => true,
    'color' => 'var(--primary)',
])

@php
    $line = \App\Support\SvgChart::linePath($values, $width, $height);
    $fill = $area ? \App\Support\SvgChart::areaPath($values, $width, $height) : null;
    $uid = 'chart-gradient-'.md5($line.json_encode($values));
    // Tyro pattern: first/last range labels only (per-point labels can't align with inset points).
    $firstLabel = $labels[0] ?? null;
    $lastLabel = count($labels) > 1 ? $labels[count($labels) - 1] : null;
@endphp

<div class="chart-line" {{ $attributes }}>
    @if($line !== '')
        <div class="rounded-[10px] border p-4" style="border-color: var(--border); background: var(--muted);">
            <svg viewBox="0 0 {{ $width }} {{ $height }}" width="100%" height="{{ $height }}"
                 style="display: block;" preserveAspectRatio="none"
                 role="img" aria-label="{{ $labels ? implode(' → ', [$firstLabel, $lastLabel]) : 'Line chart' }}">
                <defs>
                    <linearGradient id="{{ $uid }}" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="{{ $color }}" stop-opacity="0.35" />
                        <stop offset="100%" stop-color="{{ $color }}" stop-opacity="0.02" />
                    </linearGradient>
                </defs>
                @if($fill)
                    <path d="{{ $fill }}" fill="url(#{{ $uid }})" />
                @endif
                <path d="{{ $line }}" fill="none" stroke="{{ $color }}" stroke-width="2.5" stroke-linecap="round" />
            </svg>
        </div>
        @if($firstLabel || $lastLabel)
            <div class="flex justify-between text-xs text-[var(--muted-foreground)] mt-2">
                <span>{{ $firstLabel }}</span>
                <span>{{ $lastLabel }}</span>
            </div>
        @endif
    @else
        <div class="rounded-[10px] border p-4 text-sm text-[var(--muted-foreground)] text-center"
             style="border-color: var(--border); background: var(--muted);">No data yet</div>
    @endif
</div>
