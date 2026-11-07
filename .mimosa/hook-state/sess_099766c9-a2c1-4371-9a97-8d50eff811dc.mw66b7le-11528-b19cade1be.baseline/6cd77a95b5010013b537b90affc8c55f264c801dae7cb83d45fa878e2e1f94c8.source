@props(['icon' => null, 'title', 'description' => null, 'action' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-4 py-16 text-center']) }}>
    @if($icon)
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[var(--muted)] text-[var(--muted-foreground)]">
            {{ $icon }}
        </div>
    @endif
    <div class="flex flex-col gap-1">
        <p class="text-sm font-semibold text-[var(--foreground)]">{{ $title }}</p>
        @if($description)
            <p class="text-sm text-[var(--muted-foreground)] max-w-xs">{{ $description }}</p>
        @endif
    </div>
    @if($action)
        <div>{{ $action }}</div>
    @endif
</div>
