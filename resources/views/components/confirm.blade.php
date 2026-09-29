@props([
    'name' => 'confirm-modal',
    'title' => 'Konfirmasi',
    'confirmText' => 'Ya, Lanjutkan',
    'cancelText' => 'Batal',
    'action' => null,
])

<x-modal :name="$name" maxWidth="sm" :title="$title">
    <div class="space-y-4">
        <p class="text-sm text-tinta-redup">
            {{ $slot }}
        </p>

        <div class="flex items-center justify-end gap-2 pt-2">
            <button
                type="button"
                @click="show = false"
                class="px-4 py-2 text-sm font-medium rounded-lg border border-garis text-tinta hover:bg-kertas transition focus:outline-none focus:ring-2 focus:ring-batik"
            >
                {{ $cancelText }}
            </button>
            <button
                type="button"
                @if($action) wire:click="{{ $action }}" @endif
                @click="show = false"
                class="px-4 py-2 text-sm font-medium rounded-lg bg-keluar text-white hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-keluar"
            >
                {{ $confirmText }}
            </button>
        </div>
    </div>
</x-modal>
