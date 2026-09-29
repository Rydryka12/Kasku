<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class BudgetService
{
    /**
     * Hitung penggunaan budget (K-08: pengeluaran non-penyesuaian pada kategori & bulan tersebut).
     *
     * @return array{
     *   limit: int,
     *   used: int,
     *   remaining: int,
     *   percent: int,
     *   status: string,
     * }
     */
    public function usage(Budget $budget): array
    {
        $startDate = Carbon::createFromFormat('Y-m', $budget->month)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromFormat('Y-m', $budget->month)->endOfMonth()->toDateString();

        $used = (int) Transaction::where('user_id', $budget->user_id)
            ->where('category_id', $budget->category_id)
            ->where('type', 'expense')
            ->where('is_adjustment', false)
            ->whereNull('transfer_id')
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('SUM(amount + tax_amount) as total')
            ->value('total') ?? 0;

        $limit = $budget->limit_amount;
        $percent = $limit > 0 ? (int) round(($used / $limit) * 100) : 0;
        $remaining = $limit - $used;

        $status = match (true) {
            $percent >= 100 => 'exceeded', // Lewat (>=100%)
            $percent >= 80 => 'warning',   // Waspada (80-99%)
            default => 'safe',             // Aman (<80%)
        };

        return [
            'limit' => $limit,
            'used' => $used,
            'remaining' => $remaining,
            'percent' => $percent,
            'status' => $status,
        ];
    }

    /**
     * Dapatkan status budget: 'safe', 'warning', 'exceeded'.
     */
    public function status(Budget $budget): string
    {
        return $this->usage($budget)['status'];
    }

    /**
     * Salin budget dari bulan sebelumnya jika belum ada (FR-BDG-05).
     */
    public function copyFromPreviousMonth(User $user, string $targetMonth): int
    {
        $prevMonth = Carbon::createFromFormat('Y-m', $targetMonth)->subMonth()->format('Y-m');

        $previousBudgets = Budget::where('user_id', $user->id)
            ->where('month', $prevMonth)
            ->get();

        $copied = 0;
        foreach ($previousBudgets as $oldBudget) {
            $exists = Budget::where('user_id', $user->id)
                ->where('month', $targetMonth)
                ->where('category_id', $oldBudget->category_id)
                ->exists();

            if (! $exists) {
                Budget::create([
                    'user_id' => $user->id,
                    'category_id' => $oldBudget->category_id,
                    'month' => $targetMonth,
                    'limit_amount' => $oldBudget->limit_amount,
                ]);
                $copied++;
            }
        }

        return $copied;
    }

    /**
     * Cek apakah penambahan pengeluaran akan memicu status warning (>=80%) atau exceeded (>=100%) (FR-BDG-04).
     */
    public function checkWarningForExpense(User $user, int $categoryId, int $amount, string $date): ?array
    {
        $month = Carbon::parse($date)->format('Y-m');

        $budget = Budget::where('user_id', $user->id)
            ->where('category_id', $categoryId)
            ->where('month', $month)
            ->first();

        if (! $budget) {
            return null;
        }

        $currentUsage = $this->usage($budget);
        $newUsed = $currentUsage['used'] + $amount;
        $newPercent = (int) round(($newUsed / $budget->limit_amount) * 100);

        if ($newPercent >= 80) {
            return [
                'category_name' => $budget->category->name ?? 'Kategori',
                'new_percent' => $newPercent,
                'status' => $newPercent >= 100 ? 'exceeded' : 'warning',
                'message' => $newPercent >= 100
                    ? "Pengeluaran ini akan melebihi budget ({$newPercent}%)"
                    : "Pengeluaran ini akan mencapai {$newPercent}% dari budget",
            ];
        }

        return null;
    }
}
