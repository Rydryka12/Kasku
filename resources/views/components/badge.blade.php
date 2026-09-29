@props([
    'variant' => 'default', // default, aktif, arsip, waspada, lewat, lunas, gagal
])

@php
$classes = [
    'default' => 'bg-garis/60 text-tinta',
    'aktif' => 'bg-masuk/15 text-masuk border border-masuk/30',
    'arsip' => 'bg-garis text-tinta-redup',
    'waspada' => 'bg-kunyit/15 text-kunyit border border-kunyit/30',
    'lewat' => 'bg-keluar/15 text-keluar border border-keluar/30',
    'lunas' => 'bg-masuk/15 text-masuk border border-masuk/30',
    'gagal' => 'bg-keluar/15 text-keluar border border-keluar/30',
    'batik' => 'bg-batik/15 text-batik border border-batik/30',
][$variant] ?? 'bg-garis/60 text-tinta';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ' . $classes]) }}>
    {{ $slot }}
</span>
