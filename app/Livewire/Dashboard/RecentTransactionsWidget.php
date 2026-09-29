<?php

namespace App\Livewire\Dashboard;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class RecentTransactionsWidget extends Component
{
    public function placeholder()
    {
        return view('components.skeleton', ['variant' => 'row', 'count' => 5]);
    }

    public function render()
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->with(['accountSource', 'category'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(7)
            ->get();

        return view('livewire.dashboard.recent-transactions-widget', [
            'transactions' => $transactions,
        ]);
    }
}
