<?php

namespace App\Livewire\Sources;

use App\Enums\SourceType;
use App\Models\AccountSource;
use App\Services\BalanceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showModal = false;

    public ?int $editingSourceId = null;

    #[Validate]
    public string $name = '';

    public string $type = 'cash';

    public ?int $initial_balance = 0;

    public string $color = '#2B3A8C';

    public string $icon = 'wallet';

    public array $palette = [
        '#2B3A8C', '#0B7A55', '#B7791F', '#B93A2B', '#6B3FA0',
        '#0E7C86', '#C2547A', '#4F6B2D', '#7A5230', '#44515C',
    ];

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('account_sources')
                    ->where('user_id', Auth::id())
                    ->ignore($this->editingSourceId),
            ],
            'type' => ['required', Rule::enum(SourceType::class)],
            'initial_balance' => ['nullable', 'integer', 'min:0'],
            'color' => ['required', 'string', 'max:7'],
            'icon' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama sumber saldo wajib diisi.',
            'name.unique' => 'Nama sumber saldo sudah digunakan.',
            'initial_balance.min' => 'Saldo awal tidak boleh negatif.',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->editingSourceId = null;
        $this->name = '';
        $this->type = 'cash';
        $this->initial_balance = 0;
        $this->color = $this->palette[0];
        $this->icon = 'wallet';
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->resetValidation();
        $source = AccountSource::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('update', $source);

        $this->editingSourceId = $source->id;
        $this->name = $source->name;
        $this->type = $source->type->value;
        $this->initial_balance = $source->initial_balance;
        $this->color = $source->color;
        $this->icon = $source->icon ?? 'wallet';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingSourceId) {
            $source = AccountSource::where('user_id', Auth::id())->findOrFail($this->editingSourceId);
            $this->authorize('update', $source);

            $source->update([
                'name' => $this->name,
                'type' => $this->type,
                'color' => $this->color,
                'icon' => $this->icon,
            ]);

            $this->dispatch('toast', ['message' => 'Sumber saldo berhasil diperbarui', 'type' => 'success']);
        } else {
            $init = (int) ($this->initial_balance ?? 0);
            AccountSource::create([
                'user_id' => Auth::id(),
                'name' => $this->name,
                'type' => $this->type,
                'initial_balance' => $init,
                'current_balance' => $init,
                'color' => $this->color,
                'icon' => $this->icon,
            ]);

            $this->dispatch('toast', ['message' => 'Sumber saldo berhasil ditambahkan', 'type' => 'success']);
        }

        $this->showModal = false;
    }

    public function toggleArchive(int $id): void
    {
        $source = AccountSource::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('update', $source);

        if ($source->isArchived()) {
            $source->update(['archived_at' => null]);
            $this->dispatch('toast', ['message' => 'Sumber saldo berhasil diaktifkan kembali', 'type' => 'success']);
        } else {
            // Cek sisa saldo sebelum arsip (SRS 4.4)
            if ($source->current_balance > 0) {
                $this->dispatch('toast', [
                    'message' => 'Sumber dana masih memiliki saldo '.rupiah($source->current_balance).'. Pindahkan atau nolkan saldo terlebih dahulu.',
                    'type' => 'warning',
                ]);

                return;
            }

            $source->update(['archived_at' => now()]);
            $this->dispatch('toast', ['message' => 'Sumber saldo berhasil diarsipkan', 'type' => 'info']);
        }
    }

    public function delete(int $id): void
    {
        $source = AccountSource::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('delete', $source);

        // Jika sudah pernah dipakai mutasi, jangan hapus keras, arsipkan
        if ($source->transactions()->exists() || $source->incomingTransfers()->exists() || $source->outgoingTransfers()->exists()) {
            if ($source->current_balance > 0) {
                $this->dispatch('toast', [
                    'message' => 'Sumber dana memiliki saldo dan transaksi. Pindahkan saldo terlebih dahulu.',
                    'type' => 'warning',
                ]);

                return;
            }
            $source->update(['archived_at' => now()]);
            $this->dispatch('toast', ['message' => 'Sumber dana dialihkan ke arsip karena memiliki riwayat mutasi', 'type' => 'info']);

            return;
        }

        $source->delete();
        $this->dispatch('toast', ['message' => 'Sumber saldo berhasil dihapus', 'type' => 'success']);
    }

    public function render(BalanceService $balanceService)
    {
        $sources = AccountSource::where('user_id', Auth::id())
            ->active()
            ->orderBy('name')
            ->get();

        $archivedSources = AccountSource::where('user_id', Auth::id())
            ->archived()
            ->orderBy('name')
            ->get();

        $totalBalance = $balanceService->getTotalBalance(Auth::user());

        return view('livewire.sources.index', [
            'sources' => $sources,
            'archivedSources' => $archivedSources,
            'totalBalance' => $totalBalance,
        ]);
    }
}
