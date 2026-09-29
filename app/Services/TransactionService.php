<?php

namespace App\Services;

use App\Models\AccountSource;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransactionService
{
    public function __construct(
        protected BalanceService $balanceService
    ) {}

    /**
     * Catat transaksi baru (K-01: validasi saldo cukup jika pengeluaran).
     *
     * @param array{
     *   user_id: int,
     *   account_source_id: int,
     *   category_id: int,
     *   type: string,
     *   amount: int,
     *   date: string,
     *   description?: ?string,
     *   receipt_path?: ?string,
     *   transfer_id?: ?int,
     * } $data
     */
    public function create(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $source = AccountSource::where('id', $data['account_source_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($data['type'] === 'expense') {
                $this->balanceService->debit(
                    $source,
                    (int) $data['amount'],
                    'Transaksi Pengeluaran',
                );
            } else {
                $this->balanceService->credit(
                    $source,
                    (int) $data['amount'],
                    'Transaksi Pemasukan',
                );
            }

            return Transaction::create($data);
        });
    }

    /**
     * Update transaksi (K-04: rollback efek lama, terapkan efek baru, atomic).
     *
     * @param array{
     *   account_source_id?: int,
     *   category_id?: int,
     *   type?: string,
     *   amount?: int,
     *   date?: string,
     *   description?: ?string,
     *   receipt_path?: ?string,
     * } $data
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        if ($transaction->is_adjustment) {
            throw new \DomainException('Transaksi penyesuaian rekonsiliasi tidak boleh diubah.');
        }

        return DB::transaction(function () use ($transaction, $data) {
            $oldSource = AccountSource::where('id', $transaction->account_source_id)
                ->lockForUpdate()
                ->firstOrFail();

            // 1. Rollback efek lama
            if ($transaction->type->value === 'expense') {
                $this->balanceService->credit(
                    $oldSource,
                    $transaction->amount,
                    'Rollback Transaksi Pengeluaran untuk Update',
                );
            } else {
                $this->balanceService->debit(
                    $oldSource,
                    $transaction->amount,
                    'Rollback Transaksi Pemasukan untuk Update',
                );
            }

            // Target source baru (bisa sama atau beda)
            $newSourceId = $data['account_source_id'] ?? $transaction->account_source_id;
            $newSource = ($newSourceId === $oldSource->id)
                ? $oldSource
                : AccountSource::where('id', $newSourceId)->lockForUpdate()->firstOrFail();

            $newType = $data['type'] ?? $transaction->type->value;
            $newAmount = isset($data['amount']) ? (int) $data['amount'] : $transaction->amount;

            // 2. Terapkan efek baru
            if ($newType === 'expense') {
                $this->balanceService->debit(
                    $newSource,
                    $newAmount,
                    'Update Transaksi Pengeluaran',
                );
            } else {
                $this->balanceService->credit(
                    $newSource,
                    $newAmount,
                    'Update Transaksi Pemasukan',
                );
            }

            // Hapus file struk lama jika struk diganti
            if (isset($data['receipt_path']) && $data['receipt_path'] !== $transaction->receipt_path) {
                if ($transaction->receipt_path && Storage::disk('public')->exists($transaction->receipt_path)) {
                    Storage::disk('public')->delete($transaction->receipt_path);
                }
            }

            $transaction->update($data);

            return $transaction;
        });
    }

    /**
     * Hapus transaksi (K-05: pulihkan saldo sebelum delete, atomic).
     */
    public function delete(Transaction $transaction): void
    {
        if ($transaction->is_adjustment) {
            throw new \DomainException('Transaksi penyesuaian rekonsiliasi tidak boleh dihapus.');
        }

        DB::transaction(function () use ($transaction) {
            $source = AccountSource::where('id', $transaction->account_source_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->type->value === 'expense') {
                // Pengeluaran dihapus -> kembalikan uang
                $this->balanceService->credit(
                    $source,
                    $transaction->amount,
                    'Rollback Hapus Transaksi Pengeluaran',
                );
            } else {
                // Pemasukan dihapus -> kurangi uang (bisa exception jika saldo tersisa tidak cukup)
                $this->balanceService->debit(
                    $source,
                    $transaction->amount,
                    'Rollback Hapus Transaksi Pemasukan',
                );
            }

            if ($transaction->receipt_path && Storage::disk('public')->exists($transaction->receipt_path)) {
                Storage::disk('public')->delete($transaction->receipt_path);
            }

            $transaction->delete();
        });
    }
}
