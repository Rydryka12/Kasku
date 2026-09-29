<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\AccountSource;
use App\Models\Reconciliation;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ReconciliationService
{
    public function __construct(
        protected BalanceService $balanceService
    ) {}

    /**
     * Hitung pratinjau rekonsiliasi antara saldo aplikasi dan saldo asli.
     */
    public function preview(AccountSource $source, int $actualBalance): array
    {
        $appBalance = $source->current_balance;
        $difference = $actualBalance - $appBalance;

        return [
            'app_balance' => $appBalance,
            'actual_balance' => $actualBalance,
            'difference' => $difference,
            'type' => $difference >= 0 ? TransactionType::Income : TransactionType::Expense,
            'adjustment_amount' => abs($difference),
        ];
    }

    /**
     * Lakukan rekonsiliasi saldo, opsional dengan penyesuaian saldo otomatis (K-04).
     */
    public function reconcile(AccountSource $source, int $actualBalance, bool $adjust = false, ?string $date = null): Reconciliation
    {
        $date = $date ?: now()->toDateString();

        return DB::transaction(function () use ($source, $actualBalance, $adjust, $date) {
            /** @var AccountSource $lockedSource */
            $lockedSource = AccountSource::where('id', $source->id)->lockForUpdate()->firstOrFail();

            $appBalance = $lockedSource->current_balance;
            $difference = $actualBalance - $appBalance;
            $adjustmentTx = null;

            if ($adjust && $difference !== 0) {
                $absDiff = abs($difference);

                if ($difference > 0) {
                    // Selisih positif: saldo asli lebih banyak -> pemasukan penyesuaian
                    $this->balanceService->credit($lockedSource, $absDiff);
                    $adjustmentTx = Transaction::create([
                        'user_id' => $lockedSource->user_id,
                        'account_source_id' => $lockedSource->id,
                        'category_id' => null,
                        'type' => TransactionType::Income,
                        'is_adjustment' => true,
                        'amount' => $absDiff,
                        'tax_amount' => 0,
                        'date' => $date,
                        'description' => 'Penyesuaian rekonsiliasi saldo (+)',
                    ]);
                } else {
                    // Selisih negatif: saldo asli lebih sedikit -> pengeluaran penyesuaian
                    $this->balanceService->debit($lockedSource, $absDiff);
                    $adjustmentTx = Transaction::create([
                        'user_id' => $lockedSource->user_id,
                        'account_source_id' => $lockedSource->id,
                        'category_id' => null,
                        'type' => TransactionType::Expense,
                        'is_adjustment' => true,
                        'amount' => $absDiff,
                        'tax_amount' => 0,
                        'date' => $date,
                        'description' => 'Penyesuaian rekonsiliasi saldo (-)',
                    ]);
                }
            }

            return Reconciliation::create([
                'user_id' => $lockedSource->user_id,
                'account_source_id' => $lockedSource->id,
                'app_balance' => $appBalance,
                'actual_balance' => $actualBalance,
                'difference' => $difference,
                'adjusted' => $adjust && ($difference !== 0),
                'adjustment_transaction_id' => $adjustmentTx?->id,
                'date' => $date,
            ]);
        });
    }
}
