<?php

namespace Tests\Feature;

use App\Enums\GoalStatus;
use App\Enums\SourceType;
use App\Exceptions\InsufficientBalanceException;
use App\Livewire\Savings\Index;
use App\Models\AccountSource;
use App\Models\SavingsGoal;
use App\Models\User;
use App\Services\SavingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SavingTest extends TestCase
{
    use RefreshDatabase;

    private SavingService $savingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->savingService = app(SavingService::class);
    }

    public function test_user_can_create_savings_goal(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('openCreateGoalModal')
            ->set('name', 'Beli Laptop')
            ->set('target_amount', 15000000)
            ->set('deadline', now()->addYear()->toDateString())
            ->call('saveGoal')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('savings_goals', [
            'user_id' => $user->id,
            'name' => 'Beli Laptop',
            'target_amount' => 15000000,
            'current_amount' => 0,
            'status' => GoalStatus::Active->value,
        ]);
    }

    public function test_deposit_deducts_source_and_adds_to_goal(): void
    {
        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 2000000,
            'current_balance' => 2000000,
        ]);
        $goal = SavingsGoal::create([
            'user_id' => $user->id,
            'name' => 'Dana Darurat',
            'target_amount' => 5000000,
            'current_amount' => 0,
            'status' => GoalStatus::Active,
        ]);

        $entry = $this->savingService->deposit(
            $goal,
            $source,
            1000000,
            now()->toDateString(),
            'Setoran bulan pertama'
        );

        $this->assertDatabaseHas('savings_entries', ['id' => $entry->id]);
        $this->assertEquals(1000000, $source->fresh()->current_balance);
        $this->assertEquals(1000000, $goal->fresh()->current_amount);
        $this->assertEquals(GoalStatus::Active, $goal->fresh()->status);
    }

    public function test_deposit_auto_achieves_goal_when_target_reached(): void
    {
        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 3000000,
            'current_balance' => 3000000,
        ]);
        $goal = SavingsGoal::create([
            'user_id' => $user->id,
            'name' => 'Kamera Baru',
            'target_amount' => 2000000,
            'current_amount' => 0,
            'status' => GoalStatus::Active,
        ]);

        $this->savingService->deposit($goal, $source, 2000000, now()->toDateString());

        $this->assertEquals(GoalStatus::Achieved, $goal->fresh()->status);
        $this->assertEquals(2000000, $goal->fresh()->current_amount);
    }

    public function test_deposit_fails_if_source_balance_insufficient(): void
    {
        $this->expectException(InsufficientBalanceException::class);

        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'Cash',
            'type' => SourceType::Cash,
            'initial_balance' => 100000,
            'current_balance' => 100000,
        ]);
        $goal = SavingsGoal::create([
            'user_id' => $user->id,
            'name' => 'Tabungan',
            'target_amount' => 1000000,
            'current_amount' => 0,
            'status' => GoalStatus::Active,
        ]);

        $this->savingService->deposit($goal, $source, 500000, now()->toDateString());
    }

    public function test_withdraw_adds_to_source_and_deducts_from_goal(): void
    {
        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 500000,
            'current_balance' => 500000,
        ]);
        $goal = SavingsGoal::create([
            'user_id' => $user->id,
            'name' => 'Liburan',
            'target_amount' => 3000000,
            'current_amount' => 1500000,
            'status' => GoalStatus::Active,
        ]);

        $this->savingService->withdraw($goal, $source, 500000, now()->toDateString());

        $this->assertEquals(1000000, $source->fresh()->current_balance);
        $this->assertEquals(1000000, $goal->fresh()->current_amount);
    }

    public function test_withdraw_fails_if_exceeding_goal_amount(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 500000,
            'current_balance' => 500000,
        ]);
        $goal = SavingsGoal::create([
            'user_id' => $user->id,
            'name' => 'Liburan',
            'target_amount' => 1000000,
            'current_amount' => 200000,
            'status' => GoalStatus::Active,
        ]);

        $this->savingService->withdraw($goal, $source, 300000, now()->toDateString());
    }

    public function test_cancel_goal_with_funds_refunds_all_to_selected_source(): void
    {
        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 100000,
            'current_balance' => 100000,
        ]);
        $goal = SavingsGoal::create([
            'user_id' => $user->id,
            'name' => 'Target Batal',
            'target_amount' => 2000000,
            'current_amount' => 750000,
            'status' => GoalStatus::Active,
        ]);

        $this->savingService->cancel($goal, $source);

        // Entire 750.000 returned to BCA
        $this->assertEquals(850000, $source->fresh()->current_balance);
        $this->assertEquals(0, $goal->fresh()->current_amount);
        $this->assertEquals(GoalStatus::Cancelled, $goal->fresh()->status);
    }

    public function test_total_savings_only_counts_active_and_achieved(): void
    {
        $user = User::factory()->create();

        // Active goal: 500.000
        SavingsGoal::create([
            'user_id' => $user->id,
            'name' => 'Goal 1',
            'target_amount' => 1000000,
            'current_amount' => 500000,
            'status' => GoalStatus::Active,
        ]);

        // Achieved goal: 1.000.000
        SavingsGoal::create([
            'user_id' => $user->id,
            'name' => 'Goal 2',
            'target_amount' => 1000000,
            'current_amount' => 1000000,
            'status' => GoalStatus::Achieved,
        ]);

        // Cancelled goal: 0
        SavingsGoal::create([
            'user_id' => $user->id,
            'name' => 'Goal 3',
            'target_amount' => 500000,
            'current_amount' => 0,
            'status' => GoalStatus::Cancelled,
        ]);

        $totalSavings = $this->savingService->getTotalSavings($user);

        // 500.000 + 1.000.000 = 1.500.000
        $this->assertEquals(1500000, $totalSavings);
    }

    public function test_user_cannot_access_other_users_savings_goal(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $goalB = SavingsGoal::create([
            'user_id' => $userB->id,
            'name' => 'Rahasia B',
            'target_amount' => 10000000,
            'current_amount' => 5000000,
            'status' => GoalStatus::Active,
        ]);

        $this->assertFalse($userA->can('view', $goalB));
        $this->assertFalse($userA->can('update', $goalB));
        $this->assertFalse($userA->can('delete', $goalB));

        $response = $this->actingAs($userA)->get(route('savings.show', $goalB));
        $response->assertForbidden();
    }
}
