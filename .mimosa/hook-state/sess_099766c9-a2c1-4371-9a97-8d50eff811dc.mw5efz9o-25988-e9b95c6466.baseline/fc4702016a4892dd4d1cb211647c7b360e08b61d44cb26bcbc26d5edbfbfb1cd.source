@props(['label' => null, 'error' => null, 'hint' => null])

@php
    // Uses Tyro's .form-input so the field inherits glass styling and focus
    // treatment consistent with every hand-rolled form in the app.
@endphp

<div class="form-group !mb-0 flex flex-col gap-1.5">
    @if($label)
        <label class="form-label" for="{{ $attributes->get('id') }}">{{ $label }}</label>
    @endif
    <input
        {{ $attributes->merge(['class' => 'form-input']) }}
    >
    @if($error)
        <p class="form-error">{{ $error }}</p>
    @elseif($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
</div>
