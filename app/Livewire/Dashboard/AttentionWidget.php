<?php

namespace App\Livewire\Dashboard;

use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Debt;
use App\Models\RecurringTransaction;
use App\Services\BudgetService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class AttentionWidget extends Component
{
    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="h-24 bg-permukaan/50 animate-pulse rounded-2xl border border-garis"></div>
        HTML;
    }

    public function render(BudgetService $budgetService)
    {
        $user = Auth::user();
        $items = [];
        $today = now()->startOfDay();

        // 1. Budget >= 80%
        $currentMonth = now()->format('Y-m');
        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $currentMonth)
            ->with('category')
            ->get();

        foreach ($budgets as $b) {
            $usage = $budgetService->usage($b);
            if ($usage['percentage'] >= 80) {
                $isExceeded = $usage['percentage'] >= 100;
                $items[] = [
                    'type' => 'budget',
                    'title' => "Budget {$b->category->name}",
                    'message' => $isExceeded
                        ? "Melebihi budget ({$usage['percentage']}%) · Terpakai ".rupiah($usage['used']).' dari '.rupiah($b->limit_amount)
                        : "{$usage['percentage']}% dari budget · Terpakai ".rupiah($usage['used']).' dari '.rupiah($b->limit_amount),
                    'severity' => $isExceeded ? 'danger' : 'warning',
                    'url' => route('budgets.index'),
                    'action_label' => 'Lihat Budget',
                ];
            }
        }

        // 2. Tagihan berulang 7 hari ke depan (khusus pengeluaran aktif)
        $sevenDaysAhead = now()->addDays(7)->endOfDay()->format('Y-m-d');
        $todayStr = now()->format('Y-m-d');

        $dueRecurrings = RecurringTransaction::where('user_id', $user->id)
            ->active()
            ->where('type', TransactionType::Expense)
            ->where('next_run', '<=', $sevenDaysAhead)
            ->with(['accountSource', 'category'])
            ->get();

        foreach ($dueRecurrings as $rec) {
            $dueDate = Carbon::parse($rec->next_run)->startOfDay();
            $diff = (int) $today->diffInDays($dueDate, false);

            if ($diff < 0) {
                $dueText = 'Terlewat '.abs($diff).' hari';
                $severity = 'danger';
            } elseif ($diff === 0) {
                $dueText = 'Jatuh tempo hari ini';
                $severity = 'danger';
            } elseif ($diff === 1) {
                $dueText = 'Jatuh tempo besok';
                $severity = 'warning';
            } else {
                $dueText = "Jatuh tempo {$diff} hari lagi";
                $severity = 'warning';
            }

            $totalNominal = $rec->amount + $rec->tax_amount;
            $name = $rec->description ?: ($rec->category?->name ?? 'Tagihan');

            $items[] = [
                'type' => 'recurring',
                'title' => $name,
                'message' => rupiah($totalNominal)." · {$dueText} (via {$rec->accountSource->name})",
                'severity' => $severity,
                'url' => route('recurring.index'),
                'action_label' => 'Jadwal Berulang',
            ];
        }

        // 3. Hutang jatuh tempo (Hutang kita, H-3 atau terlewat)
        $threeDaysAhead = now()->addDays(3)->endOfDay()->format('Y-m-d');
        $debtsDue = Debt::where('user_id', $user->id)
            ->debts()
            ->unpaidOrPartial()
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $threeDaysAhead)
            ->get();

        foreach ($debtsDue as $d) {
            $dueDate = Carbon::parse($d->due_date)->startOfDay();
            $diff = (int) $today->diffInDays($dueDate, false);

            if ($diff < 0) {
                $dueText = 'Terlewat '.abs($diff).' hari';
                $severity = 'danger';
            } elseif ($diff === 0) {
                $dueText = 'Jatuh tempo hari ini';
                $severity = 'danger';
            } elseif ($diff === 1) {
                $dueText = 'Jatuh tempo besok';
                $severity = 'warning';
            } else {
                $dueText = "Jatuh tempo {$diff} hari lagi";
                $severity = 'warning';
            }

            $items[] = [
                'type' => 'debt',
                'title' => "Hutang ke {$d->person}",
                'message' => 'Sisa '.rupiah($d->remaining_amount)." · {$dueText}",
                'severity' => $severity,
                'url' => route('debts.index'),
                'action_label' => 'Bayar Hutang',
            ];
        }

        return view('livewire.dashboard.attention-widget', [
            'items' => $items,
        ]);
    }
}
