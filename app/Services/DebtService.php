<?php

namespace App\Services;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\AccountSource;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DebtService
{
    public function __construct(
        protected BalanceService $balanceService
    ) {}

    /**
     * Membuat hutang atau piutang baru (K-11: tidak mengubah saldo).
     */
    public function create(User $user, array $data): Debt
    {
        return Debt::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'person' => $data['person'],
            'amount' => (int) $data['amount'],
            'paid_amount' => 0,
            'due_date' => $data['due_date'] ?? null,
            'status' => DebtStatus::Unpaid,
            'note' => $data['note'] ?? null,
        ]);
    }

    /**
     * Catat pembayaran/cicilan hutang atau piutang (K-11: hutang debit saldo, piutang kredit saldo).
     *
     * @throws InsufficientBalanceException
     * @throws InvalidArgumentException
     */
    public function pay(Debt $debt, AccountSource $source, int $amount, string $date, ?string $note = null): DebtPayment
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal pembayaran harus lebih besar dari 0.');
        }

        $remaining = $debt->remaining_amount;
        if ($amount > $remaining) {
            throw new InvalidArgumentException('Nominal pembayaran ('.rupiah($amount).') tidak boleh melebihi sisa ('.rupiah($remaining).').');
        }

        return DB::transaction(function () use ($debt, $source, $amount, $date, $note) {
            /** @var AccountSource $lockedSource */
            $lockedSource = AccountSource::where('id', $source->id)->lockForUpdate()->firstOrFail();

            if ($debt->type === DebtType::Debt) {
                // Membayar hutang: saldo berkurang
                $this->balanceService->debit($lockedSource, $amount);
            } else {
                // Menerima cicilan piutang: saldo bertambah
                $this->balanceService->credit($lockedSource, $amount);
            }

            $payment = DebtPayment::create([
                'user_id' => $debt->user_id,
                'debt_id' => $debt->id,
                'account_source_id' => $lockedSource->id,
                'amount' => $amount,
                'date' => $date,
                'note' => $note,
            ]);

            $debt->paid_amount += $amount;
            $this->refreshStatus($debt);
            $debt->save();

            return $payment;
        });
    }

    /**
     * Memperbarui status hutang berdasarkan paid_amount.
     */
    public function refreshStatus(Debt $debt): void
    {
        if ($debt->paid_amount <= 0) {
            $debt->status = DebtStatus::Unpaid;
        } elseif ($debt->paid_amount < $debt->amount) {
            $debt->status = DebtStatus::Partial;
        } else {
            $debt->status = DebtStatus::Paid;
        }
    }
}
