@props([
    'name' => null,
    'show' => false,
    'maxWidth' => 'lg',
    'title' => null,
])

@php
$maxWidthClass = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth] ?? 'sm:max-w-lg';
@endphp

<div
    x-data="{
        show: @if($attributes->has('wire:model')) @entangle($attributes->wire('model')) @else {{ $show ? 'true' : 'false' }} @endif,
        focusables() {
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)].filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.focusables().indexOf(document.activeElement) + 1] || (this.$event.shiftKey ? this.lastFocusable() : this.firstFocusable()) },
        prevFocusable() { return this.focusables()[this.focusables().indexOf(document.activeElement) - 1] || (this.$event.shiftKey ? this.firstFocusable() : this.lastFocusable()) },
    }"
    @if($name)
        x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
        x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    @endif
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    id="{{ $name ?? md5($attributes) }}"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-end sm:items-center justify-center"
    style="display: none;"
>
    <!-- Backdrop -->
    <div
        x-show="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity"
        @click="show = false"
    ></div>

    <!-- Modal dialog / Mobile bottom sheet -->
    <div
        x-show="show"
        x-trap.inert.noscroll="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
        class="relative w-full {{ $maxWidthClass }} bg-permukaan border border-garis rounded-t-2xl sm:rounded-2xl p-6 shadow-xl max-h-[90vh] overflow-y-auto"
        @click.outside="show = false"
    >
        @if($title)
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-garis">
                <h3 class="text-lg font-bold text-tinta">{{ $title }}</h3>
                <button type="button" @click="show = false" class="text-tinta-redup hover:text-tinta p-1 rounded-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
