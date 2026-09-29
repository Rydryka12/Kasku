<div>
    <x-slot name="header">
        Kategori
    </x-slot>

    <div class="space-y-6">
        <!-- Top bar: Tab Switcher & Tambah Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Segmented Control -->
            <div class="inline-flex p-1 rounded-xl bg-permukaan border border-garis">
                <button
                    type="button"
                    wire:click="setTab('expense')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $activeTab === 'expense' ? 'bg-keluar/10 text-keluar font-bold' : 'text-tinta-redup hover:text-tinta' }}"
                >
                    Pengeluaran
                </button>
                <button
                    type="button"
                    wire:click="setTab('income')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $activeTab === 'income' ? 'bg-masuk/10 text-masuk font-bold' : 'text-tinta-redup hover:text-tinta' }}"
                >
                    Pemasukan
                </button>
            </div>

            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-batik text-white text-sm font-semibold hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-batik"
            >
                <span>+</span>
                <span>Tambah kategori</span>
            </button>
        </div>

        <!-- Daftar Kategori Aktif (Baris Buku Besar) -->
        <div class="bg-permukaan border border-garis rounded-2xl overflow-hidden divide-y divide-garis">
            @forelse($categories as $category)
                <div class="flex items-center justify-between p-4 hover:bg-kertas/50 transition">
                    <div class="flex items-center gap-3">
                        <span class="w-3.5 h-3.5 rounded-full shrink-0" style="background-color: {{ $category->color }};"></span>
                        <span class="text-sm font-semibold text-tinta">{{ $category->name }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="edit({{ $category->id }})"
                            class="p-1.5 text-tinta-redup hover:text-tinta rounded-lg hover:bg-kertas transition text-xs font-medium"
                        >
                            Ubah
                        </button>
                        <button
                            type="button"
                            wire:click="toggleArchive({{ $category->id }})"
                            class="p-1.5 text-tinta-redup hover:text-kunyit rounded-lg hover:bg-kertas transition text-xs font-medium"
                        >
                            Arsipkan
                        </button>
                        <button
                            type="button"
                            wire:confirm="Yakin ingin menghapus kategori ini?"
                            wire:click="delete({{ $category->id }})"
                            class="p-1.5 text-tinta-redup hover:text-keluar rounded-lg hover:bg-kertas transition text-xs font-medium"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8">
                    <x-empty
                        title="Belum ada kategori"
                        description="Tambahkan kategori untuk memudahkan pengelompokan pemasukan dan pengeluaran Anda."
                        actionText="Tambah kategori"
                        actionWire="openCreateModal"
                    />
                </div>
            @endforelse
        </div>

        <!-- Daftar Arsip (Bila ada) -->
        @if($archivedCategories->isNotEmpty())
            <div x-data="{ openArchive: false }" class="border border-garis rounded-2xl bg-permukaan overflow-hidden">
                <button
                    type="button"
                    @click="openArchive = !openArchive"
                    class="w-full flex items-center justify-between p-4 text-sm font-semibold text-tinta-redup hover:text-tinta transition"
                >
                    <span>Kategori Diarsipkan ({{ $archivedCategories->count() }})</span>
                    <svg class="w-4 h-4 transition-transform" :class="openArchive ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="openArchive" class="divide-y divide-garis border-t border-garis">
                    @foreach($archivedCategories as $archived)
                        <div class="flex items-center justify-between p-4 bg-kertas/30">
                            <div class="flex items-center gap-3">
                                <span class="w-3.5 h-3.5 rounded-full shrink-0 opacity-50" style="background-color: {{ $archived->color }};"></span>
                                <span class="text-sm font-medium text-tinta-redup line-through">{{ $archived->name }}</span>
                                <x-badge variant="arsip">Diarsipkan</x-badge>
                            </div>

                            <button
                                type="button"
                                wire:click="toggleArchive({{ $archived->id }})"
                                class="px-3 py-1 text-xs font-semibold rounded-lg border border-garis text-tinta hover:bg-permukaan transition"
                            >
                                Pulihkan
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Form Kategori -->
    <x-modal wire:model="showModal" maxWidth="md" :title="$editingCategoryId ? 'Ubah Kategori' : 'Tambah Kategori'">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Nama Kategori</label>
                <input
                    type="text"
                    wire:model.blur="name"
                    placeholder="Contoh: Makan Siang, Transportasi"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                />
                @error('name')
                    <p class="mt-1.5 text-xs text-keluar flex items-center gap-1">
                        <span>⚠</span> {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-tinta mb-2">Pilih Warna</label>
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
                    <span wire:loading.remove>Simpan kategori</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        </form>
    </x-modal>
</div>
