<div>
    <x-slot name="header">
        {{ $title }}
    </x-slot>

    <div class="p-12 text-center bg-permukaan border border-garis rounded-2xl space-y-4">
        <div class="w-12 h-12 rounded-full bg-batik/10 text-batik mx-auto flex items-center justify-center font-bold text-lg">
            ◈
        </div>
        <h2 class="text-xl font-bold text-tinta">{{ $title }}</h2>
        <p class="text-sm text-tinta-redup max-w-md mx-auto">
            Fitur <strong class="text-tinta">{{ $feature }}</strong> akan diaktifkan pada tahapan berikutnya.
        </p>
        <div>
            <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-lg bg-batik text-white hover:opacity-90 transition">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
