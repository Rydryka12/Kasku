@props([
    'title' => 'Belum ada data',
    'description' => '',
    'actionText' => null,
    'actionWire' => null,
    'actionHref' => null,
    'actionClick' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-12 px-4 rounded-2xl border border-dashed border-garis bg-permukaan/50']) }}>
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-garis/40 text-tinta-redup mb-4">
        {{ $icon ?? '' }}
        @if(!isset($icon))
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        @endif
    </div>
    <h3 class="text-base font-semibold text-tinta mb-1">{{ $title }}</h3>
    @if($description)
        <p class="text-sm text-tinta-redup max-w-sm mx-auto mb-5">{{ $description }}</p>
    @endif
    @if($actionText)
        @if($actionHref)
            <a href="{{ $actionHref }}" wire:navigate class="inline-flex items-center px-4 py-2 rounded-lg bg-batik text-white text-sm font-medium hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-batik">
                {{ $actionText }}
            </a>
        @elseif($actionWire)
            <button type="button" wire:click="{{ $actionWire }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-batik text-white text-sm font-medium hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-batik">
                {{ $actionText }}
            </button>
        @elseif($actionClick)
            <button type="button" @click="{{ $actionClick }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-batik text-white text-sm font-medium hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-batik">
                {{ $actionText }}
            </button>
        @endif
    @endif
</div>
