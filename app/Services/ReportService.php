<?php

namespace App\Services;

use App\Models\AccountSource;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Hitung rentang tanggal (startDate, endDate) dari parameter periode.
     *
     * @return array{0: string, 1: string}
     */
    public function getDateRange(string $periodType, ?string $year = null, ?string $month = null): array
    {
        $year = $year ?: now()->format('Y');

        if ($periodType === 'yearly') {
            $startDate = Carbon::createFromDate((int) $year, 1, 1)->startOfDay()->toDateString();
            $endDate = Carbon::createFromDate((int) $year, 12, 31)->endOfDay()->toDateString();
        } else {
            // monthly: default month is current month (1-12 or format 'Y-m')
            if ($month && str_contains($month, '-')) {
                [$y, $m] = explode('-', $month);
                $date = Carbon::createFromDate((int) $y, (int) $m, 1);
            } else {
                $m = $month ?: now()->format('m');
                $date = Carbon::createFromDate((int) $year, (int) $m, 1);
            }
            $startDate = $date->startOfMonth()->toDateString();
            $endDate = $date->endOfMonth()->toDateString();
        }

        return [$startDate, $endDate];
    }

    /**
     * Ringkasan Rekap Keuangan: Pemasukan, Pengeluaran, Selisih, dan Rincian Waktu.
     */
    public function summary(User $user, string $periodType, ?string $year = null, ?string $month = null): array
    {
        [$startDate, $endDate] = $this->getDateRange($periodType, $year, $month);

        // Pemasukan (transaksi normal non-transfer, non-penyesuaian K-04)
        $totalIncome = (int) Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereNull('transfer_id')
            ->where('is_adjustment', false)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        // Pengeluaran
        $totalExpense = (int) Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereNull('transfer_id')
            ->where('is_adjustment', false)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        // Biaya admin transfer
        $totalTransferFee = (int) Transfer::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('admin_fee');

        $netCashflow = $totalIncome - $totalExpense - $totalTransferFee;

        // Breakdown data
        $breakdown = [];
        if ($periodType === 'yearly') {
            // 12 bulan
            for ($m = 1; $m <= 12; $m++) {
                $mStart = Carbon::createFromDate((int) ($year ?: now()->year), $m, 1)->startOfMonth()->toDateString();
                $mEnd = Carbon::createFromDate((int) ($year ?: now()->year), $m, 1)->endOfMonth()->toDateString();

                $inc = (int) Transaction::where('user_id', $user->id)
                    ->where('type', 'income')
                    ->whereNull('transfer_id')
                    ->where('is_adjustment', false)
                    ->whereBetween('date', [$mStart, $mEnd])
                    ->sum('amount');

                $exp = (int) Transaction::where('user_id', $user->id)
                    ->where('type', 'expense')
                    ->whereNull('transfer_id')
                    ->where('is_adjustment', false)
                    ->whereBetween('date', [$mStart, $mEnd])
                    ->sum('amount');

                $fee = (int) Transfer::where('user_id', $user->id)
                    ->whereBetween('date', [$mStart, $mEnd])
                    ->sum('admin_fee');

                $monthLabel = Carbon::createFromDate((int) ($year ?: now()->year), $m, 1)->translatedFormat('F');

                $breakdown[] = [
                    'label' => $monthLabel,
                    'income' => $inc,
                    'expense' => $exp,
                    'transfer_fee' => $fee,
                    'net' => $inc - $exp - $fee,
                ];
            }
        } else {
            // Breakdown harian dalam bulan
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            $current = $start->copy();
            while ($current->lte($end)) {
                $dayStr = $current->toDateString();

                $inc = (int) Transaction::where('user_id', $user->id)
                    ->where('type', 'income')
                    ->whereNull('transfer_id')
                    ->where('is_adjustment', false)
                    ->whereDate('date', $dayStr)
                    ->sum('amount');

                $exp = (int) Transaction::where('user_id', $user->id)
                    ->where('type', 'expense')
                    ->whereNull('transfer_id')
                    ->where('is_adjustment', false)
                    ->whereDate('date', $dayStr)
                    ->sum('amount');

                $fee = (int) Transfer::where('user_id', $user->id)
                    ->whereDate('date', $dayStr)
                    ->sum('admin_fee');

                if ($inc > 0 || $exp > 0 || $fee > 0) {
                    $breakdown[] = [
                        'label' => $current->translatedFormat('d M Y'),
                        'income' => $inc,
                        'expense' => $exp,
                        'transfer_fee' => $fee,
                        'net' => $inc - $exp - $fee,
                    ];
                }

                $current->addDay();
            }
        }

        return [
            'period_type' => $periodType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'total_transfer_fee' => $totalTransferFee,
            'net_cashflow' => $netCashflow,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Laporan per kategori (FR-RPT-02).
     */
    public function byCategory(
        User $user,
        string $periodType,
        ?string $year = null,
        ?string $month = null,
        string $type = 'expense'
    ): Collection {
        [$startDate, $endDate] = $this->getDateRange($periodType, $year, $month);

        $transactions = Transaction::where('user_id', $user->id)
            ->where('type', $type)
            ->whereNull('transfer_id')
            ->where('is_adjustment', false)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['category'])
            ->get();

        $totalPeriod = $transactions->sum('amount');

        return $transactions->groupBy('category_id')->map(function ($group) use ($totalPeriod) {
            $cat = $group->first()->category;
            $sum = $group->sum('amount');
            $pct = $totalPeriod > 0 ? round(($sum / $totalPeriod) * 100, 1) : 0;

            return [
                'category_id' => $cat?->id,
                'name' => $cat?->name ?? 'Tanpa Kategori',
                'color' => $cat?->color ?? '#6B7280',
                'icon' => $cat?->icon ?? 'tag',
                'amount' => $sum,
                'count' => $group->count(),
                'percent' => $pct,
            ];
        })->sortByDesc('amount')->values();
    }

    /**
     * Laporan per sumber saldo (FR-RPT-02).
     */
    public function bySource(User $user, string $periodType, ?string $year = null, ?string $month = null): Collection
    {
        [$startDate, $endDate] = $this->getDateRange($periodType, $year, $month);

        $sources = AccountSource::where('user_id', $user->id)->get();

        return $sources->map(function ($src) use ($startDate, $endDate) {
            $income = (int) Transaction::where('account_source_id', $src->id)
                ->where('type', 'income')
                ->whereNull('transfer_id')
                ->where('is_adjustment', false)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

            $expense = (int) Transaction::where('account_source_id', $src->id)
                ->where('type', 'expense')
                ->whereNull('transfer_id')
                ->where('is_adjustment', false)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

            $transfersIn = (int) Transfer::where('to_source_id', $src->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

            $transfersOut = (int) Transfer::where('from_source_id', $src->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

            $transferFee = (int) Transfer::where('from_source_id', $src->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('admin_fee');

            $adjustments = (int) Transaction::where('account_source_id', $src->id)
                ->where('is_adjustment', true)
                ->whereBetween('date', [$startDate, $endDate])
                ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as total")
                ->value('total') ?? 0;

            $net = $income - $expense + $transfersIn - $transfersOut - $transferFee + $adjustments;

            return [
                'id' => $src->id,
                'name' => $src->name,
                'type' => $src->type->label(),
                'color' => $src->color,
                'income' => $income,
                'expense' => $expense,
                'transfers_in' => $transfersIn,
                'transfers_out' => $transfersOut,
                'transfer_fee' => $transferFee,
                'adjustments' => $adjustments,
                'net_change' => $net,
                'current_balance' => $src->current_balance,
            ];
        });
    }

    /**
     * Laporan Pajak dan Biaya Admin Transfer (FR-RPT-03, DESIGN 5.11).
     */
    public function tax(User $user, string $periodType, ?string $year = null, ?string $month = null): array
    {
        [$startDate, $endDate] = $this->getDateRange($periodType, $year, $month);

        // Biaya / pajak transfer
        $transfers = Transfer::where('user_id', $user->id)
            ->where('admin_fee', '>', 0)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['fromSource', 'toSource'])
            ->orderBy('date', 'desc')
            ->get();

        $transferFeeTotal = (int) $transfers->sum('admin_fee');

        $items = [];
        foreach ($transfers as $trf) {
            $items[] = [
                'date' => $trf->date->translatedFormat('d M Y'),
                'type' => 'Biaya Transfer',
                'description' => "Transfer {$trf->fromSource?->name} → {$trf->toSource?->name}".($trf->notes ? " ({$trf->notes})" : ''),
                'amount' => $trf->admin_fee,
            ];
        }

        $totalTax = $transferFeeTotal;

        return [
            'total_tax' => $totalTax,
            'total_transfer_tax' => $transferFeeTotal,
            'total_expense_tax' => 0,
            'items' => $items,
        ];
    }
}
