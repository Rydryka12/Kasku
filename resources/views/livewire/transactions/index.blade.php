<div>
    <x-slot name="header">
        Transaksi
    </x-slot>

    <div class="space-y-6">
        <!-- Filter Bar -->
        <div class="bg-permukaan border border-garis rounded-2xl p-4 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Pencarian -->
                <div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari deskripsi..."
                        class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                    />
                </div>

                <!-- Tipe Transaksi -->
                <div>
                    <select
                        wire:model.live="type"
                        class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                    >
                        <option value="all">Semua Tipe</option>
                        <option value="expense">Pengeluaran</option>
                        <option value="income">Pemasukan</option>
                    </select>
                </div>

                <!-- Sumber Saldo -->
                <div>
                    <select
                        wire:model.live="account_source_id"
                        class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                    >
                        <option value="all">Semua Sumber Saldo</option>
                        @foreach($sources as $source)
                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Kategori -->
                <div>
                    <select
                        wire:model.live="category_id"
                        class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                    >
                        <option value="all">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->type->label() }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Date Range & Reset -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-garis/60">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-tinta-redup font-medium">Periode:</span>
                    <input
                        type="date"
                        wire:model.live="start_date"
                        class="rounded-lg border border-garis bg-permukaan px-2.5 py-1 text-xs text-tinta focus:border-batik focus:ring-1 focus:ring-batik"
                    />
                    <span class="text-xs text-tinta-redup">s/d</span>
                    <input
                        type="date"
                        wire:model.live="end_date"
                        class="rounded-lg border border-garis bg-permukaan px-2.5 py-1 text-xs text-tinta focus:border-batik focus:ring-1 focus:ring-batik"
                    />
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        wire:click="resetFilter"
                        class="text-xs text-tinta-redup hover:text-batik font-medium transition"
                    >
                        Reset Filter
                    </button>
                    <a
                        href="{{ route('transactions.export.excel', [
                            'type' => $type === 'all' ? '' : $type,
                            'account_source_id' => $account_source_id === 'all' ? '' : $account_source_id,
                            'category_id' => $category_id === 'all' ? '' : $category_id,
                            'start_date' => $start_date,
                            'end_date' => $end_date,
                            'search' => $search,
                        ]) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-garis bg-kertas hover:bg-garis/50 text-xs font-semibold text-tinta transition shadow-2xs"
                    >
                        <svg class="w-3.5 h-3.5 text-masuk" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Export Excel</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Daftar Transaksi (Baris Buku Besar) -->
        <div class="bg-permukaan border border-garis rounded-2xl overflow-hidden divide-y divide-garis">
            @forelse($transactions as $tx)
                <div class="p-4 flex items-center justify-between hover:bg-kertas/30 transition">
                    <div class="flex items-start gap-3">
                        <span class="w-3.5 h-3.5 rounded-full mt-1 shrink-0" style="background-color: {{ $tx->category->color ?? '#2B3A8C' }};"></span>
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-tinta">{{ $tx->category->name ?? 'Kategori' }}</span>
                                <x-source-dot :color="$tx->accountSource->color ?? '#2B3A8C'" :name="$tx->accountSource->name ?? 'Sumber'" />
                                @if($tx->receipt_path)
                                    <a href="{{ Storage::disk('public')->url($tx->receipt_path) }}" target="_blank" class="text-xs text-batik hover:underline inline-flex items-center gap-0.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        <span>Struk</span>
                                    </a>
                                @endif
                            </div>
                            <p class="text-xs text-tinta-redup">
                                {{ $tx->date->translatedFormat('d M Y') }}
                                @if($tx->description)
                                    • {{ $tx->description }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-end">
                            <x-money
                                :value="$tx->type->value === 'expense' ? -$tx->amount : $tx->amount"
                                :signed="true"
                                class="text-base {{ $tx->type->value === 'expense' ? 'text-keluar' : 'text-masuk' }}"
                            />
                        </div>

                        <!-- Dropdown Menu / Action -->
                        @if($tx->transfer_id === null)
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    @click="$dispatch('open-transaction-modal', { id: {{ $tx->id }} })"
                                    class="p-1.5 text-tinta-redup hover:text-tinta rounded-lg hover:bg-kertas transition text-xs font-medium"
                                    title="Ubah"
                                >
                                    Ubah
                                </button>
                                <button
                                    type="button"
                                    wire:confirm="Yakin ingin menghapus transaksi ini? Saldo sumber akan dikembalikan."
                                    wire:click="delete({{ $tx->id }})"
                                    class="p-1.5 text-tinta-redup hover:text-keluar rounded-lg hover:bg-kertas transition text-xs font-medium"
                                    title="Hapus"
                                >
                                    Hapus
                                </button>
                            </div>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded bg-garis text-tinta-redup">Transfer</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8">
                    <x-empty
                        title="Tidak ada transaksi"
                        description="Belum ada transaksi pada periode atau filter yang dipilih."
                        actionText="Catat Transaksi Sekarang"
                        actionClick="$dispatch('open-transaction-modal')"
                    />
                </div>
            @endforelse
        </div>

        <div>
            {{ $transactions->links() }}
        </div>
    </div>
</div>
