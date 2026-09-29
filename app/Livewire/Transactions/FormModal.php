<?php

namespace App\Livewire\Transactions;

use App\Exceptions\InsufficientBalanceException;
use App\Models\AccountSource;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\BudgetService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class FormModal extends Component
{
    use WithFileUploads;

    public bool $show = false;

    public ?int $editingTransactionId = null;

    public string $type = 'expense'; // 'expense' or 'income'

    public ?int $amount = null;

    public ?int $account_source_id = null;

    public ?int $category_id = null;

    public string $date = '';

    public string $description = '';

    public $receipt = null;

    protected function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['income', 'expense'])],
            'amount' => ['required', 'integer', 'min:1'],
            'account_source_id' => [
                'required',
                Rule::exists('account_sources', 'id')->where('user_id', Auth::id()),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', Auth::id())->where('type', $this->type),
            ],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'receipt' => ['nullable', 'image', 'max:2048'], // Max 2MB
        ];
    }

    protected function messages(): array
    {
        return [
            'amount.required' => 'Nominal transaksi wajib diisi.',
            'amount.min' => 'Nominal transaksi minimal Rp 1.',
            'account_source_id.required' => 'Pilih sumber saldo.',
            'category_id.required' => 'Pilih kategori.',
            'date.required' => 'Tanggal transaksi wajib diisi.',
        ];
    }

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    #[On('open-transaction-modal')]
    public function openModal(?int $id = null, string $defaultType = 'expense'): void
    {
        $this->resetValidation();
        $this->receipt = null;

        if ($id) {
            $tx = Transaction::where('user_id', Auth::id())->findOrFail($id);
            $this->authorize('update', $tx);

            $this->editingTransactionId = $tx->id;
            $this->type = $tx->type->value;
            $this->amount = $tx->amount;
            $this->account_source_id = $tx->account_source_id;
            $this->category_id = $tx->category_id;
            $this->date = $tx->date->format('Y-m-d');
            $this->description = $tx->description ?? '';
        } else {
            $this->editingTransactionId = null;
            $this->type = $defaultType;
            $this->amount = null;
            $this->date = now()->format('Y-m-d');
            $this->description = '';

            // Default account source (pertama aktif)
            $firstSource = AccountSource::where('user_id', Auth::id())->active()->first();
            $this->account_source_id = $firstSource?->id;

            // Default category (pertama aktif tipe ini)
            $firstCategory = Category::where('user_id', Auth::id())->where('type', $this->type)->active()->first();
            $this->category_id = $firstCategory?->id;
        }

        $this->show = true;
    }

    public function updatedType(): void
    {
        // Reset category bila tipe diubah
        $firstCategory = Category::where('user_id', Auth::id())->where('type', $this->type)->active()->first();
        $this->category_id = $firstCategory?->id;
    }

    public function save(TransactionService $transactionService): void
    {
        $this->validate();

        $data = [
            'user_id' => Auth::id(),
            'account_source_id' => $this->account_source_id,
            'category_id' => $this->category_id,
            'type' => $this->type,
            'amount' => (int) $this->amount,
            'date' => $this->date,
            'description' => $this->description ?: null,
        ];

        if ($this->receipt) {
            $data['receipt_path'] = $this->receipt->store('receipts', 'public');
        }

        try {
            if ($this->editingTransactionId) {
                $tx = Transaction::where('user_id', Auth::id())->findOrFail($this->editingTransactionId);
                $this->authorize('update', $tx);

                $transactionService->update($tx, $data);
                $this->dispatch('toast', ['message' => 'Transaksi berhasil diperbarui', 'type' => 'success']);
            } else {
                $transactionService->create($data);
                $this->dispatch('toast', ['message' => 'Transaksi berhasil dicatat', 'type' => 'success']);
            }

            $this->dispatch('transaction-saved');
            $this->show = false;
        } catch (InsufficientBalanceException $e) {
            $this->addError('amount', $e->getMessage());
        }
    }

    public function render(BudgetService $budgetService)
    {
        $user = Auth::user();
        $sources = AccountSource::where('user_id', $user->id)->active()->get();
        $categories = Category::where('user_id', $user->id)
            ->where('type', $this->type)
            ->active()
            ->orderBy('name')
            ->get();

        $budgetWarning = null;
        if ($this->type === 'expense' && $this->category_id && $this->amount > 0) {
            $budgetWarning = $budgetService->checkWarningForExpense(
                $user,
                $this->category_id,
                $this->amount,
                $this->date ?: now()->toDateString()
            );
        }

        return view('livewire.transactions.form-modal', [
            'sources' => $sources,
            'categories' => $categories,
            'budgetWarning' => $budgetWarning,
        ]);
    }
}
