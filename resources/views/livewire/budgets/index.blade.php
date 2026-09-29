<div class="space-y-6">
    <!-- Header Page & Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-tinta tracking-tight">Anggaran (Budget)</h1>
            <p class="text-sm text-tinta-redup mt-0.5">Kontrol batas pengeluaran per kategori setiap bulan.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                wire:click="copyPrevious"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl border border-garis bg-permukaan text-tinta hover:bg-kertas transition shadow-2xs"
            >
                <svg class="w-4 h-4 text-tinta-redup" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span>Salin dari bulan lalu</span>
            </button>
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-xl bg-batik text-white hover:opacity-90 transition shadow-2xs"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Budget</span>
            </button>
        </div>
    </div>

    <!-- Month Navigator & Top Summary -->
    <div class="p-4 rounded-2xl bg-permukaan border border-garis shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Month Navigator -->
        <div class="flex items-center gap-3">
            <button
                type="button"
                wire:click="prevMonth"
                class="p-1.5 rounded-lg border border-garis text-tinta-redup hover:text-tinta hover:bg-kertas transition"
                title="Bulan Sebelumnya"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <span class="text-base font-bold text-tinta tracking-tight min-w-36 text-center tabular-nums">
                {{ $this->monthLabel }}
            </span>
            <button
                type="button"
                wire:click="nextMonth"
                class="p-1.5 rounded-lg border border-garis text-tinta-redup hover:text-tinta hover:bg-kertas transition"
                title="Bulan Berikutnya"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <!-- Overall Summary -->
        @if ($totalLimit > 0)
            @php
                $overallPercent = round(($totalUsed / $totalLimit) * 100);
            @endphp
            <div class="flex items-center gap-4 text-xs">
                <div>
                    <span class="text-tinta-redup">Total Terpakai:</span>
                    <span class="font-bold text-tinta tabular-nums">{{ rupiah($totalUsed) }}</span>
                    <span class="text-tinta-redup">dari {{ rupiah($totalLimit) }}</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-2xs font-bold {{ $overallPercent >= 100 ? 'bg-keluar/10 text-keluar' : ($overallPercent >= 80 ? 'bg-kunyit/15 text-kunyit' : 'bg-masuk/10 text-masuk') }}">
                    {{ $overallPercent }}%
                </span>
            </div>
        @endif
    </div>

    <!-- Budget List (DESIGN 5.7) -->
    @if ($budgets->isEmpty())
        <x-empty
            title="Belum ada budget untuk bulan ini"
            description="Tentukan batasan pengeluaran per kategori agar keuangan tetap terkendali."
            actionText="+ Buat Budget Pertama"
            actionClick="openCreateModal"
        />
    @else
        <div class="space-y-3">
            @foreach ($budgets as $b)
                <div class="p-5 rounded-2xl bg-permukaan border border-garis shadow-xs space-y-3 hover:border-batik/30 transition">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <!-- Category name with dot -->
                        <div class="flex items-center gap-2.5">
                            <span class="w-3.5 h-3.5 rounded-full shrink-0" style="background-color: {{ $b->category->color ?? '#6B7280' }}"></span>
                            <span class="font-bold text-tinta text-base">{{ $b->category->name ?? 'Kategori' }}</span>
                        </div>

                        <!-- Amount & Badge Status -->
                        <div class="flex items-center gap-3">
                            <div class="text-sm font-semibold tabular-nums text-tinta">
                                {{ rupiah($b->used) }} <span class="text-tinta-redup font-normal">/ {{ rupiah($b->limit_amount) }}</span>
                            </div>

                            @if ($b->status === 'exceeded')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-keluar/15 text-keluar">
                                    Lewat {{ rupiah(abs($b->remaining)) }}
                                </span>
                            @elseif ($b->status === 'warning')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-kunyit/20 text-kunyit">
                                    {{ $b->percent }}% Waspada
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-masuk/15 text-masuk">
                                    {{ $b->percent }}% Aman
                                </span>
                            @endif

                            <!-- Action dropdown / buttons -->
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    wire:click="edit({{ $b->id }})"
                                    class="p-1.5 rounded-lg text-tinta-redup hover:text-tinta hover:bg-kertas transition"
                                    title="Ubah Limit"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button
                                    type="button"
                                    wire:click="delete({{ $b->id }})"
                                    wire:confirm="Hapus budget ini?"
                                    class="p-1.5 rounded-lg text-tinta-redup hover:text-keluar hover:bg-kertas transition"
                                    title="Hapus Budget"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar with token colors (DESIGN 5.7) -->
                    @php
                        $barColor = match ($b->status) {
                            'exceeded' => 'bg-keluar',
                            'warning' => 'bg-kunyit',
                            default => 'bg-masuk',
                        };
                    @endphp
                    <x-progress :value="$b->percent" :color="$barColor" />

                    <!-- Remaining text -->
                    <div class="flex items-center justify-between text-xs text-tinta-redup">
                        <span>
                            @if ($b->remaining >= 0)
                                Sisa alokasi: <span class="font-semibold text-tinta tabular-nums">{{ rupiah($b->remaining) }}</span>
                            @else
                                Melebihi budget sebesar: <span class="font-bold text-keluar tabular-nums">{{ rupiah(abs($b->remaining)) }}</span>
                            @endif
                        </span>
                        <span>Batas maksimal: {{ rupiah($b->limit_amount) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal Form Budget -->
    <x-modal wire:model="showModal" :title="$editingBudgetId ? 'Ubah Batas Budget' : 'Tambah Budget Baru'">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Kategori Pengeluaran</label>
                <select
                    wire:model="category_id"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                    {{ $editingBudgetId ? 'disabled' : '' }}
                    required
                >
                    <option value="">Pilih Kategori</option>
                    @foreach ($availableCategories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Batas Nominal (Limit) per Bulan</label>
                <x-money-input wire:model="limit_amount" placeholder="0" autofocus />
                @error('limit_amount') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
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
                    class="px-5 py-2 text-sm font-semibold rounded-lg bg-batik text-white hover:opacity-90 transition"
                >
                    Simpan Budget
                </button>
            </div>
        </form>
    </x-modal>
</div>
