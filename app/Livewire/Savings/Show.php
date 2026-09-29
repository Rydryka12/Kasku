<?php

namespace App\Livewire\Savings;

use App\Exceptions\InsufficientBalanceException;
use App\Models\AccountSource;
use App\Models\SavingsEntry;
use App\Models\SavingsGoal;
use App\Services\SavingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Show extends Component
{
    use WithPagination;

    public SavingsGoal $goal;

    // Modals
    public bool $showEditModal = false;

    public string $name = '';

    public ?int $target_amount = null;

    public ?string $deadline = null;

    // Setor / Tarik Modal
    public bool $showEntryModal = false;

    public string $entryType = 'deposit'; // 'deposit' or 'withdraw'

    public ?int $selectedSourceId = null;

    public ?int $entryAmount = null;

    public string $entryDate = '';

    public ?string $entryNotes = null;

    // Cancel Modal
    public bool $showCancelModal = false;

    public ?int $cancelReturnSourceId = null;

    // Delete Modal
    public bool $showDeleteModal = false;

    public ?int $deleteReturnSourceId = null;

    public function mount(SavingsGoal $goal): void
    {
        $this->authorize('view', $goal);
        $this->goal = $goal;
        $this->entryDate = now()->toDateString();
    }

    public function openEditModal(): void
    {
        $this->authorize('update', $this->goal);
        $this->name = $this->goal->name;
        $this->target_amount = $this->goal->target_amount;
        $this->deadline = $this->goal->deadline?->toDateString();
        $this->showEditModal = true;
    }

    public function saveEdit(SavingService $savingService): void
    {
        $this->authorize('update', $this->goal);

        $this->validate([
            'name' => 'required|string|max:100',
            'target_amount' => 'required|integer|min:1000',
            'deadline' => 'nullable|date',
        ]);

        $this->goal->update([
            'name' => $this->name,
            'target_amount' => $this->target_amount,
            'deadline' => $this->deadline ?: null,
        ]);

        $savingService->syncStatus($this->goal);
        $this->dispatch('toast', ['message' => 'Target tabungan berhasil diubah', 'type' => 'success']);
        $this->showEditModal = false;
    }

    public function openEntryModal(string $type = 'deposit'): void
    {
        $this->resetValidation();
        $this->entryType = $type;
        $this->entryAmount = null;
        $this->entryDate = now()->toDateString();
        $this->entryNotes = null;

        $firstSource = AccountSource::where('user_id', Auth::id())->active()->first();
        $this->selectedSourceId = $firstSource?->id;

        $this->showEntryModal = true;
    }

    public function submitEntry(SavingService $savingService): void
    {
        $this->authorize('update', $this->goal);

        $this->validate([
            'selectedSourceId' => 'required|exists:account_sources,id',
            'entryAmount' => 'required|integer|min:1',
            'entryDate' => 'required|date',
            'entryNotes' => 'nullable|string|max:255',
        ]);

        $source = AccountSource::where('user_id', Auth::id())->findOrFail($this->selectedSourceId);

        try {
            if ($this->entryType === 'deposit') {
                $savingService->deposit(
                    $this->goal,
                    $source,
                    $this->entryAmount,
                    $this->entryDate,
                    $this->entryNotes
                );
                $this->dispatch('toast', ['message' => 'Setoran berhasil dicatat', 'type' => 'success']);
            } else {
                $savingService->withdraw(
                    $this->goal,
                    $source,
                    $this->entryAmount,
                    $this->entryDate,
                    $this->entryNotes
                );
                $this->dispatch('toast', ['message' => 'Penarikan berhasil dicatat', 'type' => 'success']);
            }

            $this->goal->refresh();
            $this->showEntryModal = false;
        } catch (InsufficientBalanceException $e) {
            $this->addError('entryAmount', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            $this->addError('entryAmount', $e->getMessage());
        }
    }

    public function openCancelModal(): void
    {
        $this->authorize('update', $this->goal);
        $firstSource = AccountSource::where('user_id', Auth::id())->active()->first();
        $this->cancelReturnSourceId = $firstSource?->id;
        $this->showCancelModal = true;
    }

    public function confirmCancel(SavingService $savingService): void
    {
        $this->authorize('update', $this->goal);

        if ($this->goal->current_amount > 0) {
            $this->validate([
                'cancelReturnSourceId' => 'required|exists:account_sources,id',
            ], [
                'cancelReturnSourceId.required' => 'Pilih sumber saldo untuk pengembalian dana.',
            ]);
            $returnSource = AccountSource::where('user_id', Auth::id())->findOrFail($this->cancelReturnSourceId);
        } else {
            $returnSource = AccountSource::where('user_id', Auth::id())->firstOrFail();
        }

        $savingService->cancel($this->goal, $returnSource);
        $this->dispatch('toast', ['message' => 'Target tabungan berhasil dibatalkan dan dana dikembalikan', 'type' => 'info']);
        $this->showCancelModal = false;
        $this->goal->refresh();
    }

    public function openDeleteModal(): void
    {
        $this->authorize('delete', $this->goal);
        $firstSource = AccountSource::where('user_id', Auth::id())->active()->first();
        $this->deleteReturnSourceId = $firstSource?->id;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(SavingService $savingService)
    {
        $this->authorize('delete', $this->goal);

        $returnSource = null;
        if ($this->goal->current_amount > 0) {
            $this->validate([
                'deleteReturnSourceId' => 'required|exists:account_sources,id',
            ]);
            $returnSource = AccountSource::where('user_id', Auth::id())->findOrFail($this->deleteReturnSourceId);
        }

        $savingService->delete($this->goal, $returnSource);
        session()->flash('toast', ['message' => 'Target tabungan berhasil dihapus', 'type' => 'success']);

        return redirect()->route('savings.index');
    }

    public function render()
    {
        $sources = AccountSource::where('user_id', Auth::id())
            ->active()
            ->orderBy('name')
            ->get();

        $selectedSource = $this->selectedSourceId
            ? $sources->firstWhere('id', $this->selectedSourceId)
            : null;

        $entries = SavingsEntry::where('goal_id', $this->goal->id)
            ->with(['source'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('livewire.savings.show', [
            'sources' => $sources,
            'selectedSource' => $selectedSource,
            'entries' => $entries,
        ]);
    }
}
