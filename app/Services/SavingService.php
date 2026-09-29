<?php

namespace App\Services;

use App\Enums\EntryType;
use App\Enums\GoalStatus;
use App\Models\AccountSource;
use App\Models\SavingsEntry;
use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SavingService
{
    public function __construct(
        protected BalanceService $balanceService
    ) {}

    /**
     * Setor uang ke target tabungan dari sumber saldo (FR-SAV-02).
     */
    public function deposit(
        SavingsGoal $goal,
        AccountSource $source,
        int $amount,
        string $date,
        ?string $notes = null
    ): SavingsEntry {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Nominal setoran harus lebih dari 0.');
        }

        return DB::transaction(function () use ($goal, $source, $amount, $date, $notes) {
            $lockedSource = AccountSource::where('id', $source->id)->lockForUpdate()->firstOrFail();
            $lockedGoal = SavingsGoal::where('id', $goal->id)->lockForUpdate()->firstOrFail();

            // Potong saldo sumber saldo
            $this->balanceService->debit(
                $lockedSource,
                $amount,
                "Setoran Tabungan: {$lockedGoal->name}"
            );

            // Buat entri tabungan
            $entry = SavingsEntry::create([
                'user_id' => $lockedGoal->user_id,
                'goal_id' => $lockedGoal->id,
                'source_id' => $lockedSource->id,
                'type' => EntryType::Deposit,
                'amount' => $amount,
                'date' => $date,
                'notes' => $notes,
            ]);

            // Tambah current_amount pada target
            $lockedGoal->current_amount += $amount;
            $this->syncStatus($lockedGoal);

            $goal->refresh();

            return $entry;
        });
    }

    /**
     * Tarik uang dari target tabungan ke sumber saldo (FR-SAV-03).
     */
    public function withdraw(
        SavingsGoal $goal,
        AccountSource $source,
        int $amount,
        string $date,
        ?string $notes = null
    ): SavingsEntry {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Nominal penarikan harus lebih dari 0.');
        }

        return DB::transaction(function () use ($goal, $source, $amount, $date, $notes) {
            $lockedSource = AccountSource::where('id', $source->id)->lockForUpdate()->firstOrFail();
            $lockedGoal = SavingsGoal::where('id', $goal->id)->lockForUpdate()->firstOrFail();

            if ($amount > $lockedGoal->current_amount) {
                throw new \InvalidArgumentException('Nominal penarikan melebihi saldo tabungan saat ini.');
            }

            // Tambah saldo sumber saldo
            $this->balanceService->credit(
                $lockedSource,
                $amount,
                "Penarikan Tabungan: {$lockedGoal->name}"
            );

            // Buat entri tabungan
            $entry = SavingsEntry::create([
                'user_id' => $lockedGoal->user_id,
                'goal_id' => $lockedGoal->id,
                'source_id' => $lockedSource->id,
                'type' => EntryType::Withdraw,
                'amount' => $amount,
                'date' => $date,
                'notes' => $notes,
            ]);

            // Kurangi current_amount pada target
            $lockedGoal->current_amount -= $amount;
            $this->syncStatus($lockedGoal);

            $goal->refresh();

            return $entry;
        });
    }

    /**
     * Batalkan target tabungan dan kembalikan seluruh sisa dana ke sumber saldo pilihan (K-09, FR-SAV-07).
     */
    public function cancel(SavingsGoal $goal, AccountSource $returnSource): void
    {
        DB::transaction(function () use ($goal, $returnSource) {
            $lockedGoal = SavingsGoal::where('id', $goal->id)->lockForUpdate()->firstOrFail();
            $lockedReturnSource = AccountSource::where('id', $returnSource->id)->lockForUpdate()->firstOrFail();

            if ($lockedGoal->current_amount > 0) {
                $refundAmount = $lockedGoal->current_amount;

                $this->balanceService->credit(
                    $lockedReturnSource,
                    $refundAmount,
                    "Pengembalian Dana Pembatalan Target: {$lockedGoal->name}"
                );

                SavingsEntry::create([
                    'user_id' => $lockedGoal->user_id,
                    'goal_id' => $lockedGoal->id,
                    'source_id' => $lockedReturnSource->id,
                    'type' => EntryType::Withdraw,
                    'amount' => $refundAmount,
                    'date' => now()->toDateString(),
                    'notes' => 'Pengembalian dana akibat pembatalan target',
                ]);

                $lockedGoal->current_amount = 0;
            }

            $lockedGoal->status = GoalStatus::Cancelled;
            $lockedGoal->save();

            $goal->refresh();
        });
    }

    /**
     * Hapus target tabungan. Jika berisi dana, kembalikan dulu ke sumber saldo (K-09).
     */
    public function delete(SavingsGoal $goal, ?AccountSource $returnSource = null): void
    {
        DB::transaction(function () use ($goal, $returnSource) {
            $lockedGoal = SavingsGoal::where('id', $goal->id)->lockForUpdate()->firstOrFail();

            if ($lockedGoal->current_amount > 0) {
                if (! $returnSource) {
                    throw new \InvalidArgumentException('Sumber saldo pengembalian dana wajib dipilih.');
                }

                $lockedReturnSource = AccountSource::where('id', $returnSource->id)->lockForUpdate()->firstOrFail();

                $this->balanceService->credit(
                    $lockedReturnSource,
                    $lockedGoal->current_amount,
                    "Pengembalian Dana Hapus Target: {$lockedGoal->name}"
                );
            }

            $lockedGoal->delete();
        });
    }

    /**
     * Sinkronisasi status ketercapaian target (K-09).
     */
    public function syncStatus(SavingsGoal $goal): void
    {
        if ($goal->status === GoalStatus::Cancelled) {
            return;
        }

        if ($goal->current_amount >= $goal->target_amount) {
            $goal->status = GoalStatus::Achieved;
        } else {
            $goal->status = GoalStatus::Active;
        }

        $goal->save();
    }

    /**
     * Total tabungan aktif dan tercapai milik user (K-10).
     */
    public function getTotalSavings(User $user): int
    {
        return (int) SavingsGoal::where('user_id', $user->id)
            ->activeOrAchieved()
            ->sum('current_amount');
    }
}
