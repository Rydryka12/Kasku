<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-tinta font-heading">Transaksi Berulang</h1>
            <p class="text-sm text-tinta-redup">Jadwalkan tagihan, langganan, dan pemasukan rutin otomatis.</p>
        </div>
        <div>
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-batik text-white font-medium text-sm hover:bg-batik-gelap transition shadow-xs focus:ring-2 focus:ring-batik focus:outline-none"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Tambah Jadwal</span>
            </button>
        </div>
    </div>

    <!-- Main Grid: Jadwal & Riwayat Eksekusi -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Jadwal (2/3) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-permukaan border border-garis rounded-xl p-4 sm:p-5 shadow-xs">
                <h2 class="text-base font-semibold text-tinta mb-4 flex items-center justify-between">
                    <span>Daftar Jadwal</span>
                    <span class="text-xs font-normal text-tinta-redup">Klik jadwal untuk memfilter riwayat</span>
                </h2>

                @if($recurrings->isEmpty())
                    <div class="text-center py-10">
                        <div class="w-12 h-12 rounded-full bg-garis/50 text-tinta-redup flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-tinta">Belum ada transaksi berulang</p>
                        <p class="text-xs text-tinta-redup mt-1">Buat jadwal pengeluaran rutin seperti langganan atau tagihan bulanan.</p>
                    </div>
                @else
                    <div class="divide-y divide-garis">
                        @foreach($recurrings as $rec)
                            @php
                                $lastLog = $rec->logs->first();
                                $hasFailed = $lastLog && $lastLog->status === 'failed';
                                $isSelected = $selectedRecurringId === $rec->id;
                            @endphp
                            <div class="py-3 sm:py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition rounded-lg px-2 {{ $isSelected ? 'bg-kertas' : 'hover:bg-kertas/50' }}">
                                <div class="flex items-start gap-3 cursor-pointer flex-1" wire:click="selectRecurring({{ $rec->id }})">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 {{ $rec->type->value === 'income' ? 'bg-masuk' : 'bg-keluar' }}">
                                        @if($rec->type->value === 'income')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-sm font-semibold text-tinta">
                                                {{ $rec->description ?: ($rec->category?->name ?? 'Transaksi Berulang') }}
                                            </h3>
                                            <span class="text-[11px] px-2 py-0.5 rounded-full font-medium bg-garis text-tinta-redup">
                                                {{ $rec->frequency->label() }}
                                            </span>
                                            @if($rec->status->value === 'paused')
                                                <span class="text-[11px] px-2 py-0.5 rounded-full font-medium bg-amber-500/10 text-amber-700 dark:text-amber-400">
                                                    Dijeda
                                                </span>
                                            @elseif($rec->status->value === 'ended')
                                                <span class="text-[11px] px-2 py-0.5 rounded-full font-medium bg-gray-500/10 text-gray-500">
                                                    Selesai
                                                </span>
                                            @endif
                                            @if($hasFailed)
                                                <span class="text-[11px] px-2 py-0.5 rounded-full font-medium bg-keluar/10 text-keluar flex items-center gap-1">
                                                    <span>⚠</span> Gagal terakhir
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-tinta-redup mt-0.5">
                                            <span>{{ $rec->accountSource->name }}</span>
                                            @if($rec->category)
                                                <span> · {{ $rec->category->name }}</span>
                                            @endif
                                            <span> · Jatuh tempo: <strong>{{ tanggal($rec->next_run) }}</strong></span>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                                    <div class="text-right">
                                        <p class="text-sm font-bold tabular-nums {{ $rec->type->value === 'income' ? 'text-masuk' : 'text-keluar' }}">
                                            {{ $rec->type->value === 'income' ? '+' : '-' }}{{ rupiah($rec->amount + $rec->tax_amount) }}
                                        </p>
                                        @if($rec->tax_amount > 0)
                                            <p class="text-[10px] text-tinta-redup">(Termasuk pajak {{ rupiah($rec->tax_amount) }})</p>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-1">
                                        @if($rec->status->value !== 'ended')
                                            <button
                                                type="button"
                                                wire:click="togglePause({{ $rec->id }})"
                                                title="{{ $rec->status->value === 'active' ? 'Jeda' : 'Aktifkan' }}"
                                                class="p-1.5 rounded-lg text-tinta-redup hover:text-tinta hover:bg-garis/50 transition text-xs"
                                            >
                                                @if($rec->status->value === 'active')
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @endif
                                            </button>
                                        @endif

                                        <button
                                            type="button"
                                            wire:click="edit({{ $rec->id }})"
                                            title="Ubah"
                                            class="p-1.5 rounded-lg text-tinta-redup hover:text-tinta hover:bg-garis/50 transition text-xs"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="delete({{ $rec->id }})"
                                            wire:confirm="Yakin ingin menghapus jadwal transaksi berulang ini?"
                                            title="Hapus"
                                            class="p-1.5 rounded-lg text-tinta-redup hover:text-keluar hover:bg-keluar/10 transition text-xs"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $recurrings->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Kolom Riwayat Eksekusi (1/3) -->
        <div class="space-y-4">
            <div class="bg-permukaan border border-garis rounded-xl p-4 sm:p-5 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-tinta font-heading">Riwayat Eksekusi</h2>
                    @if($selectedRecurringId)
                        <button
                            type="button"
                            wire:click="selectRecurring(null)"
                            class="text-xs text-batik hover:underline"
                        >
                            Tampilkan Semua
                        </button>
                    @endif
                </div>

                @if($logs->isEmpty())
                    <p class="text-xs text-tinta-redup py-4 text-center">Belum ada riwayat eksekusi.</p>
                @else
                    <div class="space-y-3">
                        @foreach($logs as $log)
                            <div class="p-3 rounded-lg border {{ $log->status === 'success' ? 'border-garis bg-kertas/40' : 'border-keluar/30 bg-keluar/5' }} text-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-tinta">
                                        {{ $log->recurringTransaction->description ?: ($log->recurringTransaction->category?->name ?? 'Transaksi Berulang') }}
                                    </span>
                                    <span class="font-semibold {{ $log->status === 'success' ? 'text-masuk' : 'text-keluar' }}">
                                        {{ $log->status === 'success' ? 'Berhasil' : 'Gagal' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-tinta-redup text-[11px]">
                                    <span>{{ tanggal($log->run_date) }}</span>
                                    <span>{{ rupiah($log->recurringTransaction->amount) }}</span>
                                </div>
                                @if($log->message)
                                    <p class="text-[11px] {{ $log->status === 'success' ? 'text-tinta-redup' : 'text-keluar' }}">
                                        {{ $log->message }}
                                    </p>
                                @endif
                                @if($log->status === 'failed')
                                    <div class="pt-1.5 flex justify-end">
                                        <button
                                            type="button"
                                            wire:click="retryLog({{ $log->id }})"
                                            class="px-2 py-1 bg-batik text-white rounded text-[11px] font-medium hover:bg-batik-gelap transition flex items-center gap-1 shadow-2xs"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            <span>Jalankan ulang</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Form Tambah/Ubah -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-permukaan border border-garis rounded-2xl w-full max-w-lg p-6 space-y-4 shadow-xl">
                <div class="flex items-center justify-between border-b border-garis pb-3">
                    <h3 class="text-lg font-bold text-tinta font-heading">
                        {{ $editingId ? 'Ubah Jadwal Transaksi' : 'Tambah Transaksi Berulang' }}
                    </h3>
                    <button
                        type="button"
                        wire:click="$set('showModal', false)"
                        class="text-tinta-redup hover:text-tinta text-xl leading-none"
                    >
                        &times;
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <!-- Segmented Tipe -->
                    <div class="grid grid-cols-2 gap-1 p-1 bg-kertas rounded-xl border border-garis">
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

                    <!-- Nominal -->
                    <div>
                        <label class="block text-sm font-medium text-tinta mb-1">Nominal</label>
                        <x-money-input wire:model="amount" placeholder="0" />
                        @error('amount')
                            <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sumber & Kategori -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-tinta mb-1">Sumber Saldo</label>
                            <select
                                wire:model="account_source_id"
                                class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                            >
                                <option value="">Pilih Sumber</option>
                                @foreach($sources as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ rupiah($s->current_balance) }})</option>
                                @endforeach
                            </select>
                            @error('account_source_id')
                                <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-tinta mb-1">Kategori (Opsional)</label>
                            <select
                                wire:model="category_id"
                                class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                            >
                                <option value="">Tanpa Kategori</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Frekuensi & Tanggal Mulai -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-tinta mb-1">Frekuensi</label>
                            <select
                                wire:model="frequency"
                                class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                            >
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                                <option value="yearly">Tahunan</option>
                            </select>
                            @error('frequency')
                                <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-tinta mb-1">Tanggal Mulai</label>
                            <input
                                type="date"
                                wire:model="start_date"
                                class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                            />
                            @error('start_date')
                                <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Tanggal Berakhir (Opsional) -->
                    <div>
                        <label class="block text-sm font-medium text-tinta mb-1">Tanggal Berakhir (Opsional)</label>
                        <input
                            type="date"
                            wire:model="end_date"
                            placeholder="Biarkan kosong jika tanpa batas"
                            class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                        />
                        @error('end_date')
                            <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi / Nama Tagihan -->
                    <div>
                        <label class="block text-sm font-medium text-tinta mb-1">Keterangan / Nama Tagihan</label>
                        <input
                            type="text"
                            wire:model="description"
                            placeholder="Contoh: Langganan Netflix, Tagihan Listrik"
                            class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                        />
                        @error('description')
                            <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pajak (khusus pengeluaran, K-07) -->
                    @if($type === 'expense')
                        <div class="border border-garis/80 rounded-xl p-3 bg-kertas/30 space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-tinta">
                                <input
                                    type="checkbox"
                                    wire:model.live="has_tax"
                                    class="rounded border-garis text-batik focus:ring-batik"
                                />
                                <span>Ada pajak / biaya admin</span>
                            </label>

                            @if($has_tax)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                    <div>
                                        <label class="block text-xs text-tinta-redup mb-1">Nominal Pajak</label>
                                        <x-money-input wire:model="tax_amount" placeholder="0" />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-tinta-redup mb-1">Keterangan Pajak</label>
                                        <input
                                            type="text"
                                            wire:model="tax_note"
                                            placeholder="Contoh: PB1, Admin"
                                            class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                                        />
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-garis">
                        <button
                            type="button"
                            wire:click="$set('showModal', false)"
                            class="px-4 py-2 text-sm font-medium text-tinta-redup hover:text-tinta rounded-lg border border-garis hover:bg-kertas transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2 text-sm font-medium text-white bg-batik rounded-lg hover:bg-batik-gelap transition shadow-xs focus:ring-2 focus:ring-batik focus:outline-none"
                        >
                            Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
