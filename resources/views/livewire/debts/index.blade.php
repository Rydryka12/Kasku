<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-tinta font-heading">Hutang &amp; Piutang</h1>
            <p class="text-sm text-tinta-redup">Catat dan pantau pelunasan pinjaman serta tagihan pihak lain.</p>
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
                <span>+ Catat {{ $activeTab === 'debt' ? 'Hutang' : 'Piutang' }}</span>
            </button>
        </div>
    </div>

    <!-- Ringkasan Total (DESIGN 5.9) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-permukaan border border-garis rounded-xl p-4 sm:p-5 shadow-xs">
            <p class="text-xs font-medium text-tinta-redup uppercase tracking-wider">Hutang Belum Lunas</p>
            <p class="text-2xl font-bold text-keluar tabular-nums mt-1 font-heading">
                {{ rupiah($totalUnpaidDebt) }}
            </p>
            <p class="text-xs text-tinta-redup mt-1">Uang yang wajib kita bayarkan ke pihak lain.</p>
        </div>

        <div class="bg-permukaan border border-garis rounded-xl p-4 sm:p-5 shadow-xs">
            <p class="text-xs font-medium text-tinta-redup uppercase tracking-wider">Piutang Belum Lunas</p>
            <p class="text-2xl font-bold text-masuk tabular-nums mt-1 font-heading">
                {{ rupiah($totalUnpaidReceivable) }}
            </p>
            <p class="text-xs text-tinta-redup mt-1">Uang kita yang dipinjam oleh pihak lain.</p>
        </div>
    </div>

    <!-- Tabs Segmented -->
    <div class="flex items-center gap-2 border-b border-garis pb-2">
        <button
            type="button"
            wire:click="$set('activeTab', 'debt')"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition {{ $activeTab === 'debt' ? 'bg-keluar text-white shadow-xs' : 'text-tinta-redup hover:text-tinta' }}"
        >
            Hutang (Kewajiban)
        </button>
        <button
            type="button"
            wire:click="$set('activeTab', 'receivable')"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition {{ $activeTab === 'receivable' ? 'bg-masuk text-white shadow-xs' : 'text-tinta-redup hover:text-tinta' }}"
        >
            Piutang (Hak)
        </button>
    </div>

    <!-- List Items -->
    @if($debts->isEmpty())
        <div class="bg-permukaan border border-garis rounded-xl p-8 text-center shadow-xs">
            <div class="w-12 h-12 rounded-full bg-garis/50 text-tinta-redup flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-tinta">Belum ada catatan {{ $activeTab === 'debt' ? 'hutang' : 'piutang' }}</p>
            <p class="text-xs text-tinta-redup mt-1">Klik tombol "+ Catat" di atas untuk menambahkan.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($debts as $item)
                @php
                    $isPaid = $item->status->value === 'paid';
                    $progress = $item->progress_percent;
                    $remaining = $item->remaining_amount;

                    // Due date alert (FR-DBT-04)
                    $dueDateText = null;
                    $dueAlertClass = 'text-tinta-redup';
                    if ($item->due_date && ! $isPaid) {
                        $due = \Carbon\Carbon::parse($item->due_date)->startOfDay();
                        $today = now()->startOfDay();
                        $diffDays = (int) $today->diffInDays($due, false);

                        if ($diffDays < 0) {
                            $dueDateText = 'Terlewat ' . abs($diffDays) . ' hari';
                            $dueAlertClass = 'text-keluar font-semibold';
                        } elseif ($diffDays === 0) {
                            $dueDateText = 'Jatuh tempo hari ini';
                            $dueAlertClass = 'text-keluar font-semibold';
                        } elseif ($diffDays === 1) {
                            $dueDateText = 'Jatuh tempo besok';
                            $dueAlertClass = 'text-amber-600 dark:text-amber-400 font-semibold';
                        } elseif ($diffDays <= 3) {
                            $dueDateText = 'Jatuh tempo ' . $diffDays . ' hari lagi';
                            $dueAlertClass = 'text-amber-600 dark:text-amber-400 font-semibold';
                        } else {
                            $dueDateText = 'Jatuh tempo ' . tanggal($item->due_date);
                        }
                    }
                @endphp

                <div class="bg-permukaan border border-garis rounded-xl p-4 sm:p-5 shadow-xs space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base font-bold text-tinta font-heading">{{ $item->person }}</h3>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-medium {{ $isPaid ? 'bg-masuk/10 text-masuk' : ($item->paid_amount > 0 ? 'bg-amber-500/10 text-amber-700 dark:text-amber-300' : 'bg-garis text-tinta-redup') }}">
                                {{ $item->status->label() }}
                            </span>
                            @if($dueDateText)
                                <span class="text-xs px-2.5 py-0.5 rounded-full {{ $dueAlertClass }} bg-kertas border border-garis/60">
                                    {{ $dueDateText }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if(! $isPaid)
                                <button
                                    type="button"
                                    wire:click="openPaymentModal({{ $item->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-batik text-white text-xs font-medium hover:bg-batik-gelap transition shadow-xs"
                                >
                                    <span>{{ $item->type->value === 'debt' ? 'Bayar Cicilan' : 'Terima Cicilan' }}</span>
                                </button>
                            @endif

                            @if($item->payments->count() === 0)
                                <button
                                    type="button"
                                    wire:click="deleteDebt({{ $item->id }})"
                                    wire:confirm="Yakin ingin menghapus catatan ini?"
                                    class="p-1.5 rounded-lg text-tinta-redup hover:text-keluar hover:bg-keluar/10 transition text-xs"
                                    title="Hapus"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Progress Bar & Amount Stats -->
                    <div>
                        <div class="flex items-center justify-between text-xs text-tinta-redup mb-1.5">
                            <span>
                                <strong>{{ rupiah($item->paid_amount) }}</strong> dari {{ rupiah($item->amount) }} dibayar ({{ $progress }}%)
                            </span>
                            <span class="font-semibold text-tinta">
                                Sisa: {{ rupiah($remaining) }}
                            </span>
                        </div>
                        <div class="w-full bg-garis rounded-full h-2.5 overflow-hidden">
                            <div
                                class="h-2.5 rounded-full transition-all duration-300 {{ $isPaid ? 'bg-masuk' : 'bg-batik' }}"
                                style="width: {{ $progress }}%"
                            ></div>
                        </div>
                    </div>

                    @if($item->note)
                        <p class="text-xs text-tinta-redup italic">"{{ $item->note }}"</p>
                    @endif

                    <!-- History Payments toggle -->
                    @if($item->payments->count() > 0)
                        <div class="pt-2 border-t border-garis">
                            <button
                                type="button"
                                wire:click="togglePaymentsHistory({{ $item->id }})"
                                class="text-xs text-batik hover:underline font-medium flex items-center gap-1"
                            >
                                <span>{{ $viewPaymentsDebtId === $item->id ? 'Sembunyikan Riwayat Pembayaran' : 'Lihat Riwayat Pembayaran (' . $item->payments->count() . ')' }}</span>
                                <svg class="w-3.5 h-3.5 transition-transform {{ $viewPaymentsDebtId === $item->id ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            @if($viewPaymentsDebtId === $item->id)
                                <div class="mt-2 space-y-1.5 pl-2 border-l-2 border-garis">
                                    @foreach($item->payments as $p)
                                        <div class="text-xs flex items-center justify-between text-tinta-redup py-1">
                                            <span>{{ tanggal($p->date) }} · via <strong>{{ $p->accountSource->name }}</strong> @if($p->note) ({{ $p->note }}) @endif</span>
                                            <span class="font-semibold text-tinta">{{ rupiah($p->amount) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal Catat Hutang/Piutang -->
    @if($showDebtModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-permukaan border border-garis rounded-2xl w-full max-w-md p-6 space-y-4 shadow-xl">
                <div class="flex items-center justify-between border-b border-garis pb-3">
                    <h3 class="text-lg font-bold text-tinta font-heading">
                        Catat {{ $debtType === 'debt' ? 'Hutang' : 'Piutang' }} Baru
                    </h3>
                    <button type="button" wire:click="$set('showDebtModal', false)" class="text-tinta-redup hover:text-tinta text-xl leading-none">&times;</button>
                </div>

                <form wire:submit="saveDebt" class="space-y-4">
                    <!-- Segmented Tipe -->
                    <div class="grid grid-cols-2 gap-1 p-1 bg-kertas rounded-xl border border-garis">
                        <button
                            type="button"
                            wire:click="$set('debtType', 'debt')"
                            class="py-1.5 text-xs font-semibold rounded-lg transition {{ $debtType === 'debt' ? 'bg-keluar text-white shadow-xs' : 'text-tinta-redup hover:text-tinta' }}"
                        >
                            Hutang (Kita berhutang)
                        </button>
                        <button
                            type="button"
                            wire:click="$set('debtType', 'receivable')"
                            class="py-1.5 text-xs font-semibold rounded-lg transition {{ $debtType === 'receivable' ? 'bg-masuk text-white shadow-xs' : 'text-tinta-redup hover:text-tinta' }}"
                        >
                            Piutang (Orang berhutang)
                        </button>
                    </div>

                    <!-- Pihak / Nama Orang -->
                    <div>
                        <label class="block text-sm font-medium text-tinta mb-1">
                            {{ $debtType === 'debt' ? 'Pemberi Pinjaman' : 'Peminjam (Nama Pihak)' }}
                        </label>
                        <input
                            type="text"
                            wire:model="person"
                            placeholder="Contoh: Andi, Budi, Bank Mandiri"
                            class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                        />
                        @error('person')
                            <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nominal Pinjaman -->
                    <div>
                        <label class="block text-sm font-medium text-tinta mb-1">Nominal Total Pinjaman</label>
                        <x-money-input wire:model="amount" placeholder="0" />
                        @error('amount')
                            <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-tinta-redup">
                            ℹ Mencatat hutang/piutang baru tidak akan mengubah saldo (K-11). Saldo hanya berubah saat ada cicilan/pembayaran.
                        </p>
                    </div>

                    <!-- Jatuh Tempo -->
                    <div>
                        <label class="block text-sm font-medium text-tinta mb-1">Tanggal Jatuh Tempo (Opsional)</label>
                        <input
                            type="date"
                            wire:model="due_date"
                            class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                        />
                        @error('due_date')
                            <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="block text-sm font-medium text-tinta mb-1">Catatan (Opsional)</label>
                        <textarea
                            wire:model="note"
                            rows="2"
                            placeholder="Catatan tambahan..."
                            class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-garis">
                        <button
                            type="button"
                            wire:click="$set('showDebtModal', false)"
                            class="px-4 py-2 text-sm font-medium text-tinta-redup hover:text-tinta rounded-lg border border-garis hover:bg-kertas transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2 text-sm font-medium text-white bg-batik rounded-lg hover:bg-batik-gelap transition shadow-xs"
                        >
                            Simpan Catatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal Bayar Cicilan (DESIGN 5.9) -->
    @if($showPaymentModal && $activePaymentDebt)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-permukaan border border-garis rounded-2xl w-full max-w-md p-6 space-y-4 shadow-xl">
                <div class="flex items-center justify-between border-b border-garis pb-3">
                    <h3 class="text-lg font-bold text-tinta font-heading">
                        {{ $activePaymentDebt->type->value === 'debt' ? 'Bayar Cicilan Hutang' : 'Terima Cicilan Piutang' }}
                    </h3>
                    <button type="button" wire:click="$set('showPaymentModal', false)" class="text-tinta-redup hover:text-tinta text-xl leading-none">&times;</button>
                </div>

                <div class="p-3 rounded-lg bg-kertas border border-garis text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-tinta-redup">Pihak:</span>
                        <span class="font-semibold text-tinta">{{ $activePaymentDebt->person }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-tinta-redup">Sisa Tagihan:</span>
                        <span class="font-bold text-tinta">{{ rupiah($activePaymentDebt->remaining_amount) }}</span>
                    </div>
                </div>

                <form wire:submit="savePayment" class="space-y-4">
                    <!-- Sumber Saldo -->
                    <div>
                        <label class="block text-sm font-medium text-tinta mb-1">
                            {{ $activePaymentDebt->type->value === 'debt' ? 'Potong dari Sumber Saldo' : 'Masuk ke Sumber Saldo' }}
                        </label>
                        <select
                            wire:model.live="paymentSourceId"
                            class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                        >
                            <option value="">Pilih Sumber Saldo</option>
                            @foreach($sources as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ rupiah($s->current_balance) }})</option>
                            @endforeach
                        </select>
                        @error('paymentSourceId')
                            <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nominal Pembayaran -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-medium text-tinta">Nominal Pembayaran</label>
                            <button
                                type="button"
                                wire:click="$set('paymentAmount', {{ $activePaymentDebt->remaining_amount }})"
                                class="text-xs text-batik hover:underline"
                            >
                                Bayar Lunas ({{ rupiah($activePaymentDebt->remaining_amount) }})
                            </button>
                        </div>
                        <x-money-input wire:model.live="paymentAmount" placeholder="0" />
                        @error('paymentAmount')
                            <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pratinjau Saldo -->
                    @if($activePaymentSource && $paymentAmount > 0)
                        @php
                            $isDebt = $activePaymentDebt->type->value === 'debt';
                            $previewBalance = $isDebt
                                ? $activePaymentSource->current_balance - $paymentAmount
                                : $activePaymentSource->current_balance + $paymentAmount;
                            $isInsufficient = $isDebt && ($previewBalance < 0);
                        @endphp
                        <div class="p-3 rounded-lg border {{ $isInsufficient ? 'border-keluar/30 bg-keluar/5 text-keluar' : 'border-garis bg-kertas/50 text-tinta' }} text-xs">
                            <p class="font-medium">
                                Pratinjau Saldo {{ $activePaymentSource->name }}:
                                {{ rupiah($activePaymentSource->current_balance) }} &rarr;
                                <span class="font-bold {{ $isInsufficient ? 'text-keluar' : 'text-tinta' }}">{{ rupiah($previewBalance) }}</span>
                            </p>
                            @if($isInsufficient)
                                <p class="text-[11px] text-keluar font-semibold mt-1">
                                    ⚠ Saldo tidak mencukupi untuk melakukan pembayaran ini (K-01).
                                </p>
                            @endif
                        </div>
                    @endif

                    <!-- Tanggal -->
                    <div>
                        <label class="block text-sm font-medium text-tinta mb-1">Tanggal Pembayaran</label>
                        <input
                            type="date"
                            wire:model="paymentDate"
                            class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                        />
                        @error('paymentDate')
                            <p class="mt-1 text-xs text-keluar">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="block text-sm font-medium text-tinta mb-1">Catatan (Opsional)</label>
                        <input
                            type="text"
                            wire:model="paymentNote"
                            placeholder="Contoh: Cicilan ke-1"
                            class="w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-garis">
                        <button
                            type="button"
                            wire:click="$set('showPaymentModal', false)"
                            class="px-4 py-2 text-sm font-medium text-tinta-redup hover:text-tinta rounded-lg border border-garis hover:bg-kertas transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2 text-sm font-medium text-white bg-batik rounded-lg hover:bg-batik-gelap transition shadow-xs"
                        >
                            Simpan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
