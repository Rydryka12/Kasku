<div>
    <x-slot name="header">
        Transfer Antar Sumber Saldo
    </x-slot>

    <div class="space-y-6">
        <!-- Header & Action -->
        <div class="flex items-center justify-between">
            <p class="text-sm text-tinta-redup">Pindahkan dana antar rekening, e-wallet, atau uang tunai Anda.</p>
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-batik text-white text-sm font-semibold hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-batik"
            >
                <span>+</span>
                <span>Transfer Dana</span>
            </button>
        </div>

        <!-- Daftar Transfer -->
        <div class="bg-permukaan border border-garis rounded-2xl overflow-hidden divide-y divide-garis">
            @forelse($transfers as $tr)
                <div class="p-4 flex items-center justify-between hover:bg-kertas/30 transition">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-tinta">{{ $tr->fromSource->name ?? '-' }}</span>
                            <span class="text-xs text-tinta-redup font-bold">→</span>
                            <span class="text-sm font-semibold text-tinta">{{ $tr->toSource->name ?? '-' }}</span>
                        </div>
                        <p class="text-xs text-tinta-redup">
                            {{ $tr->date->translatedFormat('d M Y') }}
                            @if($tr->admin_fee > 0)
                                • Biaya Admin: {{ rupiah($tr->admin_fee) }}
                            @endif
                            @if($tr->notes)
                                • {{ $tr->notes }}
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-end">
                            <x-money :value="$tr->amount" class="text-base text-tinta font-bold" />
                        </div>

                        <button
                            type="button"
                            wire:confirm="Yakin ingin membatalkan transfer ini? Saldo kedua sumber akan dikembalikan."
                            wire:click="delete({{ $tr->id }})"
                            class="p-1.5 text-tinta-redup hover:text-keluar rounded-lg hover:bg-kertas transition text-xs font-medium"
                            title="Batalkan Transfer"
                        >
                            Batalkan
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8">
                    <x-empty
                        title="Belum ada transfer"
                        description="Pindahkan dana antar rekening, e-wallet, atau kas tunai Anda dengan mudah."
                        actionText="Transfer Dana Sekarang"
                        actionWire="openCreateModal"
                    />
                </div>
            @endforelse
        </div>

        <div>
            {{ $transfers->links() }}
        </div>
    </div>

    <!-- Modal Form Transfer -->
    <x-modal wire:model="showModal" maxWidth="md" title="Transfer Dana">
        <form wire:submit="save" class="space-y-4">
            <!-- Dari Sumber -> Ke Sumber -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-tinta mb-1">Dari Akun</label>
                    <select
                        wire:model="from_source_id"
                        class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                    >
                        <option value="">Pilih Sumber Asal</option>
                        @foreach($sources as $src)
                            <option value="{{ $src->id }}">{{ $src->name }} ({{ rupiah($src->current_balance) }})</option>
                        @endforeach
                    </select>
                    @error('from_source_id')
                        <p class="mt-1.5 text-xs text-keluar">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-tinta mb-1">Ke Akun</label>
                    <select
                        wire:model="to_source_id"
                        class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                    >
                        <option value="">Pilih Sumber Tujuan</option>
                        @foreach($sources as $src)
                            <option value="{{ $src->id }}">{{ $src->name }} ({{ rupiah($src->current_balance) }})</option>
                        @endforeach
                    </select>
                    @error('to_source_id')
                        <p class="mt-1.5 text-xs text-keluar">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Nominal Transfer -->
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Nominal Transfer</label>
                <x-money-input wire:model="amount" placeholder="0" />
                @error('amount')
                    <p class="mt-1.5 text-xs text-keluar flex items-center gap-1">
                        <span>⚠</span> {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Biaya Admin -->
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Biaya Admin (Bila ada)</label>
                <x-money-input wire:model="admin_fee" placeholder="0" />
                <p class="mt-1 text-xs text-tinta-redup">Biaya admin dipotong dari akun asal bersamaan dengan nominal transfer.</p>
                @error('admin_fee')
                    <p class="mt-1.5 text-xs text-keluar">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tanggal -->
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Tanggal Transfer</label>
                <input
                    type="date"
                    wire:model="date"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                />
                @error('date')
                    <p class="mt-1.5 text-xs text-keluar">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catatan -->
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Catatan (Opsional)</label>
                <input
                    type="text"
                    wire:model="notes"
                    placeholder="Contoh: Top up e-wallet bulanan"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                />
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
                    class="px-5 py-2 text-sm font-semibold rounded-lg bg-batik text-white hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-batik disabled:opacity-50"
                >
                    <span wire:loading.remove>Kirim Transfer</span>
                    <span wire:loading>Memproses...</span>
                </button>
            </div>
        </form>
    </x-modal>
</div>
