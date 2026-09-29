<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-tinta tracking-tight">Tabungan</h1>
            <p class="text-sm text-tinta-redup mt-0.5">
                Total tabungan tersimpan: <span class="font-semibold text-tinta tabular-nums">{{ rupiah($totalSavings) }}</span>
            </p>
        </div>
        <div>
            <button
                type="button"
                wire:click="openCreateGoalModal"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-batik text-white text-sm font-semibold hover:opacity-90 transition shadow-xs"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Target baru</span>
            </button>
        </div>
    </div>

    <!-- Goals Grid / List -->
    @if ($goals->isEmpty())
        <x-empty
            title="Belum ada target tabungan"
            description="Mulai menabung untuk impian, dana darurat, atau rencana masa depanmu."
            actionText="Buat Target Tabungan"
            actionClick="openCreateGoalModal"
        />
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($goals as $goal)
                <div class="p-5 rounded-2xl bg-permukaan border border-garis shadow-xs flex flex-col justify-between hover:border-batik/30 transition">
                    <div>
                        <!-- Title & Status -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-kertas border border-garis flex items-center justify-center text-batik font-bold text-lg">
                                    🎯
                                </div>
                                <div>
                                    <a href="{{ route('savings.show', $goal) }}" wire:navigate class="font-bold text-tinta hover:text-batik text-base tracking-tight transition">
                                        {{ $goal->name }}
                                    </a>
                                    @if ($goal->deadline)
                                        <p class="text-xs text-tinta-redup">
                                            Tenggat: {{ $goal->deadline->translatedFormat('d M Y') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <x-badge :variant="$goal->status->value === 'achieved' ? 'success' : ($goal->status->value === 'cancelled' ? 'neutral' : 'info')">
                                {{ $goal->status->label() }}
                            </x-badge>
                        </div>

                        <!-- Amounts & Progress -->
                        <div class="mt-4">
                            <div class="flex items-baseline justify-between text-sm mb-1.5">
                                <span class="font-bold text-tinta tabular-nums text-base">
                                    {{ rupiah($goal->current_amount) }}
                                </span>
                                <span class="text-xs text-tinta-redup tabular-nums">
                                    dari {{ rupiah($goal->target_amount) }}
                                </span>
                            </div>

                            <x-progress
                                :value="$goal->progressPercentage()"
                                :color="$goal->status->value === 'achieved' ? 'bg-masuk' : 'bg-batik'"
                            />

                            <div class="flex items-center justify-between text-xs text-tinta-redup mt-2">
                                <span>{{ $goal->progressPercentage() }}% terkumpul</span>
                                <span>Sisa {{ rupiah($goal->remainingAmount()) }}</span>
                            </div>
                        </div>

                        <!-- Estimation -->
                        <div class="mt-3 py-1.5 px-2.5 rounded-lg bg-kertas border border-garis/50 text-xs text-tinta-redup flex items-center justify-between">
                            <span>Perkiraan tercapai:</span>
                            <span class="font-medium text-tinta">
                                {{ $goal->estimateAchievedMonth() ?? 'belum bisa diperkirakan' }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4 pt-3 border-t border-garis flex items-center justify-between gap-2">
                        <a
                            href="{{ route('savings.show', $goal) }}"
                            wire:navigate
                            class="text-xs font-semibold text-tinta-redup hover:text-tinta transition"
                        >
                            Lihat Rincian →
                        </a>

                        @if ($goal->status->value !== 'cancelled')
                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    wire:click="openEntryModal({{ $goal->id }}, 'withdraw')"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-garis text-tinta hover:bg-kertas transition"
                                >
                                    Tarik
                                </button>
                                <button
                                    type="button"
                                    wire:click="openEntryModal({{ $goal->id }}, 'deposit')"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-masuk text-white hover:opacity-90 transition shadow-2xs"
                                >
                                    Setor
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal Target Baru / Edit -->
    <x-modal wire:model="showGoalModal" :title="$editingGoalId ? 'Ubah Target Tabungan' : 'Target Tabungan Baru'">
        <form wire:submit="saveGoal" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Nama Target</label>
                <input
                    type="text"
                    wire:model="name"
                    placeholder="Contoh: Dana Darurat, Liburan Bali"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                    required
                />
                @error('name') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Nominal Target</label>
                <x-money-input wire:model="target_amount" placeholder="0" />
                @error('target_amount') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Tenggat Waktu (Opsional)</label>
                <input
                    type="date"
                    wire:model="deadline"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                />
                @error('deadline') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-garis">
                <button
                    type="button"
                    wire:click="$set('showGoalModal', false)"
                    class="px-4 py-2 text-sm font-medium rounded-lg border border-garis text-tinta hover:bg-kertas transition"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 text-sm font-semibold rounded-lg bg-batik text-white hover:opacity-90 transition"
                >
                    Simpan Target
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Setor / Tarik -->
    <x-modal
        wire:model="showEntryModal"
        :title="$entryType === 'deposit' ? 'Setor ke Tabungan' : 'Tarik dari Tabungan'"
    >
        <form wire:submit="submitEntry" class="space-y-4">
            @if ($selectedGoal)
                <div class="p-3 rounded-xl bg-kertas border border-garis text-sm">
                    <p class="text-xs text-tinta-redup">Target:</p>
                    <p class="font-bold text-tinta">{{ $selectedGoal->name }}</p>
                    <p class="text-xs text-tinta-redup mt-0.5 tabular-nums">
                        Saldo tabungan saat ini: <span class="font-semibold text-tinta">{{ rupiah($selectedGoal->current_amount) }}</span>
                    </p>
                </div>
            @endif

            <!-- Pilih Sumber Saldo -->
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">
                    {{ $entryType === 'deposit' ? 'Sumber Saldo (Dipotong)' : 'Tujuan Saldo (Ditambah)' }}
                </label>
                <select
                    wire:model.live="selectedSourceId"
                    class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                    required
                >
                    <option value="">Pilih Sumber Saldo</option>
                    @foreach ($sources as $src)
                        <option value="{{ $src->id }}">
                            {{ $src->name }} (Saldo: {{ rupiah($src->current_balance) }})
                        </option>
                    @endforeach
                </select>
                @error('selectedSourceId') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Nominal -->
            <div>
                <label class="block text-sm font-medium text-tinta mb-1">Nominal</label>
                <x-money-input wire:model.live="entryAmount" placeholder="0" autofocus />
                @error('entryAmount') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Pratinjau Saldo -->
            @if ($selectedSource && $entryAmount > 0)
                @php
                    $sourceOld = $selectedSource->current_balance;
                    $sourceNew = $entryType === 'deposit'
                        ? $sourceOld - $entryAmount
                        : $sourceOld + $entryAmount;
                @endphp
                <div class="p-3 rounded-xl bg-kertas border border-garis text-xs space-y-1">
                    <p class="font-semibold text-tinta">Pratinjau Saldo:</p>
                    <p class="text-tinta-redup">
                        {{ $selectedSource->name }}:
                        <span class="tabular-nums font-medium text-tinta">{{ rupiah($sourceOld) }}</span>
                        →
                        <span class="tabular-nums font-bold {{ $sourceNew < 0 ? 'text-keluar' : 'text-masuk' }}">
                            {{ rupiah($sourceNew) }}
                        </span>
                    </p>
                    @if ($sourceNew < 0 && $entryType === 'deposit')
                        <p class="text-keluar font-semibold">⚠️ Saldo tidak mencukupi untuk melakukan setoran ini.</p>
                    @endif
                </div>
            @endif

            <!-- Tanggal & Catatan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-tinta mb-1">Tanggal</label>
                    <input
                        type="date"
                        wire:model="entryDate"
                        class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                        required
                    />
                    @error('entryDate') <p class="text-xs text-keluar mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-tinta mb-1">Catatan (Opsional)</label>
                    <input
                        type="text"
                        wire:model="entryNotes"
                        placeholder="Contoh: Tabungan rutin gaji"
                        class="block w-full rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none"
                    />
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-2 pt-4 border-t border-garis">
                <button
                    type="button"
                    wire:click="$set('showEntryModal', false)"
                    class="px-4 py-2 text-sm font-medium rounded-lg border border-garis text-tinta hover:bg-kertas transition"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 text-sm font-semibold rounded-lg bg-batik text-white hover:opacity-90 transition disabled:opacity-50"
                >
                    {{ $entryType === 'deposit' ? 'Simpan Setoran' : 'Simpan Penarikan' }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
