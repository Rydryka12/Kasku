<?php

namespace App\Livewire\Recurring;

use App\Enums\RecurringStatus;
use App\Models\AccountSource;
use App\Models\Category;
use App\Models\RecurringLog;
use App\Models\RecurringTransaction;
use App\Services\RecurringService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Transaksi Berulang - Kasku')]
class Index extends Component
{
    use WithPagination;

    // Modal state
    public bool $showModal = false;

    public ?int $editingId = null;

    // Form inputs
    public string $type = 'expense';

    public ?int $amount = null;

    public ?int $account_source_id = null;

    public ?int $category_id = null;

    public string $frequency = 'monthly';

    public string $start_date = '';

    public ?string $end_date = null;

    public string $description = '';

    public bool $has_tax = false;

    public ?int $tax_amount = null;

    public string $tax_note = '';

    // Selected recurring for log detail
    public ?int $selectedRecurringId = null;

    protected function rules(): array
    {
        return [
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'integer', 'min:1'],
            'account_source_id' => ['required', 'exists:account_sources,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'frequency' => ['required', 'in:daily,weekly,monthly,yearly'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:255'],
            'tax_amount' => ['nullable', 'integer', 'min:0'],
            'tax_note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function mount(): void
    {
        $this->start_date = now()->format('Y-m-d');
        $firstSource = AccountSource::where('user_id', Auth::id())->active()->first();
        $this->account_source_id = $firstSource?->id;
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->type = 'expense';
        $this->amount = null;
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = null;
        $this->description = '';
        $this->frequency = 'monthly';
        $this->has_tax = false;
        $this->tax_amount = null;
        $this->tax_note = '';

        $firstSource = AccountSource::where('user_id', Auth::id())->active()->first();
        $this->account_source_id = $firstSource?->id;
        $firstCat = Category::where('user_id', Auth::id())->where('type', $this->type)->active()->first();
        $this->category_id = $firstCat?->id;

        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $recurring = RecurringTransaction::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('update', $recurring);

        $this->resetValidation();
        $this->editingId = $recurring->id;
        $this->type = $recurring->type->value;
        $this->amount = $recurring->amount;
        $this->account_source_id = $recurring->account_source_id;
        $this->category_id = $recurring->category_id;
        $this->frequency = $recurring->frequency->value;
        $this->start_date = $recurring->start_date->format('Y-m-d');
        $this->end_date = $recurring->end_date?->format('Y-m-d');
        $this->description = $recurring->description ?? '';
        $this->tax_amount = $recurring->tax_amount;
        $this->tax_note = $recurring->tax_note ?? '';
        $this->has_tax = ($recurring->tax_amount > 0);

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'user_id' => Auth::id(),
            'account_source_id' => $this->account_source_id,
            'category_id' => $this->category_id,
            'type' => $this->type,
            'amount' => (int) $this->amount,
            'tax_amount' => ($this->has_tax && $this->type === 'expense') ? (int) $this->tax_amount : 0,
            'tax_note' => ($this->has_tax && $this->type === 'expense') ? $this->tax_note : null,
            'description' => $this->description ?: null,
            'frequency' => $this->frequency,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date ?: null,
        ];

        if ($this->editingId) {
            $recurring = RecurringTransaction::where('user_id', Auth::id())->findOrFail($this->editingId);
            $this->authorize('update', $recurring);

            $recurring->update($data);
            $this->dispatch('toast', ['message' => 'Jadwal transaksi berhasil diperbarui', 'type' => 'success']);
        } else {
            $data['next_run'] = $this->start_date;
            $data['status'] = RecurringStatus::Active->value;

            RecurringTransaction::create($data);
            $this->dispatch('toast', ['message' => 'Jadwal transaksi berhasil ditambahkan', 'type' => 'success']);
        }

        $this->showModal = false;
    }

    public function togglePause(int $id): void
    {
        $recurring = RecurringTransaction::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('update', $recurring);

        if ($recurring->status === RecurringStatus::Active) {
            $recurring->update(['status' => RecurringStatus::Paused]);
            $this->dispatch('toast', ['message' => 'Jadwal transaksi dijeda', 'type' => 'info']);
        } elseif ($recurring->status === RecurringStatus::Paused) {
            $recurring->update(['status' => RecurringStatus::Active]);
            $this->dispatch('toast', ['message' => 'Jadwal transaksi diaktifkan kembali', 'type' => 'success']);
        }
    }

    public function delete(int $id): void
    {
        $recurring = RecurringTransaction::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('delete', $recurring);

        $recurring->delete();
        $this->dispatch('toast', ['message' => 'Jadwal transaksi berhasil dihapus', 'type' => 'success']);
    }

    public function retryLog(int $logId, RecurringService $recurringService): void
    {
        $log = RecurringLog::where('user_id', Auth::id())->findOrFail($logId);
        $recurring = $log->recurringTransaction;

        $newLog = $recurringService->runOne($recurring, Carbon::parse($log->run_date));

        if ($newLog->status === 'success') {
            $this->dispatch('toast', ['message' => 'Berhasil dijalankan ulang!', 'type' => 'success']);
        } else {
            $this->dispatch('toast', ['message' => 'Gagal: '.$newLog->message, 'type' => 'error']);
        }
    }

    public function selectRecurring(?int $id): void
    {
        $this->selectedRecurringId = ($this->selectedRecurringId === $id) ? null : $id;
    }

    public function render()
    {
        $user = Auth::user();

        $recurrings = RecurringTransaction::where('user_id', $user->id)
            ->with(['accountSource', 'category', 'logs' => fn ($q) => $q->latest('run_date')->limit(1)])
            ->latest()
            ->paginate(10);

        $logsQuery = RecurringLog::where('user_id', $user->id)
            ->with(['recurringTransaction', 'transaction']);

        if ($this->selectedRecurringId) {
            $logsQuery->where('recurring_id', $this->selectedRecurringId);
        }

        $logs = $logsQuery->latest('run_date')->latest('id')->limit(20)->get();

        $sources = AccountSource::where('user_id', $user->id)->active()->get();
        $categories = Category::where('user_id', $user->id)->where('type', $this->type)->active()->get();

        return view('livewire.recurring.index', [
            'recurrings' => $recurrings,
            'logs' => $logs,
            'sources' => $sources,
            'categories' => $categories,
        ]);
    }
}
