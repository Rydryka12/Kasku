<div class="bg-permukaan border border-garis rounded-2xl p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-base font-bold text-tinta">Transaksi Terakhir</h3>
        <a href="{{ route('transactions.index') }}" wire:navigate class="text-xs text-batik font-semibold hover:underline">
            Lihat Semua →
        </a>
    </div>

    <div class="divide-y divide-garis">
        @forelse($transactions as $tx)
            <div class="py-3 flex items-center justify-between hover:bg-kertas/30 transition">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $tx->category->color ?? '#2B3A8C' }};"></span>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-tinta">{{ $tx->category->name ?? 'Kategori' }}</span>
                            <x-source-dot :color="$tx->accountSource->color ?? '#2B3A8C'" :name="$tx->accountSource->name ?? ''" />
                        </div>
                        <p class="text-xs text-tinta-redup">
                            {{ $tx->date->translatedFormat('d M Y') }}
                            @if($tx->description)
                                • {{ $tx->description }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="text-end">
                    <x-money
                        :value="$tx->type->value === 'expense' ? -$tx->amount : $tx->amount"
                        :signed="true"
                        class="text-sm {{ $tx->type->value === 'expense' ? 'text-keluar' : 'text-masuk' }}"
                    />
                </div>
            </div>
        @empty
            <div class="py-8">
                <x-empty
                    title="Belum ada transaksi"
                    description="Mulai catat transaksi pengeluaran atau pemasukan pertama Anda hari ini."
                    actionText="Catat Transaksi"
                    actionClick="$dispatch('open-transaction-modal')"
                />
            </div>
        @endforelse
    </div>
</div>
