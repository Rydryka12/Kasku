<?php

namespace Tests\Feature;

use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;
use App\Enums\TransactionType;
use App\Models\AccountSource;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RecurringService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RecurringTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected AccountSource $source;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->source = AccountSource::create([
            'user_id' => $this->user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'initial_balance' => 1_000_000,
            'current_balance' => 1_000_000,
            'color' => '#10B981',
        ]);
        $this->category = Category::create([
            'user_id' => $this->user->id,
            'name' => 'Langganan',
            'type' => 'expense',
            'color' => '#3B82F6',
            'icon' => 'tag',
        ]);
    }

    public function test_user_can_access_recurring_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('recurring.index'))
            ->assertOk()
            ->assertSee('Transaksi Berulang');
    }

    public function test_recurring_executes_and_deducts_balance(): void
    {
        $recurring = RecurringTransaction::create([
            'user_id' => $this->user->id,
            'account_source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'type' => TransactionType::Expense,
            'amount' => 100_000,
            'frequency' => RecurringFrequency::Monthly,
            'start_date' => now()->toDateString(),
            'next_run' => now()->toDateString(),
            'status' => RecurringStatus::Active,
        ]);

        /** @var RecurringService $service */
        $service = app(RecurringService::class);
        $logs = $service->runDue(now());

        $this->assertCount(1, $logs);
        $this->assertEquals('success', $logs[0]->status);

        $this->source->refresh();
        $this->assertEquals(900_000, $this->source->current_balance);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => 100_000,
            'recurring_id' => $recurring->id,
        ]);
    }

    public function test_idempotency_running_twice_does_not_duplicate_transaction(): void
    {
        $recurring = RecurringTransaction::create([
            'user_id' => $this->user->id,
            'account_source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'type' => TransactionType::Expense,
            'amount' => 100_000,
            'frequency' => RecurringFrequency::Daily,
            'start_date' => now()->toDateString(),
            'next_run' => now()->toDateString(),
            'status' => RecurringStatus::Active,
        ]);

        // Jalankan via command 2x
        Artisan::call('recurring:run', ['--date' => now()->toDateString()]);
        Artisan::call('recurring:run', ['--date' => now()->toDateString()]);

        // Pastikan transaksi hanya dibuat 1 kali
        $this->assertEquals(1, Transaction::where('recurring_id', $recurring->id)->count());

        $this->source->refresh();
        $this->assertEquals(900_000, $this->source->current_balance);
    }

    public function test_insufficient_balance_creates_failed_log_and_notification(): void
    {
        // Saldo awal 50.000, butuh 200.000
        $this->source->update(['current_balance' => 50_000]);

        $recurring = RecurringTransaction::create([
            'user_id' => $this->user->id,
            'account_source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'type' => TransactionType::Expense,
            'amount' => 200_000,
            'frequency' => RecurringFrequency::Monthly,
            'start_date' => now()->toDateString(),
            'next_run' => now()->toDateString(),
            'status' => RecurringStatus::Active,
        ]);

        /** @var RecurringService $service */
        $service = app(RecurringService::class);
        $logs = $service->runDue(now());

        $this->assertCount(1, $logs);
        $this->assertEquals('failed', $logs[0]->status);

        // Saldo tidak berkurang
        $this->source->refresh();
        $this->assertEquals(50_000, $this->source->current_balance);

        // Notifikasi dibuat
        $this->assertCount(1, $this->user->notifications);
        $notification = $this->user->notifications->first();
        $this->assertEquals('Transaksi Berulang Gagal', $notification->data['title']);
    }

    public function test_catches_up_missed_dates(): void
    {
        // Terlewat 3 hari yang lalu (harian)
        $threeDaysAgo = now()->subDays(3)->toDateString();

        $recurring = RecurringTransaction::create([
            'user_id' => $this->user->id,
            'account_source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'type' => TransactionType::Expense,
            'amount' => 50_000,
            'frequency' => RecurringFrequency::Daily,
            'start_date' => $threeDaysAgo,
            'next_run' => $threeDaysAgo,
            'status' => RecurringStatus::Active,
        ]);

        /** @var RecurringService $service */
        $service = app(RecurringService::class);
        $logs = $service->runDue(now());

        // Harus mengejar 4 run (3 hari lalu, 2 hari lalu, kemarin, hari ini)
        $this->assertCount(4, $logs);
        $this->assertEquals(4, Transaction::where('recurring_id', $recurring->id)->count());

        $this->source->refresh();
        $this->assertEquals(1_000_000 - (4 * 50_000), $this->source->current_balance);

        // Next run berikutnya harus besok
        $recurring->refresh();
        $this->assertEquals(now()->addDay()->toDateString(), $recurring->next_run->toDateString());
    }

    public function test_date_clamping_for_end_of_month(): void
    {
        /** @var RecurringService $service */
        $service = app(RecurringService::class);

        // Dimulai pada tanggal 31 Januari
        $recurring = new RecurringTransaction([
            'frequency' => RecurringFrequency::Monthly,
            'start_date' => '2026-01-31',
            'next_run' => '2026-01-31',
        ]);

        // Bulan Februari 2026 hanya punya 28 hari -> harus diklamp ke 28
        $febRun = $service->nextRunDate($recurring, Carbon::parse('2026-01-31'));
        $this->assertEquals('2026-02-28', $febRun->toDateString());

        // Dari 28 Februari maju ke Maret -> kembali ke 31
        $marRun = $service->nextRunDate($recurring, $febRun);
        $this->assertEquals('2026-03-31', $marRun->toDateString());

        // Dari 31 Maret maju ke April -> diklamp ke 30
        $aprRun = $service->nextRunDate($recurring, $marRun);
        $this->assertEquals('2026-04-30', $aprRun->toDateString());
    }
}
