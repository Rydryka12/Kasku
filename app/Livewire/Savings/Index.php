<?php

namespace App\Livewire\Savings;

use App\Enums\GoalStatus;
use App\Exceptions\InsufficientBalanceException;
use App\Models\AccountSource;
use App\Models\SavingsGoal;
use App\Services\SavingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    // Modals
    public bool $showGoalModal = false;

    public ?int $editingGoalId = null;

    public string $name = '';

    public ?int $target_amount = null;

    public ?string $deadline = null;

    public string $icon = 'piggy-bank';

    // Deposit / Withdraw Modal
    public bool $showEntryModal = false;

    public string $entryType = 'deposit'; // 'deposit' or 'withdraw'

    public ?int $selectedGoalId = null;

    public ?int $selectedSourceId = null;

    public ?int $entryAmount = null;

    public string $entryDate = '';

    public ?string $entryNotes = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'target_amount' => 'required|integer|min:1000',
            'deadline' => 'nullable|date',
            'icon' => 'nullable|string|max:50',
        ];
    }

    public function mount(): void
    {
        $this->entryDate = now()->toDateString();
    }

    public function openCreateGoalModal(): void
    {
        $this->resetValidation();
        $this->editingGoalId = null;
        $this->name = '';
        $this->target_amount = null;
        $this->deadline = null;
        $this->icon = 'piggy-bank';
        $this->showGoalModal = true;
    }

    public function editGoal(int $id): void
    {
        $this->resetValidation();
        $goal = SavingsGoal::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('update', $goal);

        $this->editingGoalId = $goal->id;
        $this->name = $goal->name;
        $this->target_amount = $goal->target_amount;
        $this->deadline = $goal->deadline?->toDateString();
        $this->icon = $goal->icon ?? 'piggy-bank';
        $this->showGoalModal = true;
    }

    public function saveGoal(): void
    {
        $this->validate();

        if ($this->editingGoalId) {
            $goal = SavingsGoal::where('user_id', Auth::id())->findOrFail($this->editingGoalId);
            $this->authorize('update', $goal);

            $goal->update([
                'name' => $this->name,
                'target_amount' => $this->target_amount,
                'deadline' => $this->deadline ?: null,
                'icon' => $this->icon ?: 'piggy-bank',
            ]);

            app(SavingService::class)->syncStatus($goal);
            $this->dispatch('toast', ['message' => 'Target tabungan berhasil diperbarui', 'type' => 'success']);
        } else {
            SavingsGoal::create([
                'user_id' => Auth::id(),
                'name' => $this->name,
                'target_amount' => $this->target_amount,
                'current_amount' => 0,
                'deadline' => $this->deadline ?: null,
                'icon' => $this->icon ?: 'piggy-bank',
                'status' => GoalStatus::Active,
            ]);

            $this->dispatch('toast', ['message' => 'Target tabungan berhasil dibuat', 'type' => 'success']);
        }

        $this->showGoalModal = false;
    }

    public function openEntryModal(int $goalId, string $type = 'deposit'): void
    {
        $this->resetValidation();
        $goal = SavingsGoal::where('user_id', Auth::id())->findOrFail($goalId);
        $this->authorize('update', $goal);

        $this->selectedGoalId = $goal->id;
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
        $this->validate([
            'selectedGoalId' => 'required|exists:savings_goals,id',
            'selectedSourceId' => 'required|exists:account_sources,id',
            'entryAmount' => 'required|integer|min:1',
            'entryDate' => 'required|date',
            'entryNotes' => 'nullable|string|max:255',
        ], [
            'selectedSourceId.required' => 'Pilih sumber saldo.',
            'entryAmount.required' => 'Nominal wajib diisi.',
            'entryAmount.min' => 'Nominal minimal Rp 1.',
        ]);

        $goal = SavingsGoal::where('user_id', Auth::id())->findOrFail($this->selectedGoalId);
        $source = AccountSource::where('user_id', Auth::id())->findOrFail($this->selectedSourceId);
        $this->authorize('update', $goal);

        try {
            if ($this->entryType === 'deposit') {
                $savingService->deposit(
                    $goal,
                    $source,
                    $this->entryAmount,
                    $this->entryDate,
                    $this->entryNotes
                );
                $this->dispatch('toast', ['message' => 'Setoran berhasil dicatat', 'type' => 'success']);
            } else {
                $savingService->withdraw(
                    $goal,
                    $source,
                    $this->entryAmount,
                    $this->entryDate,
                    $this->entryNotes
                );
                $this->dispatch('toast', ['message' => 'Penarikan berhasil dicatat', 'type' => 'success']);
            }

            $this->showEntryModal = false;
        } catch (InsufficientBalanceException $e) {
            $this->addError('entryAmount', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            $this->addError('entryAmount', $e->getMessage());
        }
    }

    public function render(SavingService $savingService)
    {
        $user = Auth::user();
        $totalSavings = $savingService->getTotalSavings($user);

        $goals = SavingsGoal::where('user_id', $user->id)
            ->with(['entries'])
            ->orderByRaw("CASE WHEN status = 'active' THEN 1 WHEN status = 'achieved' THEN 2 ELSE 3 END")
            ->orderBy('id', 'desc')
            ->get();

        $sources = AccountSource::where('user_id', $user->id)
            ->active()
            ->orderBy('name')
            ->get();

        $selectedSource = $this->selectedSourceId
            ? $sources->firstWhere('id', $this->selectedSourceId)
            : null;

        $selectedGoal = $this->selectedGoalId
            ? $goals->firstWhere('id', $this->selectedGoalId)
            : null;

        return view('livewire.savings.index', [
            'totalSavings' => $totalSavings,
            'goals' => $goals,
            'sources' => $sources,
            'selectedSource' => $selectedSource,
            'selectedGoal' => $selectedGoal,
        ]);
    }
}
