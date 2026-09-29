<?php

namespace Tests\Feature;

use App\Enums\CategoryType;
use App\Enums\SourceType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\AccountSource;
use App\Models\Category;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $txService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->txService = app(TransactionService::class);
    }

    public function test_income_increases_account_balance(): void
    {
        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 100000,
            'current_balance' => 100000,
        ]);
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Gaji',
            'type' => CategoryType::Income,
        ]);

        $tx = $this->txService->create([
            'user_id' => $user->id,
            'account_source_id' => $source->id,
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => 500000,
            'date' => now()->toDateString(),
            'description' => 'Gaji Bulanan',
        ]);

        $this->assertDatabaseHas('transactions', ['id' => $tx->id]);
        $this->assertEquals(600000, $source->fresh()->current_balance);
    }

    public function test_expense_decreases_account_balance(): void
    {
        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'Gopay',
            'type' => SourceType::Ewallet,
            'initial_balance' => 100000,
            'current_balance' => 100000,
        ]);
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Makan',
            'type' => CategoryType::Expense,
        ]);

        $tx = $this->txService->create([
            'user_id' => $user->id,
            'account_source_id' => $source->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 35000,
            'date' => now()->toDateString(),
            'description' => 'Makan Siang',
        ]);

        $this->assertDatabaseHas('transactions', ['id' => $tx->id]);
        $this->assertEquals(65000, $source->fresh()->current_balance);
    }

    public function test_expense_throws_exception_when_balance_insufficient(): void
    {
        $this->expectException(InsufficientBalanceException::class);

        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'Cash',
            'type' => SourceType::Cash,
            'initial_balance' => 20000,
            'current_balance' => 20000,
        ]);
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Belanja',
            'type' => CategoryType::Expense,
        ]);

        $this->txService->create([
            'user_id' => $user->id,
            'account_source_id' => $source->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 50000,
            'date' => now()->toDateString(),
        ]);
    }

    public function test_update_transaction_rolls_back_old_and_applies_new_balance(): void
    {
        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 100000,
            'current_balance' => 100000,
        ]);
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Makan',
            'type' => CategoryType::Expense,
        ]);

        // Create initial expense of 30.000 -> balance becomes 70.000
        $tx = $this->txService->create([
            'user_id' => $user->id,
            'account_source_id' => $source->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 30000,
            'date' => now()->toDateString(),
        ]);
        $this->assertEquals(70000, $source->fresh()->current_balance);

        // Update amount to 50.000 (old 30.000 returned -> 100.000, then new 50.000 deducted -> 50.000)
        $this->txService->update($tx, [
            'amount' => 50000,
        ]);

        $this->assertEquals(50000, $source->fresh()->current_balance);
        $this->assertEquals(50000, $tx->fresh()->amount);
    }

    public function test_delete_expense_transaction_restores_balance(): void
    {
        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 100000,
            'current_balance' => 100000,
        ]);
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Makan',
            'type' => CategoryType::Expense,
        ]);

        $tx = $this->txService->create([
            'user_id' => $user->id,
            'account_source_id' => $source->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 40000,
            'date' => now()->toDateString(),
        ]);
        $this->assertEquals(60000, $source->fresh()->current_balance);

        // Delete expense -> balance should be restored to 100.000
        $this->txService->delete($tx);

        $this->assertEquals(100000, $source->fresh()->current_balance);
        $this->assertDatabaseMissing('transactions', ['id' => $tx->id]);
    }

    public function test_delete_income_fails_if_balance_would_become_negative(): void
    {
        $this->expectException(InsufficientBalanceException::class);

        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 0,
            'current_balance' => 0,
        ]);
        $catIncome = Category::create([
            'user_id' => $user->id,
            'name' => 'Gaji',
            'type' => CategoryType::Income,
        ]);
        $catExpense = Category::create([
            'user_id' => $user->id,
            'name' => 'Belanja',
            'type' => CategoryType::Expense,
        ]);

        // Income 100.000 -> balance 100.000
        $txIncome = $this->txService->create([
            'user_id' => $user->id,
            'account_source_id' => $source->id,
            'category_id' => $catIncome->id,
            'type' => 'income',
            'amount' => 100000,
            'date' => now()->toDateString(),
        ]);

        // Expense 80.000 -> balance 20.000
        $this->txService->create([
            'user_id' => $user->id,
            'account_source_id' => $source->id,
            'category_id' => $catExpense->id,
            'type' => 'expense',
            'amount' => 80000,
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(20000, $source->fresh()->current_balance);

        // Trying to delete income of 100.000 when current balance is only 20.000 -> must throw InsufficientBalanceException (K-03)
        $this->txService->delete($txIncome);
    }
}
