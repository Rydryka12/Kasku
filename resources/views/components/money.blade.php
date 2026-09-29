@props([
    'value' => 0,
    'signed' => false,
])

@php
    $val = (int) $value;
    $formatted = rupiah($val, $signed);
    $colorClass = '';
    if ($signed) {
        if ($val > 0) {
            $colorClass = 'text-masuk';
        } elseif ($val < 0) {
            $colorClass = 'text-keluar';
        }
    }
@endphp

<span {{ $attributes->merge(['class' => 'tabular-nums font-semibold ' . $colorClass]) }}>
    {{ $formatted }}
</span>
