<div>
    <x-slot name="header">
        Ringkasan Sistem Administrator
    </x-slot>

    <div class="space-y-6">
        <!-- 3 Angka Ringkasan (DESIGN 5.13, FR-ADM-04, FR-ADM-05) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-6 bg-permukaan border border-garis rounded-2xl shadow-xs">
                <p class="text-xs uppercase tracking-wider text-tinta-redup font-semibold">Total Pengguna</p>
                <p class="text-3xl font-extrabold text-tinta mt-2 tabular-nums font-heading">{{ $totalUsers }}</p>
                <p class="text-xs text-tinta-redup mt-1">Pengguna terdaftar di aplikasi</p>
            </div>

            <div class="p-6 bg-permukaan border border-garis rounded-2xl shadow-xs">
                <p class="text-xs uppercase tracking-wider text-tinta-redup font-semibold">Total Transaksi (Sistem)</p>
                <p class="text-3xl font-extrabold text-batik mt-2 tabular-nums font-heading">{{ $totalTransactions }}</p>
                <p class="text-xs text-tinta-redup mt-1">Akumulasi catatan transaksi</p>
            </div>

            <div class="p-6 bg-permukaan border border-garis rounded-2xl shadow-xs">
                <p class="text-xs uppercase tracking-wider text-tinta-redup font-semibold">Pengguna Baru Bulan Ini</p>
                <p class="text-3xl font-extrabold text-masuk mt-2 tabular-nums font-heading">{{ $newUsersThisMonth }}</p>
                <p class="text-xs text-tinta-redup mt-1">{{ now()->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <!-- Grafik Pertumbuhan Pengguna Per Bulan (DESIGN 5.13) -->
        <div class="bg-permukaan border border-garis rounded-2xl p-6 shadow-xs space-y-4">
            <div>
                <h3 class="text-base font-bold text-tinta font-heading">Pertumbuhan Pengguna Baru (6 Bulan Terakhir)</h3>
                <p class="text-xs text-tinta-redup">Statistik penambahan akun pengguna per bulan</p>
            </div>

            <div class="grid grid-cols-6 gap-2 sm:gap-4 items-end pt-6 pb-2 h-48 border-b border-garis">
                @foreach($userGrowth as $growth)
                    @php
                        $heightPercent = (int) round(($growth['count'] / $maxGrowth) * 100);
                        $heightPercent = max(10, $heightPercent);
                    @endphp
                    <div class="flex flex-col items-center gap-2 h-full justify-end group">
                        <span class="text-xs font-bold text-tinta tabular-nums opacity-0 group-hover:opacity-100 transition">
                            {{ $growth['count'] }}
                        </span>
                        <div
                            class="w-full max-w-[48px] bg-batik/80 hover:bg-batik rounded-t-lg transition-all duration-300"
                            style="height: {{ $heightPercent }}%;"
                            title="{{ $growth['label'] }}: {{ $growth['count'] }} user"
                        ></div>
                        <span class="text-[11px] text-tinta-redup text-center truncate w-full">
                            {{ $growth['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>

            <p class="text-xs text-tinta-redup italic">
                ℹ Kebijakan Privasi (K-14): Administrator tidak memiliki hak akses maupun query terhadap nominal keuangan, saldo, atau detail transaksi pengguna.
            </p>
        </div>
    </div>
</div>
