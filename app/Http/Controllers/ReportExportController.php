<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Models\ExportHistory;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportController extends Controller
{
    public function pdf(Request $request, ReportService $reportService)
    {
        $user = Auth::user();
        $periodType = $request->query('periodType', 'monthly');
        $year = $request->query('year', now()->format('Y'));
        $month = $request->query('month', now()->format('m'));

        $summary = $reportService->summary($user, $periodType, $year, $month);
        $categories = $reportService->byCategory($user, $periodType, $year, $month);
        $sources = $reportService->bySource($user, $periodType, $year, $month);
        $tax = $reportService->tax($user, $periodType, $year, $month);

        $periodLabel = $periodType === 'yearly'
            ? $year
            : Carbon::createFromDate((int) $year, (int) $month, 1)->translatedFormat('F Y');

        $pdf = Pdf::loadView('exports.report-pdf', [
            'user' => $user,
            'periodType' => $periodType,
            'periodLabel' => $periodLabel,
            'summary' => $summary,
            'categories' => $categories,
            'sources' => $sources,
            'tax' => $tax,
        ])->setPaper('a4', 'portrait');

        $filename = 'Laporan-Keuangan-'.str_replace(' ', '-', $periodLabel).'-'.now()->format('Ymd-His').'.pdf';
        $storagePath = "exports/{$user->id}/{$filename}";

        Storage::disk('local')->put($storagePath, $pdf->output());

        ExportHistory::create([
            'user_id' => $user->id,
            'type' => 'report_pdf',
            'filters' => [
                'period_type' => $periodType,
                'year' => $year,
                'month' => $month,
            ],
            'file_path' => $storagePath,
            'file_name' => $filename,
        ]);

        return $pdf->download($filename);
    }

    public function excel(Request $request)
    {
        $user = Auth::user();
        $periodType = $request->query('periodType', 'monthly');
        $year = $request->query('year', now()->format('Y'));
        $month = $request->query('month', now()->format('m'));
        $activeTab = $request->query('activeTab', 'summary');

        $periodLabel = $periodType === 'yearly'
            ? $year
            : Carbon::createFromDate((int) $year, (int) $month, 1)->translatedFormat('F Y');

        $filename = 'Laporan-Keuangan-'.str_replace(' ', '-', $periodLabel).'-'.now()->format('Ymd-His').'.xlsx';
        $storagePath = "exports/{$user->id}/{$filename}";

        $export = new ReportExport($user, $periodType, $year, $month, $activeTab);
        Excel::store($export, $storagePath, 'local');

        ExportHistory::create([
            'user_id' => $user->id,
            'type' => 'report_excel',
            'filters' => [
                'period_type' => $periodType,
                'year' => $year,
                'month' => $month,
                'active_tab' => $activeTab,
            ],
            'file_path' => $storagePath,
            'file_name' => $filename,
        ]);

        return Excel::download(
            new ReportExport($user, $periodType, $year, $month, $activeTab),
            $filename
        );
    }
}
