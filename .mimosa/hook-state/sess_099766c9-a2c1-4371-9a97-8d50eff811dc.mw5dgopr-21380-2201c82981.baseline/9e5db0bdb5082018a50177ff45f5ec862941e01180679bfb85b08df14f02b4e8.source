@props(['status'])

@php
    $map = [
        'active'     => ['success',   'Active'],
        'trialing'   => ['info',      'Trial'],
        'past_due'   => ['warning',   'Past Due'],
        'canceled'   => ['danger',    'Canceled'],
        'paused'     => ['muted',     'Paused'],
        'incomplete' => ['warning',   'Incomplete'],
    ];
    [$variant, $label] = $map[$status] ?? ['muted', ucfirst(str_replace('_', ' ', $status))];
@endphp

<x-ui.badge :variant="$variant">{{ $label }}</x-ui.badge>
