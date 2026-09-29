<div class="space-y-6">
    <!-- Header Page & Export Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-tinta tracking-tight">Laporan Keuangan</h1>
            <p class="text-sm text-tinta-redup mt-0.5">Analisis arus kas, distribusi kategori, mutasi saldo, dan pajak.</p>
        </div>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('reports.export.pdf', ['periodType' => $periodType, 'year' => $year, 'month' => $month, 'activeTab' => $activeTab]) }}"
                target="_blank"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl border border-garis bg-permukaan text-tinta hover:bg-kertas transition shadow-2xs"
            >
                <svg class="w-4 h-4 text-keluar" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export PDF</span>
            </a>
            <a
                href="{{ route('reports.export.excel', ['periodType' => $periodType, 'year' => $year, 'month' => $month, 'activeTab' => $activeTab]) }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-masuk text-white hover:opacity-90 transition shadow-2xs"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel</span>
            </a>
            <a
                href="{{ route('exports.index') }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl border border-garis bg-permukaan text-tinta hover:bg-kertas transition shadow-2xs"
            >
                <svg class="w-4 h-4 text-batik" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Riwayat Export</span>
            </a>
        </div>
    </div>

    <!-- Filters & Period Navigator -->
    <div class="p-4 rounded-2xl bg-permukaan border border-garis shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Segmented Period Type (Bulanan / Tahunan) -->
        <div class="inline-flex p-1 rounded-xl bg-kertas border border-garis self-start">
            <button
                type="button"
                wire:click="setPeriodType('monthly')"
                class="px-4 py-1.5 text-xs font-semibold rounded-lg transition {{ $periodType === 'monthly' ? 'bg-permukaan text-tinta shadow-xs' : 'text-tinta-redup hover:text-tinta' }}"
            >
                Bulanan
            </button>
            <button
                type="button"
                wire:click="setPeriodType('yearly')"
                class="px-4 py-1.5 text-xs font-semibold rounded-lg transition {{ $periodType === 'yearly' ? 'bg-permukaan text-tinta shadow-xs' : 'text-tinta-redup hover:text-tinta' }}"
            >
                Tahunan
            </button>
        </div>

        <!-- Period Navigator -->
        <div class="flex items-center gap-3 self-center sm:self-auto">
            <button
                type="button"
                wire:click="prevPeriod"
                class="p-1.5 rounded-lg border border-garis text-tinta-redup hover:text-tinta hover:bg-kertas transition"
                title="Periode Sebelumnya"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <span class="text-base font-bold text-tinta tracking-tight min-w-36 text-center tabular-nums">
                {{ $this->periodLabel }}
            </span>
            <button
                type="button"
                wire:click="nextPeriod"
                class="p-1.5 rounded-lg border border-garis text-tinta-redup hover:text-tinta hover:bg-kertas transition"
                title="Periode Berikutnya"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    <!-- Segmented Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-garis pb-1 overflow-x-auto">
        <button
            type="button"
            wire:click="setTab('summary')"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition whitespace-nowrap {{ $activeTab === 'summary' ? 'bg-batik text-white shadow-2xs' : 'text-tinta-redup hover:text-tinta hover:bg-kertas' }}"
        >
            Ringkasan
        </button>
        <button
            type="button"
            wire:click="setTab('category')"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition whitespace-nowrap {{ $activeTab === 'category' ? 'bg-batik text-white shadow-2xs' : 'text-tinta-redup hover:text-tinta hover:bg-kertas' }}"
        >
            Kategori
        </button>
        <button
            type="button"
            wire:click="setTab('source')"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition whitespace-nowrap {{ $activeTab === 'source' ? 'bg-batik text-white shadow-2xs' : 'text-tinta-redup hover:text-tinta hover:bg-kertas' }}"
        >
            Sumber Saldo
        </button>
        <button
            type="button"
            wire:click="setTab('tax')"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition whitespace-nowrap {{ $activeTab === 'tax' ? 'bg-batik text-white shadow-2xs' : 'text-tinta-redup hover:text-tinta hover:bg-kertas' }}"
        >
            Pajak & Biaya
        </button>
    </div>

    <!-- TAB 1: RINGKASAN -->
    @if ($activeTab === 'summary')
        <div class="space-y-6">
            <!-- Tiga Angka Berdampingan Dipisah Garis Vertikal (DESIGN.md 5.11) -->
            <div class="p-6 rounded-2xl bg-permukaan border border-garis shadow-xs">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 divide-y md:divide-y-0 md:divide-x divide-garis">
                    <div class="md:px-4 first:pl-0">
                        <p class="text-xs uppercase tracking-wider text-tinta-redup font-semibold">Total Pemasukan</p>
                        <p class="text-3xl font-extrabold text-masuk tabular-nums tracking-tight mt-1.5">
                            +{{ rupiah($summary['total_income']) }}
                        </p>
                    </div>
                    <div class="pt-4 md:pt-0 md:px-6">
                        <p class="text-xs uppercase tracking-wider text-tinta-redup font-semibold">Total Pengeluaran & Biaya</p>
                        <p class="text-3xl font-extrabold text-keluar tabular-nums tracking-tight mt-1.5">
                            -{{ rupiah($summary['total_expense'] + $summary['total_transfer_fee']) }}
                        </p>
                        @if ($summary['total_transfer_fee'] > 0)
                            <p class="text-xs text-tinta-redup mt-1">
                                Termasuk biaya transfer: {{ rupiah($summary['total_transfer_fee']) }}
                            </p>
                        @endif
                    </div>
                    <div class="pt-4 md:pt-0 md:px-6">
                        <p class="text-xs uppercase tracking-wider text-tinta-redup font-semibold">Arus Kas Bersih (Selisih)</p>
                        <p class="text-3xl font-extrabold tabular-nums tracking-tight mt-1.5 {{ $summary['net_cashflow'] >= 0 ? 'text-masuk' : 'text-keluar' }}">
                            {{ $summary['net_cashflow'] >= 0 ? '+' : '' }}{{ rupiah($summary['net_cashflow']) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tabel Rincian Breakdown -->
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-tinta">
                    {{ $periodType === 'yearly' ? 'Rincian Bulanan' : 'Rincian Hari dengan Transaksi' }}
                </h3>

                @if (empty($summary['breakdown']))
                    <div class="p-8 rounded-2xl bg-permukaan border border-garis text-center text-xs text-tinta-redup">
                        Tidak ada transaksi pada periode ini.
                    </div>
                @else
                    <div class="overflow-hidden rounded-2xl border border-garis bg-permukaan shadow-xs">
                        <table class="min-w-full divide-y divide-garis text-left text-sm">
                            <thead class="bg-kertas text-xs font-semibold text-tinta-redup">
                                <tr>
                                    <th class="py-3 px-4">Waktu</th>
                                    <th class="py-3 px-4 text-right">Pemasukan</th>
                                    <th class="py-3 px-4 text-right">Pengeluaran</th>
                                    <th class="py-3 px-4 text-right">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-garis">
                                @foreach ($summary['breakdown'] as $row)
                                    <tr class="hover:bg-kertas/50 transition">
                                        <td class="py-3 px-4 text-xs font-medium text-tinta whitespace-nowrap">
                                            {{ $row['label'] }}
                                        </td>
                                        <td class="py-3 px-4 text-right tabular-nums text-masuk text-xs font-semibold whitespace-nowrap">
                                            +{{ rupiah($row['income']) }}
                                        </td>
                                        <td class="py-3 px-4 text-right tabular-nums text-keluar text-xs font-semibold whitespace-nowrap">
                                            -{{ rupiah($row['expense'] + $row['transfer_fee']) }}
                                        </td>
                                        <td class="py-3 px-4 text-right tabular-nums font-bold text-sm whitespace-nowrap {{ $row['net'] >= 0 ? 'text-masuk' : 'text-keluar' }}">
                                            {{ $row['net'] >= 0 ? '+' : '' }}{{ rupiah($row['net']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- TAB 2: KATEGORI -->
    @if ($activeTab === 'category')
        <div class="space-y-4">
            @if ($categories->isEmpty())
                <div class="p-8 rounded-2xl bg-permukaan border border-garis text-center text-xs text-tinta-redup">
                    Belum ada pengeluaran per kategori pada periode ini.
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-garis bg-permukaan shadow-xs">
                    <table class="min-w-full divide-y divide-garis text-left text-sm">
                        <thead class="bg-kertas text-xs font-semibold text-tinta-redup">
                            <tr>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4">Frekuensi</th>
                                <th class="py-3 px-4">Porsi</th>
                                <th class="py-3 px-4 text-right">Total Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-garis">
                            @foreach ($categories as $cat)
                                <tr class="hover:bg-kertas/50 transition">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $cat['color'] }}"></span>
                                            <span class="font-medium text-tinta">{{ $cat['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-tinta-redup whitespace-nowrap">
                                        {{ $cat['count'] }} kali
                                    </td>
                                    <td class="py-3 px-4 min-w-44">
                                        <div class="flex items-center gap-2">
                                            <div class="w-full bg-garis rounded-full h-2 overflow-hidden">
                                                <div class="h-2 rounded-full" style="width: {{ $cat['percent'] }}%; background-color: {{ $cat['color'] }}"></div>
                                            </div>
                                            <span class="text-xs font-bold text-tinta tabular-nums shrink-0">{{ $cat['percent'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right tabular-nums font-bold text-tinta whitespace-nowrap">
                                        {{ rupiah($cat['amount']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    <!-- TAB 3: SUMBER SALDO -->
    @if ($activeTab === 'source')
        <div class="space-y-4">
            <div class="overflow-hidden rounded-2xl border border-garis bg-permukaan shadow-xs">
                <table class="min-w-full divide-y divide-garis text-left text-sm">
                    <thead class="bg-kertas text-xs font-semibold text-tinta-redup">
                        <tr>
                            <th class="py-3 px-4">Sumber Saldo</th>
                            <th class="py-3 px-4 text-right">Pemasukan</th>
                            <th class="py-3 px-4 text-right">Pengeluaran</th>
                            <th class="py-3 px-4 text-right">Transfer Masuk</th>
                            <th class="py-3 px-4 text-right">Transfer Keluar</th>
                            <th class="py-3 px-4 text-right">Selisih Periode</th>
                            <th class="py-3 px-4 text-right">Saldo Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis">
                        @foreach ($sources as $src)
                            <tr class="hover:bg-kertas/50 transition">
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <x-source-dot :color="$src['color']" />
                                        <div>
                                            <p class="font-medium text-tinta">{{ $src['name'] }}</p>
                                            <p class="text-2xs text-tinta-redup">{{ $src['type'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums text-masuk text-xs whitespace-nowrap">
                                    +{{ rupiah($src['income']) }}
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums text-keluar text-xs whitespace-nowrap">
                                    -{{ rupiah($src['expense']) }}
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums text-masuk text-xs whitespace-nowrap">
                                    +{{ rupiah($src['transfers_in']) }}
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums text-keluar text-xs whitespace-nowrap">
                                    -{{ rupiah($src['transfers_out'] + $src['transfer_fee']) }}
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums font-bold text-sm whitespace-nowrap {{ $src['net_change'] >= 0 ? 'text-masuk' : 'text-keluar' }}">
                                    {{ $src['net_change'] >= 0 ? '+' : '' }}{{ rupiah($src['net_change']) }}
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums font-bold text-tinta text-sm whitespace-nowrap">
                                    {{ rupiah($src['current_balance']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TAB 4: PAJAK & BIAYA -->
    @if ($activeTab === 'tax')
        <div class="space-y-6">
            <div class="p-6 rounded-2xl bg-permukaan border border-garis shadow-xs">
                <p class="text-xs uppercase tracking-wider text-tinta-redup font-semibold">Total Pajak & Biaya Admin</p>
                <p class="text-3xl font-extrabold text-keluar tabular-nums tracking-tight mt-1.5">
                    {{ rupiah($tax['total_tax']) }}
                </p>
                <p class="text-xs text-tinta-redup mt-1">
                    Biaya admin transfer: {{ rupiah($tax['total_transfer_tax']) }}
                </p>
            </div>

            <div class="space-y-3">
                <h3 class="text-sm font-bold text-tinta">Daftar Rincian Pajak & Biaya</h3>

                @if (empty($tax['items']))
                    <div class="p-8 rounded-2xl bg-permukaan border border-garis text-center text-xs text-tinta-redup">
                        Tidak ada biaya admin transfer atau pajak pada periode ini.
                    </div>
                @else
                    <div class="overflow-hidden rounded-2xl border border-garis bg-permukaan shadow-xs">
                        <table class="min-w-full divide-y divide-garis text-left text-sm">
                            <thead class="bg-kertas text-xs font-semibold text-tinta-redup">
                                <tr>
                                    <th class="py-3 px-4">Tanggal</th>
                                    <th class="py-3 px-4">Jenis</th>
                                    <th class="py-3 px-4">Keterangan</th>
                                    <th class="py-3 px-4 text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-garis">
                                @foreach ($tax['items'] as $item)
                                    <tr class="hover:bg-kertas/50 transition">
                                        <td class="py-3 px-4 text-xs text-tinta-redup whitespace-nowrap">
                                            {{ $item['date'] }}
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <x-badge variant="warning">{{ $item['type'] }}</x-badge>
                                        </td>
                                        <td class="py-3 px-4 text-xs text-tinta font-medium">
                                            {{ $item['description'] }}
                                        </td>
                                        <td class="py-3 px-4 text-right tabular-nums font-bold text-keluar whitespace-nowrap">
                                            {{ rupiah($item['amount']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
