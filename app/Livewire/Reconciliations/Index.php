<?php

namespace App\Livewire\Reconciliations;

use App\Models\AccountSource;
use App\Models\Reconciliation;
use App\Services\ReconciliationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Rekonsiliasi Saldo - Kasku')]
class Index extends Component
{
    use WithPagination;

    public ?int $selectedSourceId = null;

    public ?int $actualBalance = null;

    public string $reconcileDate = '';

    public function mount(): void
    {
        $this->reconcileDate = now()->format('Y-m-d');
        $firstSource = AccountSource::where('user_id', Auth::id())->active()->first();
        $this->selectedSourceId = $firstSource?->id;
        $this->actualBalance = $firstSource?->current_balance;
    }

    public function updatedSelectedSourceId(): void
    {
        $source = AccountSource::where('user_id', Auth::id())->find($this->selectedSourceId);
        $this->actualBalance = $source?->current_balance;
    }

    public function submitReconciliation(bool $adjust, ReconciliationService $reconciliationService): void
    {
        $this->validate([
            'selectedSourceId' => ['required', 'exists:account_sources,id'],
            'actualBalance' => ['required', 'integer'],
            'reconcileDate' => ['required', 'date'],
        ], [
            'selectedSourceId.required' => 'Pilih sumber saldo.',
            'actualBalance.required' => 'Masukkan saldo asli.',
        ]);

        $source = AccountSource::where('user_id', Auth::id())->findOrFail($this->selectedSourceId);

        $reconciliation = $reconciliationService->reconcile(
            $source,
            (int) $this->actualBalance,
            $adjust,
            $this->reconcileDate
        );

        if ($reconciliation->adjusted) {
            $this->dispatch('toast', ['message' => 'Rekonsiliasi disimpan dan saldo disesuaikan', 'type' => 'success']);
        } else {
            $this->dispatch('toast', ['message' => 'Catatan rekonsiliasi berhasil disimpan', 'type' => 'success']);
        }

        // Refresh source balance
        $source->refresh();
        $this->actualBalance = $source->current_balance;
    }

    public function render()
    {
        $user = Auth::user();
        $sources = AccountSource::where('user_id', $user->id)->active()->get();

        $selectedSource = $this->selectedSourceId
            ? AccountSource::where('user_id', $user->id)->find($this->selectedSourceId)
            : null;

        $appBalance = $selectedSource?->current_balance ?? 0;
        $difference = ($this->actualBalance !== null) ? ((int) $this->actualBalance - $appBalance) : 0;

        $histories = Reconciliation::where('user_id', $user->id)
            ->with(['accountSource', 'adjustmentTransaction'])
            ->latest('date')
            ->latest('id')
            ->paginate(15);

        return view('livewire.reconciliations.index', [
            'sources' => $sources,
            'selectedSource' => $selectedSource,
            'appBalance' => $appBalance,
            'difference' => $difference,
            'histories' => $histories,
        ]);
    }
}
