@props([
    'href' => null,
])

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'block w-full px-4 py-2 text-start text-sm leading-5 text-tinta hover:bg-kertas focus:outline-none transition duration-150 ease-in-out']) }}>
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => 'block w-full px-4 py-2 text-start text-sm leading-5 text-tinta hover:bg-kertas focus:outline-none transition duration-150 ease-in-out']) }}>
        {{ $slot }}
    </button>
@endif
