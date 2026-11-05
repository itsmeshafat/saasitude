@props([
    'values' => [],
    'labels' => [],
    'height' => 180,
    'width' => 600,
    'area' => true,
    'color' => 'var(--primary)',
])

@php
    $line = \App\Support\SvgChart::linePath($values, $width, $height);
    $fill = $area ? \App\Support\SvgChart::areaPath($values, $width, $height) : null;
    $uid = 'chart-gradient-'.md5($line.json_encode($values));
@endphp

<div class="chart-line" {{ $attributes }}>
    @if($line !== '')
        <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full" style="height: {{ $height }}px;" preserveAspectRatio="none" role="img" aria-label="{{ $labels ? implode(', ', $labels) : 'Line chart' }}">
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
        @if($labels)
            <div class="flex justify-between text-xs text-[var(--muted-foreground)] mt-2">
                @foreach($labels as $label)
                    <span>{{ $label }}</span>
                @endforeach
            </div>
        @endif
    @else
        <div class="text-sm text-[var(--muted-foreground)] py-8 text-center">No data yet</div>
    @endif
</div>
