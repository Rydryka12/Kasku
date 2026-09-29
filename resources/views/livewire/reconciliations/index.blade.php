<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-tinta font-heading">Rekonsiliasi Saldo</h1>
            <p class="text-sm text-tinta-redup">Cocokkan saldo di aplikasi dengan saldo fisik/asli rekening Anda.</p>
        </div>
    </div>

    <!-- Panel Form Rekonsiliasi (DESIGN 5.10) -->
    <div class="bg-permukaan border border-garis rounded-2xl p-5 sm:p-6 shadow-xs max-w-2xl">
        <h2 class="text-base font-bold text-tinta mb-4 font-heading">Cek &amp; Cocokkan Saldo</h2>

        <div class="space-y-4">
            <!-- Pilih Sumber Saldo -->
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Pilih Sumber Saldo</label>
                <select
                    wire:model.live="selectedSourceId"
                    class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                >
                    <option value="">Pilih Sumber Saldo</option>
                    @foreach($sources as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ rupiah($s->current_balance) }})</option>
                    @endforeach
                </select>
                @error('selectedSourceId')
                    <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                @enderror
            </div>

            @if($selectedSource)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <!-- Saldo Aplikasi -->
                    <div class="p-4 rounded-xl bg-kertas border border-garis">
                        <p class="text-xs font-medium text-tinta-redup uppercase tracking-wider">Saldo di Aplikasi</p>
                        <p class="text-xl font-bold text-tinta tabular-nums mt-1 font-heading">
                            {{ rupiah($appBalance) }}
                        </p>
                    </div>

                    <!-- Saldo Asli Input -->
                    <div>
                        <label class="block text-xs font-medium text-tinta uppercase tracking-wider mb-1">Saldo Asli (Fisik / Bank)</label>
                        <x-money-input wire:model.live="actualBalance" placeholder="0" />
                        @error('actualBalance')
                            <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Selisih Visual Box -->
                <div class="p-4 rounded-xl border {{ $difference === 0 ? 'bg-masuk/5 border-masuk/30 text-masuk' : ($difference > 0 ? 'bg-batik/5 border-batik/30 text-batik' : 'bg-keluar/5 border-keluar/30 text-keluar') }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider opacity-80">Selisih Saldo</p>
                            <p class="text-2xl font-bold tabular-nums mt-0.5 font-heading">
                                {{ $difference > 0 ? '+' : '' }}{{ rupiah($difference) }}
                            </p>
                        </div>
                        <div class="text-right text-xs">
                            @if($difference === 0)
                                <span class="px-2.5 py-1 rounded-full bg-masuk/15 text-masuk font-semibold">
                                    ✓ Saldo Cocok
                                </span>
                            @elseif($difference < 0)
                                <span class="font-medium text-keluar">
                                    Di aplikasi tercatat lebih banyak {{ rupiah(abs($difference)) }}
                                </span>
                            @else
                                <span class="font-medium text-batik">
                                    Di aplikasi tercatat lebih sedikit {{ rupiah($difference) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($difference !== 0)
                        <p class="text-[11px] mt-2 opacity-80">
                            Pilih <strong>Sesuaikan Saldo</strong> untuk otomatis membuat transaksi penyesuaian (K-04) agar saldo aplikasi sama persis dengan saldo asli.
                        </p>
                    @endif
                </div>

                <!-- Tanggal Rekonsiliasi -->
                <div>
                    <label class="block text-sm font-medium text-tinta mb-1">Tanggal</label>
                    <input
                        type="date"
                        wire:model="reconcileDate"
                        class="w-full sm:w-1/2 rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                    />
                    @error('reconcileDate')
                        <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap items-center gap-3 pt-3 border-t border-garis">
                    @if($difference !== 0)
                        <button
                            type="button"
                            wire:click="submitReconciliation(true)"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-batik rounded-lg hover:bg-batik-gelap transition shadow-xs focus:ring-2 focus:ring-batik focus:outline-none"
                        >
                            Sesuaikan Saldo (Otomatis)
                        </button>
                    @endif

                    <button
                        type="button"
                        wire:click="submitReconciliation(false)"
                        class="px-4 py-2.5 text-sm font-medium text-tinta rounded-lg border border-garis hover:bg-kertas transition focus:outline-none"
                    >
                        {{ $difference === 0 ? 'Simpan Rekonsiliasi' : 'Simpan Tanpa Menyesuaikan' }}
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Riwayat Rekonsiliasi (DESIGN 5.10) -->
    <div class="bg-permukaan border border-garis rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
        <h2 class="text-base font-bold text-tinta font-heading">Riwayat Rekonsiliasi</h2>

        @if($histories->isEmpty())
            <p class="text-xs text-tinta-redup py-4 text-center">Belum ada riwayat rekonsiliasi yang tercatat.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-garis">
                    <thead>
                        <tr class="text-xs text-tinta-redup uppercase tracking-wider">
                            <th class="py-3 px-3">Tanggal</th>
                            <th class="py-3 px-3">Sumber Saldo</th>
                            <th class="py-3 px-3 text-right">Saldo Aplikasi</th>
                            <th class="py-3 px-3 text-right">Saldo Asli</th>
                            <th class="py-3 px-3 text-right">Selisih</th>
                            <th class="py-3 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis">
                        @foreach($histories as $h)
                            <tr class="hover:bg-kertas/50 transition">
                                <td class="py-3 px-3 text-xs text-tinta font-medium">
                                    {{ tanggal($h->date) }}
                                </td>
                                <td class="py-3 px-3 text-xs text-tinta">
                                    {{ $h->accountSource->name }}
                                </td>
                                <td class="py-3 px-3 text-xs text-right tabular-nums text-tinta-redup">
                                    {{ rupiah($h->app_balance) }}
                                </td>
                                <td class="py-3 px-3 text-xs text-right tabular-nums font-semibold text-tinta">
                                    {{ rupiah($h->actual_balance) }}
                                </td>
                                <td class="py-3 px-3 text-xs text-right tabular-nums font-bold {{ $h->difference === 0 ? 'text-masuk' : ($h->difference > 0 ? 'text-batik' : 'text-keluar') }}">
                                    {{ $h->difference > 0 ? '+' : '' }}{{ rupiah($h->difference) }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($h->adjusted)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-batik/10 text-batik">
                                            Disesuaikan
                                        </span>
                                    @elseif($h->difference === 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-masuk/10 text-masuk">
                                            Cocok
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-garis text-tinta-redup">
                                            Tanpa Koreksi
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $histories->links() }}
            </div>
        @endif
    </div>
</div>
