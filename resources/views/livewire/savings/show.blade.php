<div class="space-y-6">
    <!-- Back button & Header -->
    <div>
        <a href="{{ route('savings.index') }}" wire:navigate class="inline-flex items-center gap-1.5 text-xs font-semibold text-tinta-redup hover:text-tinta transition mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Kembali ke Tabungan</span>
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-kertas border border-garis flex items-center justify-center text-batik font-bold text-2xl shadow-2xs">
                    🎯
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold text-tinta tracking-tight">{{ $goal->name }}</h1>
                        <x-badge :variant="$goal->status->value === 'achieved' ? 'success' : ($goal->status->value === 'cancelled' ? 'neutral' : 'info')">
                            {{ $goal->status->label() }}
                        </x-badge>
                    </div>
                    @if ($goal->deadline)
                        <p class="text-xs text-tinta-redup mt-0.5">
                            Tenggat waktu: {{ $goal->deadline->translatedFormat('d M Y') }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-2">
                @if ($goal->status->value !== 'cancelled')
                    <button
                        type="button"
                        wire:click="openEntryModal('withdraw')"
                        class="px-4 py-2 text-sm font-semibold rounded-xl border border-garis bg-permukaan text-tinta hover:bg-kertas transition shadow-2xs"
                    >
                        Tarik
                    </button>
                    <button
                        type="button"
                        wire:click="openEntryModal('deposit')"
                        class="px-4 py-2 text-sm font-semibold rounded-xl bg-masuk text-white hover:opacity-90 transition shadow-2xs"
                    >
                        Setor
                    </button>
                @endif
                <button
                    type="button"
                    wire:click="openEditModal"
                    class="p-2 text-tinta-redup hover:text-tinta border border-garis rounded-xl bg-permukaan hover:bg-kertas transition"
                    title="Ubah Target"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Progress Card -->
    <div class="p-6 rounded-2xl bg-permukaan border border-garis shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-2">
            <div>
                <p class="text-xs text-tinta-redup uppercase tracking-wider font-semibold">Terkumpul</p>
                <p class="text-3xl font-extrabold text-tinta tabular-nums tracking-tight mt-1">
                    {{ rupiah($goal->current_amount) }}
                </p>
            </div>
            <div class="text-sm sm:text-right">
                <p class="text-xs text-tinta-redup">Target Nominal</p>
                <p class="text-lg font-bold text-tinta tabular-nums mt-0.5">
                    {{ rupiah($goal->target_amount) }}
                </p>
            </div>
        </div>

        <x-progress
            :value="$goal->progressPercentage()"
            :color="$goal->status->value === 'achieved' ? 'bg-masuk' : 'bg-batik'"
        />

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 text-xs">
            <div class="p-3 rounded-xl bg-kertas border border-garis/50">
                <span class="text-tinta-redup block">Persentase</span>
                <span class="text-sm font-bold text-tinta tabular-nums">{{ $goal->progressPercentage() }}%</span>
            </div>
            <div class="p-3 rounded-xl bg-kertas border border-garis/50">
                <span class="text-tinta-redup block">Sisa Yang Dibutuhkan</span>
                <span class="text-sm font-bold text-tinta tabular-nums">{{ rupiah($goal->remainingAmount()) }}</span>
            </div>
            <div class="p-3 rounded-xl bg-kertas border border-garis/50">
                <span class="text-tinta-redup block">Perkiraan Tercapai</span>
                <span class="text-sm font-bold text-tinta">{{ $goal->estimateAchievedMonth() ?? 'belum bisa diperkirakan' }}</span>
            </div>
        </div>

        @if ($goal->status->value !== 'cancelled')
            <div class="pt-3 border-t border-garis flex items-center justify-between text-xs text-tinta-redup">
                <span>Ingin menghentikan target ini?</span>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        wire:click="openCancelModal"
                        class="text-keluar hover:underline font-semibold"
                    >
                        Batalkan Target
                    </button>
                    <span>·</span>
                    <button
                        type="button"
                        wire:click="openDeleteModal"
                        class="text-tinta-redup hover:text-keluar font-semibold"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Riwayat Setor & Tarik -->
    <div class="space-y-3">
        <h2 class="text-base font-bold text-tinta">Riwayat Setoran & Penarikan</h2>

        @if ($entries->isEmpty())
            <div class="p-8 rounded-2xl bg-permukaan border border-garis text-center">
                <p class="text-sm font-semibold text-tinta">Belum ada riwayat transaksi tabungan</p>
                <p class="text-xs text-tinta-redup mt-1">Lakukan setoran pertama untuk mulai mengumpulkan dana target.</p>
            </div>
        @else
            <div class="overflow-hidden rounded-2xl border border-garis bg-permukaan shadow-xs">
                <table class="min-w-full divide-y divide-garis text-left text-sm">
                    <thead class="bg-kertas text-xs font-semibold text-tinta-redup">
                        <tr>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Tipe</th>
                            <th class="py-3 px-4">Sumber Saldo</th>
                            <th class="py-3 px-4">Catatan</th>
                            <th class="py-3 px-4 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis">
                        @foreach ($entries as $entry)
                            <tr class="hover:bg-kertas/50 transition">
                                <td class="py-3 px-4 text-xs text-tinta-redup whitespace-nowrap">
                                    {{ $entry->date->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <x-badge :variant="$entry->type->value === 'deposit' ? 'success' : 'warning'">
                                        {{ $entry->type->label() }}
                                    </x-badge>
                                </td>
                                <td class="py-3 px-4 text-sm font-medium text-tinta whitespace-nowrap">
                                    {{ $entry->source->name ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-xs text-tinta-redup">
                                    {{ $entry->notes ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-sm font-bold text-right tabular-nums whitespace-nowrap {{ $entry->type->value === 'deposit' ? 'text-masuk' : 'text-keluar' }}">
                                    {{ $entry->type->value === 'deposit' ? '+' : '-' }}{{ rupiah($entry->amount) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-2">
                {{ $entries->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Setor / Tarik -->
    <x-modal
        wire:model="showEntryModal"
        :title="$entryType === 'deposit' ? 'Setor ke Tabungan' : 'Tarik dari Tabungan'"
    >
        <form wire:submit="submitEntry" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">
                    {{ $entryType === 'deposit' ? 'Sumber Saldo (Dipotong)' : 'Tujuan Saldo (Ditambah)' }}
                </label>
                <select
                    wire:model.live="selectedSourceId"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                    required
                >
                    <option value="">Pilih Sumber Saldo</option>
                    @foreach ($sources as $src)
                        <option value="{{ $src->id }}">
                            {{ $src->name }} (Saldo: {{ rupiah($src->current_balance) }})
                        </option>
                    @endforeach
                </select>
                @error('selectedSourceId') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Nominal</label>
                <x-money-input wire:model.live="entryAmount" placeholder="0" autofocus />
                @error('entryAmount') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Pratinjau Saldo -->
            @if ($selectedSource && $entryAmount > 0)
                @php
                    $sourceOld = $selectedSource->current_balance;
                    $sourceNew = $entryType === 'deposit'
                        ? $sourceOld - $entryAmount
                        : $sourceOld + $entryAmount;
                @endphp
                <div class="p-3 rounded-xl bg-kertas border border-garis text-xs space-y-1">
                    <p class="font-semibold text-tinta">Pratinjau Saldo:</p>
                    <p class="text-tinta-redup">
                        {{ $selectedSource->name }}:
                        <span class="tabular-nums font-medium text-tinta">{{ rupiah($sourceOld) }}</span>
                        →
                        <span class="tabular-nums font-bold {{ $sourceNew < 0 ? 'text-keluar' : 'text-masuk' }}">
                            {{ rupiah($sourceNew) }}
                        </span>
                    </p>
                    @if ($sourceNew < 0 && $entryType === 'deposit')
                        <p class="text-keluar font-semibold">⚠️ Saldo tidak mencukupi untuk melakukan setoran ini.</p>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-tinta mb-1">Tanggal</label>
                    <input
                        type="date"
                        wire:model="entryDate"
                        class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                        required
                    />
                    @error('entryDate') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-tinta mb-1">Catatan (Opsional)</label>
                    <input
                        type="text"
                        wire:model="entryNotes"
                        placeholder="Keterangan transaksi"
                        class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                    />
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-garis">
                <button
                    type="button"
                    wire:click="$set('showEntryModal', false)"
                    class="px-4 py-2 text-sm font-medium rounded-lg border border-garis text-tinta hover:bg-kertas transition"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 text-sm font-semibold rounded-lg bg-batik text-white hover:opacity-90 transition"
                >
                    Simpan
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Ubah Target -->
    <x-modal wire:model="showEditModal" title="Ubah Target Tabungan">
        <form wire:submit="saveEdit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Nama Target</label>
                <input
                    type="text"
                    wire:model="name"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                    required
                />
                @error('name') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Nominal Target</label>
                <x-money-input wire:model="target_amount" />
                @error('target_amount') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Tenggat Waktu</label>
                <input
                    type="date"
                    wire:model="deadline"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                />
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-garis">
                <button
                    type="button"
                    wire:click="$set('showEditModal', false)"
                    class="px-4 py-2 text-sm font-medium rounded-lg border border-garis text-tinta hover:bg-kertas transition"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 text-sm font-semibold rounded-lg bg-batik text-white hover:opacity-90 transition"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Batalkan Target (K-09, FR-SAV-07) -->
    <x-modal wire:model="showCancelModal" title="Batalkan Target Tabungan">
        <form wire:submit="confirmCancel" class="space-y-4">
            <p class="text-sm text-tinta">
                Apakah Anda yakin ingin membatalkan target <span class="font-bold">{{ $goal->name }}</span>?
            </p>

            @if ($goal->current_amount > 0)
                <div class="p-3 rounded-xl bg-kertas border border-garis space-y-2">
                    <p class="text-xs text-tinta-redup">
                        Dana tersimpan sebesar <span class="font-bold text-tinta">{{ rupiah($goal->current_amount) }}</span> akan dikembalikan sepenuhnya ke sumber saldo pilihan Anda:
                    </p>
                    <select
                        wire:model="cancelReturnSourceId"
                        class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                        required
                    >
                        <option value="">Pilih Sumber Saldo Pengembalian</option>
                        @foreach ($sources as $src)
                            <option value="{{ $src->id }}">{{ $src->name }}</option>
                        @endforeach
                    </select>
                    @error('cancelReturnSourceId') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-garis">
                <button
                    type="button"
                    wire:click="$set('showCancelModal', false)"
                    class="px-4 py-2 text-sm font-medium rounded-lg border border-garis text-tinta hover:bg-kertas transition"
                >
                    Tutup
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 text-sm font-semibold rounded-lg bg-keluar text-white hover:opacity-90 transition"
                >
                    Ya, Batalkan Target
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Hapus Target -->
    <x-modal wire:model="showDeleteModal" title="Hapus Target Tabungan">
        <form wire:submit="confirmDelete" class="space-y-4">
            <p class="text-sm text-tinta">
                Target <span class="font-bold">{{ $goal->name }}</span> beserta seluruh riwayatnya akan dihapus permanen.
            </p>

            @if ($goal->current_amount > 0)
                <div class="p-3 rounded-xl bg-kertas border border-garis space-y-2">
                    <p class="text-xs text-tinta-redup">
                        Sebelum dihapus, dana tersimpan <span class="font-bold text-tinta">{{ rupiah($goal->current_amount) }}</span> wajib dikembalikan ke sumber saldo:
                    </p>
                    <select
                        wire:model="deleteReturnSourceId"
                        class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                        required
                    >
                        <option value="">Pilih Sumber Saldo Pengembalian</option>
                        @foreach ($sources as $src)
                            <option value="{{ $src->id }}">{{ $src->name }}</option>
                        @endforeach
                    </select>
                    @error('deleteReturnSourceId') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-garis">
                <button
                    type="button"
                    wire:click="$set('showDeleteModal', false)"
                    class="px-4 py-2 text-sm font-medium rounded-lg border border-garis text-tinta hover:bg-kertas transition"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 text-sm font-semibold rounded-lg bg-keluar text-white hover:opacity-90 transition"
                >
                    Hapus Permanen
                </button>
            </div>
        </form>
    </x-modal>
</div>
