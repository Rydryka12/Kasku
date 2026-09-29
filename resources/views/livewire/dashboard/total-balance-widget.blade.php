<div class="bg-permukaan border border-garis rounded-2xl p-6 space-y-6">
    <!-- Header Widget: Saldo & Arus Kas Bulan Ini -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="space-y-3">
            <div>
                <p class="text-xs uppercase tracking-wider text-tinta-redup font-semibold">Total Kekayaan</p>
                <p class="text-3xl sm:text-4xl font-extrabold text-tinta tabular-nums tracking-tight mt-1">{{ rupiah($totalWealth) }}</p>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <div>
                    <span class="text-tinta-redup">Saldo Tersedia:</span>
                    <span class="font-bold text-tinta tabular-nums">{{ rupiah($availableBalance) }}</span>
                </div>
                <span class="text-garis">|</span>
                <div>
                    <span class="text-tinta-redup">Total Tabungan:</span>
                    <span class="font-bold text-batik tabular-nums">{{ rupiah($totalSavings) }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 sm:gap-6 bg-kertas/60 p-3.5 rounded-xl border border-garis/50 overflow-x-auto">
            <div>
                <p class="text-xs text-tinta-redup">Pemasukan Bulan Ini</p>
                <x-money :value="$monthlyIncome" class="text-sm font-bold text-masuk" />
            </div>
            <div class="w-px h-8 bg-garis"></div>
            <div>
                <p class="text-xs text-tinta-redup">Pengeluaran Bulan Ini</p>
                <x-money :value="$monthlyExpense" class="text-sm font-bold text-keluar" />
            </div>
            <div class="w-px h-8 bg-garis"></div>
            <div>
                <p class="text-xs text-tinta-redup">Arus Kas Bersih</p>
                <x-money :value="$netCashflow" :signed="true" class="text-sm font-bold {{ $netCashflow >= 0 ? 'text-masuk' : 'text-keluar' }}" />
            </div>
        </div>
    </div>

    <!-- Money-Bar Proporsi Sumber Saldo -->
    <div>
        <p class="text-xs text-tinta-redup font-medium mb-2">Distribusi Akun & Sumber Saldo</p>
        <x-money-bar :sources="$sourceData" :total="$totalBalance" />
    </div>
</div>
