<div>
    <x-slot name="header">
        Template Kategori Default Sistem
    </x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
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
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-batik text-white text-sm font-semibold hover:opacity-90 transition shadow-xs"
            >
                <span>+</span>
                <span>Tambah Template Kategori</span>
            </button>
        </div>

        <div class="p-3.5 rounded-xl bg-batik/5 border border-batik/20 text-xs text-tinta flex items-center gap-2">
            <span class="text-batik font-bold">ℹ</span>
            <span><strong>Catatan:</strong> Perubahan template kategori hanya berlaku untuk pengguna baru yang mendaftar (K-18, DESIGN 5.13). Data pengguna yang sudah ada tidak terpengaruh.</span>
        </div>

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
                            class="p-1.5 text-tinta-redup hover:text-tinta rounded-lg text-xs font-medium"
                        >
                            Ubah
                        </button>
                        <button
                            type="button"
                            wire:confirm="Yakin ingin menghapus template ini?"
                            wire:click="delete({{ $category->id }})"
                            class="p-1.5 text-tinta-redup hover:text-keluar rounded-lg text-xs font-medium"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8">
                    <x-empty title="Belum ada template" description="Template ini akan disalin otomatis untuk pengguna baru saat registrasi." />
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Form -->
    <x-modal wire:model="showModal" maxWidth="md" :title="$editingCategoryId ? 'Ubah Template Kategori' : 'Tambah Template Kategori'">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Nama Kategori</label>
                <input
                    type="text"
                    wire:model="name"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-tinta mb-2">Pilih Warna</label>
                <div class="flex flex-wrap gap-2.5">
                    @foreach($palette as $hex)
                        <button
                            type="button"
                            wire:click="$set('color', '{{ $hex }}')"
                            class="w-7 h-7 rounded-full transition-transform focus:outline-none"
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
                    class="px-4 py-2 text-sm font-medium rounded-lg border border-garis text-tinta hover:bg-kertas"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 text-sm font-semibold rounded-lg bg-batik text-white hover:opacity-90"
                >
                    Simpan Template
                </button>
            </div>
        </form>
    </x-modal>
</div>
