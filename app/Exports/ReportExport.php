<?php

namespace App\Exports;

use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportExport implements FromView, ShouldAutoSize
{
    public function __construct(
        protected User $user,
        protected string $periodType,
        protected ?string $year = null,
        protected ?string $month = null,
        protected string $activeTab = 'summary'
    ) {}

    public function view(): View
    {
        $reportService = app(ReportService::class);

        $summary = $reportService->summary($this->user, $this->periodType, $this->year, $this->month);
        $categories = $reportService->byCategory($this->user, $this->periodType, $this->year, $this->month);
        $sources = $reportService->bySource($this->user, $this->periodType, $this->year, $this->month);
        $tax = $reportService->tax($this->user, $this->periodType, $this->year, $this->month);

        $periodLabel = $this->periodType === 'yearly'
            ? ($this->year ?: now()->format('Y'))
            : Carbon::createFromDate((int) ($this->year ?: now()->year), (int) ($this->month ?: now()->month), 1)->translatedFormat('F Y');

        return view('exports.report-excel', [
            'user' => $this->user,
            'periodType' => $this->periodType,
            'periodLabel' => $periodLabel,
            'activeTab' => $this->activeTab,
            'summary' => $summary,
            'categories' => $categories,
            'sources' => $sources,
            'tax' => $tax,
        ]);
    }
}
