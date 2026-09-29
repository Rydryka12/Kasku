<?php

namespace Tests\Feature;

use App\Enums\SourceType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\AccountSource;
use App\Models\Transfer;
use App\Models\User;
use App\Services\TransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    private TransferService $transferService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transferService = app(TransferService::class);
    }

    public function test_transfer_debits_source_with_fee_and_credits_destination(): void
    {
        $user = User::factory()->create();
        $sourceA = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 200000,
            'current_balance' => 200000,
        ]);
        $sourceB = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'Gopay',
            'type' => SourceType::Ewallet,
            'initial_balance' => 50000,
            'current_balance' => 50000,
        ]);

        $transfer = $this->transferService->transfer([
            'user_id' => $user->id,
            'from_source_id' => $sourceA->id,
            'to_source_id' => $sourceB->id,
            'amount' => 100000,
            'admin_fee' => 2500,
            'date' => now()->toDateString(),
            'notes' => 'Topup Gopay',
        ]);

        $this->assertDatabaseHas('transfers', ['id' => $transfer->id]);
        // Source A: 200.000 - (100.000 + 2.500) = 97.500
        $this->assertEquals(97500, $sourceA->fresh()->current_balance);
        // Source B: 50.000 + 100.000 = 150.000
        $this->assertEquals(150000, $sourceB->fresh()->current_balance);
    }

    public function test_transfer_rejected_when_source_balance_is_insufficient(): void
    {
        $this->expectException(InsufficientBalanceException::class);

        $user = User::factory()->create();
        $sourceA = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 100000,
            'current_balance' => 100000,
        ]);
        $sourceB = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'Gopay',
            'type' => SourceType::Ewallet,
            'initial_balance' => 50000,
            'current_balance' => 50000,
        ]);

        // Attempting to transfer 100.000 + 2.500 fee = 102.500 when balance is 100.000
        $this->transferService->transfer([
            'user_id' => $user->id,
            'from_source_id' => $sourceA->id,
            'to_source_id' => $sourceB->id,
            'amount' => 100000,
            'admin_fee' => 2500,
            'date' => now()->toDateString(),
        ]);
    }

    public function test_transfer_to_same_source_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = User::factory()->create();
        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 100000,
            'current_balance' => 100000,
        ]);

        $this->transferService->transfer([
            'user_id' => $user->id,
            'from_source_id' => $source->id,
            'to_source_id' => $source->id,
            'amount' => 50000,
            'date' => now()->toDateString(),
        ]);
    }

    public function test_delete_transfer_restores_balances(): void
    {
        $user = User::factory()->create();
        $sourceA = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => SourceType::Bank,
            'initial_balance' => 200000,
            'current_balance' => 200000,
        ]);
        $sourceB = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'Gopay',
            'type' => SourceType::Ewallet,
            'initial_balance' => 50000,
            'current_balance' => 50000,
        ]);

        $transfer = $this->transferService->transfer([
            'user_id' => $user->id,
            'from_source_id' => $sourceA->id,
            'to_source_id' => $sourceB->id,
            'amount' => 100000,
            'admin_fee' => 2500,
            'date' => now()->toDateString(),
        ]);

        $this->assertEquals(97500, $sourceA->fresh()->current_balance);
        $this->assertEquals(150000, $sourceB->fresh()->current_balance);

        // Delete transfer -> rollback balances
        $this->transferService->delete($transfer);

        $this->assertEquals(200000, $sourceA->fresh()->current_balance);
        $this->assertEquals(50000, $sourceB->fresh()->current_balance);
        $this->assertDatabaseMissing('transfers', ['id' => $transfer->id]);
    }
}
