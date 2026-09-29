<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TransactionsExport implements FromView, ShouldAutoSize
{
    public function __construct(
        protected User $user,
        protected array $filters = []
    ) {}

    public function view(): View
    {
        $query = Transaction::where('user_id', $this->user->id)
            ->with(['accountSource', 'category'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if (! empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if (! empty($this->filters['account_source_id'])) {
            $query->where('account_source_id', $this->filters['account_source_id']);
        }

        if (! empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }

        if (! empty($this->filters['start_date'])) {
            $query->whereDate('date', '>=', $this->filters['start_date']);
        }

        if (! empty($this->filters['end_date'])) {
            $query->whereDate('date', '<=', $this->filters['end_date']);
        }

        if (! empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                    ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$s}%"));
            });
        }

        $transactions = $query->get();

        return view('exports.transactions-excel', [
            'transactions' => $transactions,
            'user' => $this->user,
        ]);
    }
}
