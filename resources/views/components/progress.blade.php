@props([
    'percentage' => 0,
    'variant' => 'auto', // auto, kunyit, batik, keluar
])

@php
$pct = min(100, max(0, (float) $percentage));
$color = 'bg-batik';

if ($variant === 'kunyit') {
    $color = 'bg-kunyit';
} elseif ($variant === 'keluar') {
    $color = 'bg-keluar';
} elseif ($variant === 'batik') {
    $color = 'bg-batik';
} else {
    // auto
    if ($percentage >= 100) {
        $color = 'bg-keluar';
    } elseif ($percentage >= 80) {
        $color = 'bg-kunyit';
    } else {
        $color = 'bg-batik';
    }
}
@endphp

<div class="w-full">
    <div class="w-full bg-garis/50 rounded-full h-2 overflow-hidden">
        <div class="h-2 rounded-full {{ $color }} transition-all duration-300" style="width: {{ $pct }}%"></div>
    </div>
</div>
