<?php

namespace Tests\Feature;

use App\Livewire\Budgets\Index;
use App\Models\AccountSource;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $category;

    protected AccountSource $source;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->source = AccountSource::create([
            'user_id' => $this->user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'initial_balance' => 10_000_000,
            'current_balance' => 10_000_000,
            'color' => '#10B981',
        ]);
        $this->category = Category::create([
            'user_id' => $this->user->id,
            'name' => 'Makan & Minum',
            'type' => 'expense',
            'color' => '#EF4444',
            'icon' => 'utensils',
        ]);
    }

    public function test_user_can_access_budgets_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('budgets.index'))
            ->assertOk()
            ->assertSee('Budget');
    }

    public function test_user_can_create_and_manage_budget(): void
    {
        $currentMonth = now()->format('Y-m');

        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('category_id', $this->category->id)
            ->set('limit_amount', 1_000_000)
            ->call('save')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('budgets', [
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'month' => $currentMonth,
            'limit_amount' => 1_000_000,
        ]);
    }

    public function test_budget_service_status_and_percentages(): void
    {
        $currentMonth = now()->format('Y-m');
        $budget = Budget::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'month' => $currentMonth,
            'limit_amount' => 1_000_000,
        ]);

        /** @var BudgetService $service */
        $service = app(BudgetService::class);

        // Usage 0 -> safe
        $usage = $service->usage($budget);
        $this->assertEquals(0, $usage['used']);
        $this->assertEquals('safe', $usage['status']);

        // Transaction 850.000 -> 85% -> warning
        Transaction::create([
            'user_id' => $this->user->id,
            'account_source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 850_000,
            'date' => now()->toDateString(),
        ]);

        $usage = $service->usage($budget);
        $this->assertEquals(850_000, $usage['used']);
        $this->assertEquals(85, $usage['percent']);
        $this->assertEquals('warning', $usage['status']);

        // Transaction 200.000 -> total 1.050.000 -> 105% -> exceeded
        Transaction::create([
            'user_id' => $this->user->id,
            'account_source_id' => $this->source->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 200_000,
            'date' => now()->toDateString(),
        ]);

        $usage = $service->usage($budget);
        $this->assertEquals(1_050_000, $usage['used']);
        $this->assertEquals(105, $usage['percent']);
        $this->assertEquals('exceeded', $usage['status']);
    }

    public function test_copy_from_previous_month(): void
    {
        $prevMonth = now()->subMonth()->format('Y-m');
        $currentMonth = now()->format('Y-m');

        Budget::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'month' => $prevMonth,
            'limit_amount' => 2_000_000,
        ]);

        /** @var BudgetService $service */
        $service = app(BudgetService::class);
        $copied = $service->copyFromPreviousMonth($this->user, $currentMonth);

        $this->assertEquals(1, $copied);
        $this->assertDatabaseHas('budgets', [
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'month' => $currentMonth,
            'limit_amount' => 2_000_000,
        ]);
    }
}
