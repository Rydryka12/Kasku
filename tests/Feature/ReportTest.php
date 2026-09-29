<?php

namespace Tests\Feature;

use App\Enums\CategoryType;
use App\Enums\SourceType;
use App\Enums\TransactionType;
use App\Exports\ReportExport;
use App\Livewire\Reports\Index;
use App\Models\AccountSource;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private ReportService $reportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportService = app(ReportService::class);
    }

    public function test_user_can_access_reports_page_and_switch_tabs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('reports.index'))->assertOk();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertSee('Laporan Keuangan')
            ->call('setTab', 'category')
            ->assertSet('activeTab', 'category')
            ->call('setTab', 'source')
            ->assertSet('activeTab', 'source')
            ->call('setTab', 'tax')
            ->assertSet('activeTab', 'tax')
            ->call('setPeriodType', 'yearly')
            ->assertSet('periodType', 'yearly');
    }

    public function test_report_service_calculates_correct_summary_and_tax(): void
    {
        $user = User::factory()->create();

        $source1 = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 1000000,
            'current_balance' => 1000000,
        ]);

        $source2 = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'Gopay',
            'type' => SourceType::Ewallet,
            'initial_balance' => 100000,
            'current_balance' => 100000,
        ]);

        $catIncome = Category::create([
            'user_id' => $user->id,
            'name' => 'Gaji',
            'type' => CategoryType::Income,
        ]);

        $catExpense = Category::create([
            'user_id' => $user->id,
            'name' => 'Makan',
            'type' => CategoryType::Expense,
        ]);

        $today = now()->toDateString();
        $year = now()->format('Y');
        $month = now()->format('m');

        // Income 5.000.000
        Transaction::create([
            'user_id' => $user->id,
            'account_source_id' => $source1->id,
            'category_id' => $catIncome->id,
            'type' => TransactionType::Income,
            'amount' => 5000000,
            'date' => $today,
        ]);

        // Expense 500.000
        Transaction::create([
            'user_id' => $user->id,
            'account_source_id' => $source1->id,
            'category_id' => $catExpense->id,
            'type' => TransactionType::Expense,
            'amount' => 500000,
            'date' => $today,
        ]);

        // Transfer 200.000 with admin fee 2.500
        Transfer::create([
            'user_id' => $user->id,
            'from_source_id' => $source1->id,
            'to_source_id' => $source2->id,
            'amount' => 200000,
            'admin_fee' => 2500,
            'date' => $today,
        ]);

        $summary = $this->reportService->summary($user, 'monthly', $year, $month);

        $this->assertEquals(5000000, $summary['total_income']);
        $this->assertEquals(500000, $summary['total_expense']);
        $this->assertEquals(2500, $summary['total_transfer_fee']);
        // Net: 5.000.000 - 500.000 - 2.500 = 4.497.500
        $this->assertEquals(4497500, $summary['net_cashflow']);

        // Tax report
        $tax = $this->reportService->tax($user, 'monthly', $year, $month);
        $this->assertEquals(2500, $tax['total_tax']);
        $this->assertCount(1, $tax['items']);

        // By Category
        $byCat = $this->reportService->byCategory($user, 'monthly', $year, $month, 'expense');
        $this->assertCount(1, $byCat);
        $this->assertEquals(500000, $byCat[0]['amount']);
    }

    public function test_report_numbers_match_between_service_page_pdf_and_excel(): void
    {
        $user = User::factory()->create();

        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 1000000,
            'current_balance' => 1000000,
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Proyek Freelance',
            'type' => CategoryType::Income,
        ]);

        $today = now()->toDateString();
        $year = now()->format('Y');
        $month = now()->format('m');

        Transaction::create([
            'user_id' => $user->id,
            'account_source_id' => $source->id,
            'category_id' => $category->id,
            'type' => TransactionType::Income,
            'amount' => 3500000,
            'date' => $today,
        ]);

        // 1. ReportService output
        $summary = $this->reportService->summary($user, 'monthly', $year, $month);
        $expectedIncome = $summary['total_income']; // 3500000
        $expectedIncomeFormatted = rupiah($expectedIncome);

        // 2. Livewire Reports Page renders identical number
        Livewire::actingAs($user)
            ->test(Index::class, [
                'periodType' => 'monthly',
                'year' => $year,
                'month' => $month,
            ])
            ->assertSee($expectedIncomeFormatted);

        // 3. PDF View renders identical number
        $categories = $this->reportService->byCategory($user, 'monthly', $year, $month);
        $sources = $this->reportService->bySource($user, 'monthly', $year, $month);
        $tax = $this->reportService->tax($user, 'monthly', $year, $month);

        $pdfView = view('exports.report-pdf', [
            'user' => $user,
            'periodType' => 'monthly',
            'periodLabel' => Carbon::createFromDate((int) $year, (int) $month, 1)->translatedFormat('F Y'),
            'summary' => $summary,
            'categories' => $categories,
            'sources' => $sources,
            'tax' => $tax,
        ])->render();

        $this->assertStringContainsString($expectedIncomeFormatted, $pdfView);

        // 4. Excel View renders identical raw number
        $excelExport = new ReportExport($user, 'monthly', $year, $month, 'summary');
        $excelView = $excelExport->view()->render();
        $this->assertStringContainsString((string) $expectedIncome, $excelView);

        // 5. Test Download endpoints
        $responsePdf = $this->actingAs($user)->get(route('reports.export.pdf', [
            'periodType' => 'monthly',
            'year' => $year,
            'month' => $month,
        ]));
        $responsePdf->assertOk();
        $responsePdf->assertHeader('content-type', 'application/pdf');

        $responseExcel = $this->actingAs($user)->get(route('reports.export.excel', [
            'periodType' => 'monthly',
            'year' => $year,
            'month' => $month,
        ]));
        $responseExcel->assertOk();

        // 6. Test Transactions Excel Export
        $responseTxExcel = $this->actingAs($user)->get(route('transactions.export.excel'));
        $responseTxExcel->assertOk();
    }

    public function test_user_cannot_see_other_users_transactions_in_reports(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $sourceB = AccountSource::create([
            'user_id' => $userB->id,
            'name' => 'BCA User B',
            'type' => SourceType::Bank,
            'initial_balance' => 10000000,
            'current_balance' => 10000000,
        ]);

        $catB = Category::create([
            'user_id' => $userB->id,
            'name' => 'Gaji Sultan B',
            'type' => CategoryType::Income,
        ]);

        Transaction::create([
            'user_id' => $userB->id,
            'account_source_id' => $sourceB->id,
            'category_id' => $catB->id,
            'type' => TransactionType::Income,
            'amount' => 99999999,
            'date' => now()->toDateString(),
        ]);

        $summaryA = $this->reportService->summary($userA, 'monthly');
        $this->assertEquals(0, $summaryA['total_income']);

        Livewire::actingAs($userA)
            ->test(Index::class)
            ->assertDontSee('99.999.999')
            ->assertDontSee('Gaji Sultan B');
    }
}
