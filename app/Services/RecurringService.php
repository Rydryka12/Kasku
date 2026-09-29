<?php

namespace App\Services;

use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;
use App\Enums\TransactionType;
use App\Models\AccountSource;
use App\Models\RecurringLog;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Notifications\RecurringFailedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringService
{
    public function __construct(
        protected BalanceService $balanceService
    ) {}

    /**
     * Menjalankan semua transaksi berulang yang jatuh tempo (<= $today) dan mengejar tanggal terlewat (K-12).
     *
     * @return array<int, RecurringLog>
     */
    public function runDue(Carbon $today): array
    {
        $todayStr = $today->format('Y-m-d');

        $dueRecurrings = RecurringTransaction::query()
            ->active()
            ->whereDate('next_run', '<=', $todayStr)
            ->where(function ($q) use ($todayStr) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $todayStr);
            })
            ->get();

        $logs = [];

        foreach ($dueRecurrings as $recurring) {
            // Proses berurutan dari next_run sampai <= today
            while ($recurring->status === RecurringStatus::Active && $recurring->next_run->format('Y-m-d') <= $todayStr) {
                $currentRunDate = Carbon::parse($recurring->next_run);
                $log = $this->runOne($recurring, $currentRunDate);
                $logs[] = $log;

                // Hitung jadwal berikutnya
                $next = $this->nextRunDate($recurring, $currentRunDate);
                $recurring->next_run = $next;

                // Cek apakah sudah melewati batas akhir
                if ($recurring->end_date && $next->format('Y-m-d') > $recurring->end_date->format('Y-m-d')) {
                    $recurring->status = RecurringStatus::Ended;
                }

                $recurring->save();
            }
        }

        return $logs;
    }

    /**
     * Menjalankan satu eksekusi transaksi berulang pada tanggal tertentu secara idempoten (K-12).
     */
    public function runOne(RecurringTransaction $recurring, Carbon $runDate): RecurringLog
    {
        $runDateStr = $runDate->format('Y-m-d');

        // Cek log yang sudah ada
        $existingLog = RecurringLog::where('recurring_id', $recurring->id)
            ->where('run_date', $runDateStr)
            ->first();

        // Idempoten: bila sudah pernah sukses, jangan lakukan apa-apa
        if ($existingLog && $existingLog->status === 'success') {
            return $existingLog;
        }

        $totalNeeded = $recurring->amount + ($recurring->tax_amount ?? 0);

        return DB::transaction(function () use ($recurring, $runDateStr, $existingLog, $totalNeeded) {
            /** @var AccountSource $source */
            $source = AccountSource::where('id', $recurring->account_source_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Bila pengeluaran dan saldo tidak cukup -> catat failed, kirim notifikasi
            if ($recurring->type === TransactionType::Expense && $source->current_balance < $totalNeeded) {
                $message = 'Saldo tidak mencukupi (tersedia '.rupiah($source->current_balance).', dibutuhkan '.rupiah($totalNeeded).')';

                $log = $existingLog ?? new RecurringLog;
                $log->fill([
                    'user_id' => $recurring->user_id,
                    'recurring_id' => $recurring->id,
                    'run_date' => $runDateStr,
                    'status' => 'failed',
                    'message' => $message,
                    'transaction_id' => null,
                ]);
                $log->save();

                // Kirim notifikasi database ke user
                $recurring->user->notify(new RecurringFailedNotification($recurring, $message));

                return $log;
            }

            // Potong / tambah saldo via BalanceService
            if ($recurring->type === TransactionType::Expense) {
                $this->balanceService->debit($source, $totalNeeded);
            } else {
                $this->balanceService->credit($source, $recurring->amount);
            }

            // Buat transaksi
            $tx = Transaction::create([
                'user_id' => $recurring->user_id,
                'account_source_id' => $source->id,
                'category_id' => $recurring->category_id,
                'type' => $recurring->type,
                'amount' => $recurring->amount,
                'tax_amount' => $recurring->tax_amount ?? 0,
                'tax_note' => $recurring->tax_note,
                'date' => $runDateStr,
                'description' => $recurring->description ?: ('Transaksi Berulang ('.$recurring->frequency->label().')'),
                'recurring_id' => $recurring->id,
                'is_adjustment' => false,
            ]);

            $log = $existingLog ?? new RecurringLog;
            $log->fill([
                'user_id' => $recurring->user_id,
                'recurring_id' => $recurring->id,
                'run_date' => $runDateStr,
                'status' => 'success',
                'message' => 'Berhasil dieksekusi',
                'transaction_id' => $tx->id,
            ]);
            $log->save();

            return $log;
        });
    }

    /**
     * Hitung tanggal jatuh tempo berikutnya dengan aturan klamp tanggal 29-31 (K-12).
     */
    public function nextRunDate(RecurringTransaction $recurring, ?Carbon $from = null): Carbon
    {
        $base = $from ? $from->copy() : Carbon::parse($recurring->next_run);
        $startDate = Carbon::parse($recurring->start_date);
        $startDay = $startDate->day;

        return match ($recurring->frequency) {
            RecurringFrequency::Daily => $base->addDay()->startOfDay(),
            RecurringFrequency::Weekly => $base->addWeek()->startOfDay(),
            RecurringFrequency::Monthly => (function () use ($base, $startDay) {
                // Maju 1 bulan kalender murni
                $targetYear = $base->year;
                $targetMonth = $base->month + 1;
                if ($targetMonth > 12) {
                    $targetMonth = 1;
                    $targetYear++;
                }

                $daysInTarget = Carbon::create($targetYear, $targetMonth, 1)->daysInMonth;
                $clampedDay = min($startDay, $daysInTarget);

                return Carbon::create($targetYear, $targetMonth, $clampedDay)->startOfDay();
            })(),
            RecurringFrequency::Yearly => (function () use ($base, $startDate, $startDay) {
                $targetYear = $base->year + 1;
                $targetMonth = $startDate->month;
                $daysInTarget = Carbon::create($targetYear, $targetMonth, 1)->daysInMonth;
                $clampedDay = min($startDay, $daysInTarget);

                return Carbon::create($targetYear, $targetMonth, $clampedDay)->startOfDay();
            })(),
        };
    }
}
