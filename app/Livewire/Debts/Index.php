<?php

namespace App\Livewire\Debts;

use App\Exceptions\InsufficientBalanceException;
use App\Models\AccountSource;
use App\Models\Debt;
use App\Services\DebtService;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Hutang & Piutang - Kasku')]
class Index extends Component
{
    public string $activeTab = 'debt'; // 'debt' or 'receivable'

    // Modal Create/Edit Debt
    public bool $showDebtModal = false;

    public ?int $editingDebtId = null;

    public string $debtType = 'debt';

    public string $person = '';

    public ?int $amount = null;

    public ?string $due_date = null;

    public string $note = '';

    // Modal Payment
    public bool $showPaymentModal = false;

    public ?int $paymentDebtId = null;

    public ?int $paymentAmount = null;

    public ?int $paymentSourceId = null;

    public string $paymentDate = '';

    public string $paymentNote = '';

    // Selected debt for payments history
    public ?int $viewPaymentsDebtId = null;

    protected function rules(): array
    {
        return [
            'debtType' => ['required', 'in:debt,receivable'],
            'person' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1'],
            'due_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function mount(): void
    {
        $this->paymentDate = now()->format('Y-m-d');
        $firstSource = AccountSource::where('user_id', Auth::id())->active()->first();
        $this->paymentSourceId = $firstSource?->id;
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->editingDebtId = null;
        $this->debtType = $this->activeTab;
        $this->person = '';
        $this->amount = null;
        $this->due_date = null;
        $this->note = '';

        $this->showDebtModal = true;
    }

    public function saveDebt(DebtService $debtService): void
    {
        $this->validate();

        if ($this->editingDebtId) {
            $debt = Debt::where('user_id', Auth::id())->findOrFail($this->editingDebtId);
            $this->authorize('update', $debt);

            $debt->update([
                'type' => $this->debtType,
                'person' => $this->person,
                'amount' => (int) $this->amount,
                'due_date' => $this->due_date ?: null,
                'note' => $this->note ?: null,
            ]);

            $debtService->refreshStatus($debt);
            $debt->save();

            $this->dispatch('toast', ['message' => 'Catatan berhasil diperbarui', 'type' => 'success']);
        } else {
            $debtService->create(Auth::user(), [
                'type' => $this->debtType,
                'person' => $this->person,
                'amount' => $this->amount,
                'due_date' => $this->due_date ?: null,
                'note' => $this->note ?: null,
            ]);

            $this->dispatch('toast', ['message' => 'Catatan berhasil ditambahkan', 'type' => 'success']);
        }

        $this->showDebtModal = false;
    }

    public function openPaymentModal(int $debtId): void
    {
        $this->resetValidation();
        $debt = Debt::where('user_id', Auth::id())->findOrFail($debtId);
        $this->authorize('update', $debt);

        $this->paymentDebtId = $debt->id;
        $this->paymentAmount = $debt->remaining_amount;
        $this->paymentDate = now()->format('Y-m-d');
        $this->paymentNote = '';

        $firstSource = AccountSource::where('user_id', Auth::id())->active()->first();
        $this->paymentSourceId = $firstSource?->id;

        $this->showPaymentModal = true;
    }

    public function savePayment(DebtService $debtService): void
    {
        $this->validate([
            'paymentDebtId' => ['required', 'exists:debts,id'],
            'paymentSourceId' => ['required', 'exists:account_sources,id'],
            'paymentAmount' => ['required', 'integer', 'min:1'],
            'paymentDate' => ['required', 'date'],
            'paymentNote' => ['nullable', 'string', 'max:255'],
        ], [
            'paymentSourceId.required' => 'Pilih sumber saldo.',
            'paymentAmount.required' => 'Nominal wajib diisi.',
            'paymentAmount.min' => 'Nominal minimal Rp 1.',
        ]);

        $debt = Debt::where('user_id', Auth::id())->findOrFail($this->paymentDebtId);
        $this->authorize('update', $debt);

        $source = AccountSource::where('user_id', Auth::id())->findOrFail($this->paymentSourceId);

        try {
            $debtService->pay(
                $debt,
                $source,
                (int) $this->paymentAmount,
                $this->paymentDate,
                $this->paymentNote ?: null
            );

            $this->dispatch('toast', ['message' => 'Pembayaran berhasil dicatat', 'type' => 'success']);
            $this->showPaymentModal = false;
        } catch (InsufficientBalanceException $e) {
            $this->addError('paymentAmount', $e->getMessage());
        } catch (InvalidArgumentException $e) {
            $this->addError('paymentAmount', $e->getMessage());
        }
    }

    public function togglePaymentsHistory(int $debtId): void
    {
        $this->viewPaymentsDebtId = ($this->viewPaymentsDebtId === $debtId) ? null : $debtId;
    }

    public function deleteDebt(int $id): void
    {
        $debt = Debt::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('delete', $debt);

        if ($debt->payments()->exists()) {
            $this->dispatch('toast', ['message' => 'Hutang dengan riwayat pembayaran tidak dapat dihapus', 'type' => 'error']);

            return;
        }

        $debt->delete();
        $this->dispatch('toast', ['message' => 'Catatan berhasil dihapus', 'type' => 'success']);
    }

    public function render()
    {
        $user = Auth::user();

        // Total unpaid debt and receivable
        $totalUnpaidDebt = Debt::where('user_id', $user->id)
            ->debts()
            ->unpaidOrPartial()
            ->selectRaw('SUM(amount - paid_amount) as total')
            ->value('total') ?? 0;

        $totalUnpaidReceivable = Debt::where('user_id', $user->id)
            ->receivables()
            ->unpaidOrPartial()
            ->selectRaw('SUM(amount - paid_amount) as total')
            ->value('total') ?? 0;

        // Current list based on activeTab
        $debts = Debt::where('user_id', $user->id)
            ->where('type', $this->activeTab)
            ->with(['payments.accountSource'])
            ->latest()
            ->get();

        $sources = AccountSource::where('user_id', $user->id)->active()->get();

        // Target debt for payment preview
        $activePaymentDebt = $this->paymentDebtId ? Debt::find($this->paymentDebtId) : null;
        $activePaymentSource = $this->paymentSourceId ? AccountSource::find($this->paymentSourceId) : null;

        return view('livewire.debts.index', [
            'totalUnpaidDebt' => (int) $totalUnpaidDebt,
            'totalUnpaidReceivable' => (int) $totalUnpaidReceivable,
            'debts' => $debts,
            'sources' => $sources,
            'activePaymentDebt' => $activePaymentDebt,
            'activePaymentSource' => $activePaymentSource,
        ]);
    }
}
