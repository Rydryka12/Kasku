<?php

namespace App\Console\Commands;

use App\Services\RecurringService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RunRecurringCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring:run {--date= : Tanggal eksekusi (format: YYYY-MM-DD, default: hari ini)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jalankan semua transaksi berulang yang jatuh tempo';

    /**
     * Execute the console command.
     */
    public function handle(RecurringService $recurringService): int
    {
        $dateParam = $this->option('date');
        $date = $dateParam ? Carbon::parse($dateParam) : now();

        $this->info("Menjalankan transaksi berulang untuk tanggal: {$date->toDateString()}");

        $logs = $recurringService->runDue($date);

        $successCount = collect($logs)->where('status', 'success')->count();
        $failedCount = collect($logs)->where('status', 'failed')->count();

        $this->info("Selesai. Sukses: {$successCount}, Gagal: {$failedCount}");

        return Command::SUCCESS;
    }
}
