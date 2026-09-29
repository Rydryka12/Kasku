@props([
    'color' => '#2B3A8C',
    'name' => '',
])

<div class="inline-flex items-center gap-2">
    <span class="inline-block w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $color }};"></span>
    @if($name)
        <span class="text-sm font-medium text-tinta">{{ $name }}</span>
    @endif
    {{ $slot }}
</div>
