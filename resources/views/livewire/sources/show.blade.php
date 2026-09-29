<div>
    <x-slot name="header">
        Buku Besar: {{ $source->name }}
    </x-slot>

    <div class="space-y-6">
        <!-- Header Info Sumber Saldo -->
        <div class="bg-permukaan border border-garis rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-4 h-4 rounded-full" style="background-color: {{ $source->color }};"></span>
                <div>
                    <h2 class="text-xl font-bold text-tinta">{{ $source->name }}</h2>
                    <p class="text-xs text-tinta-redup">Tipe: {{ $source->type->label() }} • Saldo Awal: {{ rupiah($source->initial_balance) }}</p>
                </div>
            </div>

            <div class="text-start sm:text-end">
                <p class="text-xs text-tinta-redup">Saldo Saat Ini</p>
                <p class="text-3xl font-extrabold text-tinta tabular-nums tracking-tight">{{ rupiah($source->current_balance) }}</p>
            </div>
        </div>

        <!-- Tabel Mutasi (Baris Buku Besar) -->
        <div class="bg-permukaan border border-garis rounded-2xl overflow-hidden divide-y divide-garis">
            <div class="p-4 bg-kertas/50 flex items-center justify-between text-xs font-semibold text-tinta-redup uppercase tracking-wider">
                <span>Riwayat Mutasi & Transaksi</span>
                <span>Jumlah</span>
            </div>

            @forelse($mutations as $item)
                <div class="p-4 flex items-center justify-between hover:bg-kertas/30 transition">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-tinta">{{ $item->title }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $item->type === 'income' ? 'bg-masuk/10 text-masuk' : 'bg-keluar/10 text-keluar' }} font-medium">
                                {{ $item->badge }}
                            </span>
                        </div>
                        <p class="text-xs text-tinta-redup">
                            {{ \Carbon\Carbon::parse($item->date)->translatedFormat('d M Y') }}
                            @if($item->subtitle)
                                • {{ $item->subtitle }}
                            @endif
                        </p>
                    </div>

                    <div class="text-end">
                        <x-money :value="$item->amount" :signed="true" class="text-base {{ $item->type === 'income' ? 'text-masuk' : 'text-keluar' }}" />
                    </div>
                </div>
            @empty
                <div class="p-8">
                    <x-empty
                        title="Belum ada mutasi"
                        description="Belum ada mutasi pemasukan, pengeluaran, atau transfer pada sumber saldo ini."
                    />
                </div>
            @endforelse
        </div>

        <div>
            {{ $mutations->links() }}
        </div>
    </div>
</div>
