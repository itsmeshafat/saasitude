@props([
    'values' => [],
    'labels' => [],
    'size' => 120,
    'stroke' => 14,
    'colors' => ['var(--primary)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)'],
])

@php
    $segments = \App\Support\SvgChart::donutSegments(array_values($values));
    $radius = 40;
@endphp

<div class="chart-donut flex items-center gap-5" {{ $attributes }}>
    @if($segments !== [])
        <svg viewBox="0 0 100 100" style="width: {{ $size }}px; height: {{ $size }}px;" role="img" aria-label="{{ $labels ? implode(', ', $labels) : 'Donut chart' }}">
            <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="var(--muted)" stroke-width="{{ $stroke }}" />
            @foreach($segments as $i => $segment)
                <circle cx="50" cy="50" r="{{ $radius }}" fill="none"
                        stroke="{{ $colors[$i % count($colors)] }}"
                        stroke-width="{{ $stroke }}"
                        stroke-dasharray="{{ $segment['dash'] }} {{ 2 * M_PI * $radius - $segment['dash'] }}"
                        stroke-dashoffset="{{ -$segment['offset'] }}"
                        transform="rotate(-90 50 50)"
                        stroke-linecap="butt" />
            @endforeach
        </svg>
        @if($labels)
            <div class="space-y-1.5 text-sm">
                @foreach($labels as $i => $label)
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $colors[$i % count($colors)] }}"></span>
                        <span>{{ $label }}</span>
                        <span class="text-[var(--muted-foreground)]">{{ $segments[$i]['pct'] ?? 0 }}%</span>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <div class="text-sm text-[var(--muted-foreground)] py-8 text-center w-full">No data yet</div>
    @endif
</div>
