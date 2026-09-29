<?php

namespace App\Services;

use App\Models\AccountSource;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function __construct(
        protected BalanceService $balanceService
    ) {}

    /**
     * Catat transfer baru (K-01: validasi saldo cukup untuk amount + fee).
     *
     * @param array{
     *   user_id: int,
     *   from_source_id: int,
     *   to_source_id: int,
     *   amount: int,
     *   admin_fee?: int,
     *   date: string,
     *   notes?: ?string,
     * } $data
     */
    public function transfer(array $data): Transfer
    {
        if ($data['from_source_id'] === $data['to_source_id']) {
            throw new \InvalidArgumentException('Sumber asal dan tujuan transfer tidak boleh sama.');
        }

        $amount = (int) $data['amount'];
        $adminFee = (int) ($data['admin_fee'] ?? 0);
        $totalDebit = $amount + $adminFee;

        return DB::transaction(function () use ($data, $amount, $totalDebit) {
            $fromSource = AccountSource::where('id', $data['from_source_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $toSource = AccountSource::where('id', $data['to_source_id'])
                ->lockForUpdate()
                ->firstOrFail();

            // 1. Kurangi sumber asal (amount + admin_fee)
            $this->balanceService->debit($fromSource, $totalDebit, 'Transfer Keluar & Biaya Admin');

            // 2. Tambah sumber tujuan (hanya amount)
            $this->balanceService->credit($toSource, $amount, 'Transfer Masuk');

            return Transfer::create($data);
        });
    }

    /**
     * Hapus transfer (Rollback saldo kedua sumber dana secara atomik).
     */
    public function delete(Transfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            $fromSource = AccountSource::where('id', $transfer->from_source_id)
                ->lockForUpdate()
                ->firstOrFail();

            $toSource = AccountSource::where('id', $transfer->to_source_id)
                ->lockForUpdate()
                ->firstOrFail();

            $totalOriginalDebit = $transfer->amount + $transfer->admin_fee;

            // 1. Tarik kembali dana dari tujuan
            $this->balanceService->debit($toSource, $transfer->amount, 'Rollback Transfer Masuk (Penghapusan Transfer)');

            // 2. Kembalikan total dana ke sumber asal
            $this->balanceService->credit($fromSource, $totalOriginalDebit, 'Rollback Transfer Keluar & Biaya Admin (Penghapusan Transfer)');

            $transfer->delete();
        });
    }
}
