<?php

namespace App\Console\Commands;

use App\Models\AccountSource;
use App\Services\BalanceService;
use Illuminate\Console\Command;

class VerifyBalancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balances:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifikasi konsistensi saldo seluruh sumber dana terhadap riwayat transaksi dan transfer';

    /**
     * Execute the console command.
     */
    public function handle(BalanceService $balanceService): int
    {
        $sources = AccountSource::with('user')->get();
        $discrepancies = 0;

        $this->info("Memeriksa {$sources->count()} sumber dana...");

        $headers = ['ID', 'User', 'Sumber', 'Saldo Tercatat', 'Selisih', 'Status'];
        $rows = [];

        foreach ($sources as $source) {
            $diff = $balanceService->verify($source);

            if ($diff !== 0) {
                $discrepancies++;
                $rows[] = [
                    $source->id,
                    $source->user->email ?? '-',
                    $source->name,
                    rupiah($source->current_balance),
                    rupiah($diff, true),
                    'DISCREPANCY',
                ];
            } else {
                $rows[] = [
                    $source->id,
                    $source->user->email ?? '-',
                    $source->name,
                    rupiah($source->current_balance),
                    'Rp 0',
                    'MATCH',
                ];
            }
        }

        $this->table($headers, $rows);

        if ($discrepancies > 0) {
            $this->error("Ditemukan {$discrepancies} sumber dana dengan saldo tidak konsisten!");

            return Command::FAILURE;
        }

        $this->info('Semua saldo konsisten dan terverifikasi secara matematis.');

        return Command::SUCCESS;
    }
}
