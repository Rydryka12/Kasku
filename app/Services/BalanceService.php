<?php

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Models\AccountSource;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    /**
     * Menambah saldo sumber.
     */
    public function credit(AccountSource $source, int $amount, string $reason = '', ?int $referenceId = null): void
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Credit amount must be positive.');
        }

        DB::transaction(function () use ($source, $amount) {
            $locked = AccountSource::where('id', $source->id)->lockForUpdate()->firstOrFail();
            $locked->current_balance += $amount;
            $locked->save();
            $source->current_balance = $locked->current_balance;
        });
    }

    /**
     * Mengurangi saldo sumber.
     * Throw InsufficientBalanceException jika saldo tidak cukup (K-01).
     */
    public function debit(AccountSource $source, int $amount, string $reason = '', ?int $referenceId = null): void
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Debit amount must be positive.');
        }

        DB::transaction(function () use ($source, $amount) {
            $locked = AccountSource::where('id', $source->id)->lockForUpdate()->firstOrFail();

            if ($locked->current_balance < $amount) {
                throw new InsufficientBalanceException(
                    sourceName: $locked->name,
                    availableBalance: $locked->current_balance,
                    requiredAmount: $amount
                );
            }

            $locked->current_balance -= $amount;
            $locked->save();
            $source->current_balance = $locked->current_balance;
        });
    }

    /**
     * Verifikasi integritas saldo (K-02).
     * Hitung: initial_balance + sum(income) - sum(expense) + sum(transfer masuk) - sum(transfer keluar + admin fee)
     * Mengembalikan selisih (0 jika valid).
     */
    public function verify(AccountSource $source): int
    {
        $initial = (int) $source->initial_balance;

        // Transaksi mandiri (bukan yang ditautkan ke transfer agar tidak double count)
        $income = (int) DB::table('transactions')
            ->where('account_source_id', $source->id)
            ->where('type', 'income')
            ->whereNull('transfer_id')
            ->sum('amount');

        $expense = (int) DB::table('transactions')
            ->where('account_source_id', $source->id)
            ->where('type', 'expense')
            ->whereNull('transfer_id')
            ->selectRaw('COALESCE(SUM(amount + tax_amount), 0) as total')
            ->value('total');

        $transfersIn = (int) DB::table('transfers')
            ->where('to_source_id', $source->id)
            ->sum('amount');

        $transfersOut = (int) DB::table('transfers')
            ->where('from_source_id', $source->id)
            ->selectRaw('COALESCE(SUM(amount + admin_fee), 0) as total')
            ->value('total');

        $savingsDeposits = (int) DB::table('savings_entries')
            ->where('source_id', $source->id)
            ->where('type', 'deposit')
            ->sum('amount');

        $savingsWithdrawals = (int) DB::table('savings_entries')
            ->where('source_id', $source->id)
            ->where('type', 'withdraw')
            ->sum('amount');

        // Pembayaran hutang (mengurangi saldo) dan cicilan piutang (menambah saldo) (SRS 6.3)
        $debtPaymentsOut = (int) DB::table('debt_payments')
            ->join('debts', 'debt_payments.debt_id', '=', 'debts.id')
            ->where('debt_payments.account_source_id', $source->id)
            ->where('debts.type', 'debt')
            ->sum('debt_payments.amount');

        $receivablePaymentsIn = (int) DB::table('debt_payments')
            ->join('debts', 'debt_payments.debt_id', '=', 'debts.id')
            ->where('debt_payments.account_source_id', $source->id)
            ->where('debts.type', 'receivable')
            ->sum('debt_payments.amount');

        $expectedBalance = $initial
            + $income
            - $expense
            + $transfersIn
            - $transfersOut
            - $savingsDeposits
            + $savingsWithdrawals
            - $debtPaymentsOut
            + $receivablePaymentsIn;

        return (int) $source->current_balance - $expectedBalance;
    }

    /**
     * Hitung total saldo semua sumber aktif milik user.
     */
    public function getTotalBalance(User $user): int
    {
        return (int) AccountSource::where('user_id', $user->id)
            ->active()
            ->sum('current_balance');
    }
}
