@props(['label' => null, 'error' => null, 'hint' => null, 'options' => [], 'placeholder' => null])

<div class="form-group !mb-0 flex flex-col gap-1.5">
    @if($label)
        <label class="form-label" for="{{ $attributes->get('id') }}">{{ $label }}</label>
    @endif
    <select {{ $attributes->merge(['class' => 'form-select']) }}>
        @isset($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endisset
        @foreach($options as $value => $optionLabel)
            <option value="{{ is_array($optionLabel) ? $value : $optionLabel }}"
                    @selected((string) $attributes->get('value') === (string) $value)>
                {{ is_array($optionLabel) ? ($optionLabel['label'] ?? $value) : $optionLabel }}
            </option>
        @endforeach
        {{ $slot }}
    </select>
    @if($error)
        <p class="form-error">{{ $error }}</p>
    @elseif($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
</div>
