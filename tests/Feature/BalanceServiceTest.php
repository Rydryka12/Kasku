<?php

namespace Tests\Feature;

use App\Enums\CategoryType;
use App\Enums\SourceType;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\AccountSource;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private BalanceService $balanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->balanceService = app(BalanceService::class);
    }

    public function test_credit_increases_balance(): void
    {
        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 100000,
            'current_balance' => 100000,
        ]);

        $this->balanceService->credit($source, 50000);

        $this->assertEquals(150000, $source->fresh()->current_balance);
    }

    public function test_debit_decreases_balance(): void
    {
        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 100000,
            'current_balance' => 100000,
        ]);

        $this->balanceService->debit($source, 40000);

        $this->assertEquals(60000, $source->fresh()->current_balance);
    }

    public function test_debit_throws_exception_when_balance_insufficient(): void
    {
        $this->expectException(InsufficientBalanceException::class);

        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'Dompet Cash',
            'type' => SourceType::Cash,
            'initial_balance' => 20000,
            'current_balance' => 20000,
        ]);

        $this->balanceService->debit($source, 50000);
    }

    public function test_verify_returns_true_for_mathematically_consistent_balance(): void
    {
        $user = User::factory()->create();

        $source1 = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 500000,
            'current_balance' => 500000,
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

        // Income 200.000 to BCA
        Transaction::create([
            'user_id' => $user->id,
            'account_source_id' => $source1->id,
            'category_id' => $catIncome->id,
            'type' => TransactionType::Income,
            'amount' => 200000,
            'tax_amount' => 0,
            'date' => now()->toDateString(),
        ]);
        $this->balanceService->credit($source1, 200000);

        // Expense 50.000 from BCA
        Transaction::create([
            'user_id' => $user->id,
            'account_source_id' => $source1->id,
            'category_id' => $catExpense->id,
            'type' => TransactionType::Expense,
            'amount' => 50000,
            'date' => now()->toDateString(),
        ]);
        $this->balanceService->debit($source1, 50000);

        // Transfer 100.000 + admin fee 2.500 from BCA to Gopay
        Transfer::create([
            'user_id' => $user->id,
            'from_source_id' => $source1->id,
            'to_source_id' => $source2->id,
            'amount' => 100000,
            'admin_fee' => 2500,
            'date' => now()->toDateString(),
        ]);
        $this->balanceService->debit($source1, 102500);
        $this->balanceService->credit($source2, 100000);

        // BCA: 500.000 + 200.000 - 50.000 - 102.500 = 547.500
        $this->assertEquals(547500, $source1->fresh()->current_balance);
        // Gopay: 100.000 + 100.000 = 200.000
        $this->assertEquals(200000, $source2->fresh()->current_balance);

        $this->assertSame(0, $this->balanceService->verify($source1));
        $this->assertSame(0, $this->balanceService->verify($source2));
    }
}
