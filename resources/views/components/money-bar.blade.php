@props([
    'sources' => [], // array of ['id' => int, 'name' => string, 'balance' => int, 'color' => string, 'percent' => float]
    'savings' => null, // ['name' => 'Tabungan', 'amount' => int, 'percent' => float]
    'total' => 0,
])

<div class="space-y-3">
    <!-- Bar Horizontal -->
    <div
        class="h-7 w-full rounded-xl bg-garis/50 overflow-hidden flex p-0.5 border border-garis gap-0.5"
        role="progressbar"
        aria-label="Proporsi saldo dan tabungan"
    >
        @if($total > 0)
            @foreach($sources as $src)
                @if($src['percent'] > 0)
                    <a
                        href="{{ route('sources.show', $src['id']) }}"
                        wire:navigate
                        title="{{ $src['name'] }}: {{ rupiah($src['balance']) }} ({{ number_format($src['percent'], 1) }}%)"
                        style="width: {{ max(2, $src['percent']) }}%; background-color: {{ $src['color'] }};"
                        class="h-full rounded-lg transition-all duration-300 hover:opacity-85 focus:outline-none focus:ring-2 focus:ring-batik"
                    ></a>
                @endif
            @endforeach

            @if($savings && ($savings['percent'] ?? 0) > 0)
                <div
                    title="{{ $savings['name'] ?? 'Tabungan' }}: {{ rupiah($savings['amount']) }} ({{ number_format($savings['percent'], 1) }}%)"
                    style="width: {{ max(2, $savings['percent']) }}%; background-color: var(--color-kunyit);"
                    class="h-full rounded-lg transition-all duration-300"
                ></div>
            @endif
        @else
            <div class="w-full h-full rounded-lg bg-garis flex items-center justify-center text-xs text-tinta-redup">
                Belum ada saldo
            </div>
        @endif
    </div>

    <!-- Legenda Bar (Daftar Sumber) -->
    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 pt-1 text-sm">
        @foreach($sources as $src)
            <div class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $src['color'] }};"></span>
                <span class="text-tinta-redup">{{ $src['name'] }}</span>
                <span class="font-semibold text-tinta tabular-nums">{{ rupiah($src['balance']) }}</span>
            </div>
        @endforeach

        @if($savings && ($savings['amount'] ?? 0) > 0)
            <div class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 bg-kunyit"></span>
                <span class="text-tinta-redup">{{ $savings['name'] ?? 'Tabungan' }}</span>
                <span class="font-semibold text-tinta tabular-nums">{{ rupiah($savings['amount']) }}</span>
            </div>
        @endif
    </div>
</div>
