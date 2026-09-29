<?php

namespace App\Livewire\Transactions;

use App\Models\AccountSource;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $type = 'all'; // all, income, expense

    public string $account_source_id = 'all';

    public string $category_id = 'all';

    public string $start_date = '';

    public string $end_date = '';

    public function mount(): void
    {
        // Default filter: bulan berjalan
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
    }

    #[On('transaction-saved')]
    public function refreshTransactions(): void
    {
        $this->resetPage();
    }

    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['search', 'type', 'account_source_id', 'category_id', 'start_date', 'end_date'])) {
            $this->resetPage();
        }
    }

    public function resetFilter(): void
    {
        $this->search = '';
        $this->type = 'all';
        $this->account_source_id = 'all';
        $this->category_id = 'all';
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function delete(int $id, TransactionService $transactionService): void
    {
        $tx = Transaction::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('delete', $tx);

        try {
            $transactionService->delete($tx);
            $this->dispatch('toast', ['message' => 'Transaksi berhasil dihapus', 'type' => 'success']);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function render()
    {
        $query = Transaction::where('user_id', Auth::id())
            ->with(['accountSource', 'category'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($this->search) {
            $query->where('description', 'like', '%'.$this->search.'%');
        }

        if ($this->type !== 'all') {
            $query->where('type', $this->type);
        }

        if ($this->account_source_id !== 'all') {
            $query->where('account_source_id', $this->account_source_id);
        }

        if ($this->category_id !== 'all') {
            $query->where('category_id', $this->category_id);
        }

        if ($this->start_date) {
            $query->whereDate('date', '>=', $this->start_date);
        }

        if ($this->end_date) {
            $query->whereDate('date', '<=', $this->end_date);
        }

        $transactions = $query->paginate(20);

        // Ambil data untuk opsi filter
        $sources = AccountSource::where('user_id', Auth::id())->get();
        $categories = Category::where('user_id', Auth::id())->get();

        return view('livewire.transactions.index', [
            'transactions' => $transactions,
            'sources' => $sources,
            'categories' => $categories,
        ]);
    }
}
