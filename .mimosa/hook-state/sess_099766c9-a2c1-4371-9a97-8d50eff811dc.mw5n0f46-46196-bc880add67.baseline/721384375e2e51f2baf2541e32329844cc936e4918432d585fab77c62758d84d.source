@props([
    'values' => [],
    'labels' => [],
    'size' => 130,
    'stroke' => 14,
    'colors' => ['var(--primary)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)'],
])

@php
    $segments = \App\Support\SvgChart::donutSegments(array_values($values));
    $radius = 40;
    $total = array_sum($values);
@endphp

<div class="chart-donut" {{ $attributes }}>
    @if($segments !== [])
        {{-- Tyro pattern: fixed donut column + 1fr legend, center-aligned --}}
        <div class="grid items-center gap-5" style="grid-template-columns: {{ $size }}px 1fr;">
            <svg viewBox="0 0 100 100" style="width: {{ $size }}px; height: {{ $size }}px; display: block; margin: 0 auto;"
                 role="img" aria-label="{{ $labels ? implode(', ', $labels) : 'Donut chart' }}">
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
                <div class="text-sm">
                    @foreach($labels as $i => $label)
                        <div class="flex items-center justify-between gap-3 py-1">
                            <span class="flex items-center gap-2">
                                <span class="inline-block w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $colors[$i % count($colors)] }}"></span>
                                <span>{{ $label }}</span>
                            </span>
                            <span class="text-[var(--muted-foreground)]">{{ $segments[$i]['pct'] ?? 0 }}%</span>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between gap-3 pt-2 mt-1 border-t" style="border-color: var(--border);">
                        <span class="font-medium">Total</span>
                        <span class="font-semibold">{{ number_format($total) }}</span>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="rounded-[10px] border p-4 text-sm text-[var(--muted-foreground)] text-center"
             style="border-color: var(--border); background: var(--muted);">No data yet</div>
    @endif
</div>
