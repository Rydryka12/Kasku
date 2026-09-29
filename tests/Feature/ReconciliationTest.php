<?php

namespace Tests\Feature;

use App\Models\AccountSource;
use App\Models\User;
use App\Services\ReconciliationService;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected AccountSource $source;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->source = AccountSource::create([
            'user_id' => $this->user->id,
            'name' => 'Gopay',
            'type' => 'ewallet',
            'initial_balance' => 500_000,
            'current_balance' => 500_000,
            'color' => '#3B82F6',
        ]);
    }

    public function test_user_can_access_reconciliations_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('reconciliations.index'))
            ->assertOk()
            ->assertSee('Rekonsiliasi Saldo');
    }

    public function test_reconciliation_negative_difference_creates_expense_adjustment(): void
    {
        /** @var ReconciliationService $service */
        $service = app(ReconciliationService::class);

        // Saldo aplikasi 500.000, saldo asli 485.000 -> selisih -15.000 (aplikasi kelebihan)
        $reconciliation = $service->reconcile($this->source, 485_000, true, now()->toDateString());

        $this->assertEquals(-15_000, $reconciliation->difference);
        $this->assertTrue($reconciliation->adjusted);

        // Saldo sumber berkurang menjadi 485.000
        $this->source->refresh();
        $this->assertEquals(485_000, $this->source->current_balance);

        // Transaksi penyesuaian dibuat dengan is_adjustment = true, category_id = null
        $this->assertDatabaseHas('transactions', [
            'id' => $reconciliation->adjustment_transaction_id,
            'user_id' => $this->user->id,
            'account_source_id' => $this->source->id,
            'category_id' => null,
            'type' => 'expense',
            'amount' => 15_000,
            'is_adjustment' => true,
        ]);
    }

    public function test_reconciliation_positive_difference_creates_income_adjustment(): void
    {
        /** @var ReconciliationService $service */
        $service = app(ReconciliationService::class);

        // Saldo aplikasi 500.000, saldo asli 520.000 -> selisih +20.000 (aplikasi kekurangan)
        $reconciliation = $service->reconcile($this->source, 520_000, true, now()->toDateString());

        $this->assertEquals(20_000, $reconciliation->difference);
        $this->assertTrue($reconciliation->adjusted);

        $this->source->refresh();
        $this->assertEquals(520_000, $this->source->current_balance);

        $this->assertDatabaseHas('transactions', [
            'id' => $reconciliation->adjustment_transaction_id,
            'type' => 'income',
            'amount' => 20_000,
            'is_adjustment' => true,
        ]);
    }

    public function test_adjustment_transaction_excluded_from_standard_report_totals(): void
    {
        /** @var ReconciliationService $reconService */
        $reconService = app(ReconciliationService::class);

        // Buat penyesuaian -15.000
        $reconService->reconcile($this->source, 485_000, true, now()->toDateString());

        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);
        $summary = $reportService->summary($this->user, 'monthly', now()->format('Y'), now()->format('m'));

        // Pengeluaran standar di rekap laporan harus tetap 0 (K-04)
        $this->assertEquals(0, $summary['total_expense']);
        $this->assertEquals(0, $summary['total_income']);

        // Tapi di bySource ada kolom adjustments terpisah
        $bySource = $reportService->bySource($this->user, 'monthly', now()->format('Y'), now()->format('m'));
        $srcReport = $bySource->firstWhere('id', $this->source->id);

        $this->assertEquals(-15_000, $srcReport['adjustments']);
        $this->assertEquals(-15_000, $srcReport['net_change']);
    }
}
