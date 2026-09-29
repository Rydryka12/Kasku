<?php

namespace App\Livewire\Reports;

use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    #[Url]
    public string $periodType = 'monthly'; // 'monthly' or 'yearly'

    #[Url]
    public string $year = '';

    #[Url]
    public string $month = '';

    #[Url]
    public string $activeTab = 'summary'; // 'summary', 'category', 'source', 'tax'

    public function mount(): void
    {
        $this->year = $this->year ?: now()->format('Y');
        $this->month = $this->month ?: now()->format('m');
    }

    public function setPeriodType(string $type): void
    {
        $this->periodType = $type;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function prevPeriod(): void
    {
        if ($this->periodType === 'yearly') {
            $this->year = (string) (((int) $this->year) - 1);
        } else {
            $dt = Carbon::createFromDate((int) $this->year, (int) $this->month, 1)->subMonth();
            $this->year = $dt->format('Y');
            $this->month = $dt->format('m');
        }
    }

    public function nextPeriod(): void
    {
        if ($this->periodType === 'yearly') {
            $this->year = (string) (((int) $this->year) + 1);
        } else {
            $dt = Carbon::createFromDate((int) $this->year, (int) $this->month, 1)->addMonth();
            $this->year = $dt->format('Y');
            $this->month = $dt->format('m');
        }
    }

    public function getPeriodLabelProperty(): string
    {
        if ($this->periodType === 'yearly') {
            return $this->year;
        }

        return Carbon::createFromDate((int) $this->year, (int) $this->month, 1)->translatedFormat('F Y');
    }

    public function render(ReportService $reportService)
    {
        $user = Auth::user();

        $summary = $reportService->summary($user, $this->periodType, $this->year, $this->month);
        $categories = $reportService->byCategory($user, $this->periodType, $this->year, $this->month);
        $sources = $reportService->bySource($user, $this->periodType, $this->year, $this->month);
        $tax = $reportService->tax($user, $this->periodType, $this->year, $this->month);

        return view('livewire.reports.index', [
            'summary' => $summary,
            'categories' => $categories,
            'sources' => $sources,
            'tax' => $tax,
        ]);
    }
}
