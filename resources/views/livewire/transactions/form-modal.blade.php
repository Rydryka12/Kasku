<x-modal wire:model="show" maxWidth="md" :title="$editingTransactionId ? 'Ubah Transaksi' : 'Catat Transaksi'">
    <form wire:submit="save" class="space-y-4">
        <!-- Segmented Type Picker -->
        <div class="grid grid-cols-2 p-1 rounded-xl bg-kertas border border-garis">
            <button
                type="button"
                wire:click="$set('type', 'expense')"
                class="py-2 text-sm font-semibold rounded-lg transition {{ $type === 'expense' ? 'bg-keluar text-white shadow-xs' : 'text-tinta-redup hover:text-tinta' }}"
            >
                Pengeluaran
            </button>
            <button
                type="button"
                wire:click="$set('type', 'income')"
                class="py-2 text-sm font-semibold rounded-lg transition {{ $type === 'income' ? 'bg-masuk text-white shadow-xs' : 'text-tinta-redup hover:text-tinta' }}"
            >
                Pemasukan
            </button>
        </div>

        <!-- Nominal Input (x-money-input) -->
        <div>
            <label class="block text-sm font-medium text-tinta mb-1">Nominal</label>
            <x-money-input wire:model="amount" placeholder="0" autofocus />
            @error('amount')
                <p class="mt-1.5 text-xs text-keluar flex items-center gap-1">
                    <span>⚠</span> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Sumber Saldo & Kategori -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Sumber Saldo</label>
                <select
                    wire:model="account_source_id"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                >
                    <option value="">Pilih Sumber</option>
                    @foreach($sources as $source)
                        <option value="{{ $source->id }}">
                            {{ $source->name }} ({{ rupiah($source->current_balance) }})
                        </option>
                    @endforeach
                </select>
                @error('account_source_id')
                    <p class="mt-1.5 text-xs text-keluar">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Kategori</label>
                <select
                    wire:model.live="category_id"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                >
                    <option value="">Pilih Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1.5 text-xs text-keluar">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if($budgetWarning)
            <div class="p-3 rounded-lg border {{ $budgetWarning['status'] === 'exceeded' ? 'bg-keluar/10 border-keluar/30 text-keluar' : 'bg-amber-500/10 border-amber-500/30 text-amber-700 dark:text-amber-300' }} text-xs flex items-center gap-2">
                <span class="font-bold text-sm">⚠</span>
                <div>
                    <span class="font-medium">{{ $budgetWarning['category_name'] }}:</span>
                    <span>{{ $budgetWarning['message'] }}</span>
                    <span class="text-[10px] opacity-75 ml-1">(Informasi saja, tidak memblokir simpan)</span>
                </div>
            </div>
        @endif

        <!-- Tanggal -->
        <div>
            <label class="block text-sm font-medium text-tinta mb-1">Tanggal</label>
            <input
                type="date"
                wire:model="date"
                class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
            />
            @error('date')
                <p class="mt-1.5 text-xs text-keluar">{{ $message }}</p>
            @enderror
        </div>

        <!-- Catatan / Deskripsi -->
        <div>
            <label class="block text-sm font-medium text-tinta mb-1">Catatan (Opsional)</label>
            <input
                type="text"
                wire:model="description"
                placeholder="Contoh: Makan siang bersama tim"
                class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
            />
            @error('description')
                <p class="mt-1.5 text-xs text-keluar">{{ $message }}</p>
            @enderror
        </div>

        <!-- Foto Struk (Opsional) -->
        <div>
            <label class="block text-sm font-medium text-tinta mb-1">Lampiran Struk (Opsional)</label>
            <input
                type="file"
                wire:model="receipt"
                accept="image/*"
                class="block w-full text-xs text-tinta-redup file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-kertas file:text-tinta hover:file:bg-garis"
            />
            @error('receipt')
                <p class="mt-1.5 text-xs text-keluar">{{ $message }}</p>
            @enderror
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-2 pt-4 border-t border-garis">
            <button
                type="button"
                wire:click="$set('show', false)"
                class="px-4 py-2 text-sm font-medium rounded-lg border border-garis text-tinta hover:bg-kertas transition"
            >
                Batal
            </button>
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="px-5 py-2 text-sm font-semibold rounded-lg bg-batik text-white hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-batik disabled:opacity-50"
            >
                <span wire:loading.remove>Simpan Transaksi</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </div>
    </form>
</x-modal>
