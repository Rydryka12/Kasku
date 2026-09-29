<div>
    <x-slot name="header">
        Sumber Saldo
    </x-slot>

    <div class="space-y-6">
        <!-- Header Ringkasan & Aksi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-wider text-tinta-redup font-semibold">Total Saldo Tersedia</p>
                <p class="text-3xl font-extrabold text-tinta tabular-nums tracking-tight">{{ rupiah($totalBalance) }}</p>
            </div>

            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-batik text-white text-sm font-semibold hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-batik"
            >
                <span>+</span>
                <span>Tambah Sumber Saldo</span>
            </button>
        </div>

        <!-- Kartu-kartu Sumber Saldo -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($sources as $source)
                <div class="bg-permukaan border border-garis rounded-2xl p-5 hover:border-batik/40 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3.5 h-3.5 rounded-full shrink-0" style="background-color: {{ $source->color }};"></span>
                                <a href="{{ route('sources.show', $source->id) }}" wire:navigate class="text-base font-bold text-tinta hover:text-batik transition">
                                    {{ $source->name }}
                                </a>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-garis text-tinta-redup font-medium">
                                {{ $source->type->label() }}
                            </span>
                        </div>

                        <div class="my-4">
                            <p class="text-xs text-tinta-redup mb-0.5">Saldo Saat Ini</p>
                            <p class="text-2xl font-bold text-tinta tabular-nums">{{ rupiah($source->current_balance) }}</p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-garis flex items-center justify-between text-xs">
                        <a href="{{ route('sources.show', $source->id) }}" wire:navigate class="text-batik font-semibold hover:underline">
                            Lihat Buku Besar →
                        </a>

                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                wire:click="edit({{ $source->id }})"
                                class="p-1 text-tinta-redup hover:text-tinta transition font-medium"
                            >
                                Ubah
                            </button>
                            <button
                                type="button"
                                wire:click="toggleArchive({{ $source->id }})"
                                class="p-1 text-tinta-redup hover:text-kunyit transition font-medium"
                            >
                                Arsipkan
                            </button>
                            <button
                                type="button"
                                wire:confirm="Yakin ingin menghapus sumber saldo ini?"
                                wire:click="delete({{ $source->id }})"
                                class="p-1 text-tinta-redup hover:text-keluar transition font-medium"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <x-empty
                        title="Belum ada sumber saldo"
                        description="Buat akun seperti Dompet Tunai, Rekening BCA, atau GoPay untuk mulai mencatat keuangan Anda."
                        actionText="Tambah Sumber Saldo"
                        actionWire="openCreateModal"
                    />
                </div>
            @endforelse
        </div>

        <!-- Bagian Arsip -->
        @if($archivedSources->isNotEmpty())
            <div x-data="{ openArchive: false }" class="border border-garis rounded-2xl bg-permukaan overflow-hidden">
                <button
                    type="button"
                    @click="openArchive = !openArchive"
                    class="w-full flex items-center justify-between p-4 text-sm font-semibold text-tinta-redup hover:text-tinta transition"
                >
                    <span>Sumber Saldo Diarsipkan ({{ $archivedSources->count() }})</span>
                    <svg class="w-4 h-4 transition-transform" :class="openArchive ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="openArchive" class="divide-y divide-garis border-t border-garis">
                    @foreach($archivedSources as $archived)
                        <div class="flex items-center justify-between p-4 bg-kertas/30">
                            <div class="flex items-center gap-3">
                                <span class="w-3.5 h-3.5 rounded-full shrink-0 opacity-50" style="background-color: {{ $archived->color }};"></span>
                                <div>
                                    <span class="text-sm font-medium text-tinta-redup line-through">{{ $archived->name }}</span>
                                    <span class="text-xs text-tinta-redup ml-2">({{ rupiah($archived->current_balance) }})</span>
                                </div>
                                <x-badge variant="arsip">Diarsipkan</x-badge>
                            </div>

                            <button
                                type="button"
                                wire:click="toggleArchive({{ $archived->id }})"
                                class="px-3 py-1 text-xs font-semibold rounded-lg border border-garis text-tinta hover:bg-permukaan transition"
                            >
                                Aktifkan Kembali
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Form Sumber Saldo -->
    <x-modal wire:model="showModal" maxWidth="md" :title="$editingSourceId ? 'Ubah Sumber Saldo' : 'Tambah Sumber Saldo'">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Nama Sumber Saldo</label>
                <input
                    type="text"
                    wire:model.blur="name"
                    placeholder="Contoh: Dompet Tunai, BCA Utama, GoPay"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                />
                @error('name')
                    <p class="mt-1.5 text-xs text-keluar flex items-center gap-1">
                        <span>⚠</span> {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Tipe</label>
                <select
                    wire:model="type"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                >
                    <option value="cash">Cash / Tunai</option>
                    <option value="bank">Bank / Rekening</option>
                    <option value="ewallet">E-Wallet</option>
                    <option value="other">Lainnya</option>
                </select>
                @error('type')
                    <p class="mt-1.5 text-xs text-keluar">{{ $message }}</p>
                @enderror
            </div>

            @if(!$editingSourceId)
                <div>
                    <label class="block text-sm font-medium text-tinta mb-1">Saldo Awal</label>
                    <x-money-input wire:model="initial_balance" placeholder="0" />
                    <p class="mt-1 text-xs text-tinta-redup">Saldo awal yang saat ini ada di akun/dompet Anda.</p>
                    @error('initial_balance')
                        <p class="mt-1.5 text-xs text-keluar">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-tinta mb-2">Pilih Warna Penanda</label>
                <div class="flex flex-wrap gap-2.5">
                    @foreach($palette as $hex)
                        <button
                            type="button"
                            wire:click="$set('color', '{{ $hex }}')"
                            class="w-7 h-7 rounded-full transition-transform focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-batik"
                            style="background-color: {{ $hex }};"
                            :class="'{{ $color }}' === '{{ $hex }}' ? 'ring-2 ring-batik scale-110' : 'hover:scale-105'"
                        ></button>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-garis">
                <button
                    type="button"
                    wire:click="$set('showModal', false)"
                    class="px-4 py-2 text-sm font-medium rounded-lg border border-garis text-tinta hover:bg-kertas transition"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-semibold rounded-lg bg-batik text-white hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-batik disabled:opacity-50"
                >
                    <span wire:loading.remove>Simpan</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        </form>
    </x-modal>
</div>
