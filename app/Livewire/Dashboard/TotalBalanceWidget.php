<?php

namespace App\Livewire\Dashboard;

use App\Models\AccountSource;
use App\Models\Transaction;
use App\Services\BalanceService;
use App\Services\SavingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class TotalBalanceWidget extends Component
{
    public function placeholder()
    {
        return view('components.skeleton', ['variant' => 'panel']);
    }

    public function render(BalanceService $balanceService, SavingService $savingService)
    {
        $user = Auth::user();
        $availableBalance = $balanceService->getTotalBalance($user);
        $totalSavings = $savingService->getTotalSavings($user);
        $totalWealth = $availableBalance + $totalSavings;

        // Sumber saldo aktif untuk proporsi money-bar
        $sources = AccountSource::where('user_id', $user->id)
            ->active()
            ->orderByDesc('current_balance')
            ->get();

        $sourceData = [];
        foreach ($sources as $source) {
            $pct = $availableBalance > 0 ? ($source->current_balance / $availableBalance) * 100 : 0;
            $sourceData[] = [
                'id' => $source->id,
                'name' => $source->name,
                'balance' => $source->current_balance,
                'color' => $source->color,
                'percent' => $pct,
            ];
        }

        // Pemasukan & Pengeluaran Bulan Ini
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = now()->endOfMonth()->format('Y-m-d');

        $monthlyIncome = (int) Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->whereNull('transfer_id')
            ->sum('amount');

        $monthlyExpense = (int) Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->whereNull('transfer_id')
            ->sum('amount');

        $netCashflow = $monthlyIncome - $monthlyExpense;

        return view('livewire.dashboard.total-balance-widget', [
            'totalBalance' => $availableBalance,
            'availableBalance' => $availableBalance,
            'totalSavings' => $totalSavings,
            'totalWealth' => $totalWealth,
            'sourceData' => $sourceData,
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => $monthlyExpense,
            'netCashflow' => $netCashflow,
        ]);
    }
}
