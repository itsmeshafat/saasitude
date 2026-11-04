@props(['label' => null, 'error' => null, 'hint' => null])

<div class="flex flex-col gap-1.5">
    @if($label)
        <label class="text-sm font-medium text-[var(--foreground)]">{{ $label }}</label>
    @endif
    <input
        {{ $attributes->merge(['class' => 'h-11 w-full rounded-[var(--radius)] border border-[var(--input)] bg-[var(--background)] px-3 text-sm text-[var(--foreground)] placeholder:text-[var(--muted-foreground)] focus:outline-none focus:ring-2 focus:ring-[var(--ring)] focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed']) }}
    >
    @if($error)
        <p class="text-xs text-[var(--destructive)]">{{ $error }}</p>
    @elseif($hint)
        <p class="text-xs text-[var(--muted-foreground)]">{{ $hint }}</p>
    @endif
</div>
