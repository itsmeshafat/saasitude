@props(['label', 'used', 'limit' => null, 'unit' => null])

@php
    $pct = $limit ? min(100, ($used / max(1, $limit)) * 100) : 0;
    $barColor = match(true) {
        $pct >= 90 => 'var(--destructive)',
        $pct >= 70 => 'var(--warning)',
        default    => 'var(--primary)',
    };
    $usedDisplay = is_int($used) ? number_format($used) : $used;
    $limitDisplay = $limit ? number_format($limit) : null;
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-2']) }}>
    <div class="flex items-center justify-between text-sm">
        <span class="font-medium text-[var(--foreground)]">{{ $label }}</span>
        <span class="tabular-nums text-[var(--muted-foreground)]">
            {{ $usedDisplay }}{{ $unit ? ' '.$unit : '' }}
            @if($limitDisplay) / {{ $limitDisplay }}{{ $unit ? ' '.$unit : '' }} @endif
        </span>
    </div>
    @if($limit)
        <div class="h-2 w-full overflow-hidden rounded-full bg-[var(--muted)]" role="progressbar" aria-valuenow="{{ round($pct) }}" aria-valuemin="0" aria-valuemax="100">
            <div
                class="h-full rounded-full transition-all duration-300"
                style="width: {{ $pct }}%; background-color: {{ $barColor }};"
            ></div>
        </div>
    @endif
</div>
