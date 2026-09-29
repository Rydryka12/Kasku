<?php

namespace Tests\Feature;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Models\AccountSource;
use App\Models\User;
use App\Services\DebtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class DebtTest extends TestCase
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
            'name' => 'BCA',
            'type' => 'bank',
            'initial_balance' => 2_000_000,
            'current_balance' => 2_000_000,
            'color' => '#10B981',
        ]);
    }

    public function test_user_can_access_debts_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('debts.index'))
            ->assertOk()
            ->assertSee('Hutang &amp; Piutang', false);
    }

    public function test_creating_new_debt_does_not_change_balance(): void
    {
        /** @var DebtService $service */
        $service = app(DebtService::class);

        $debt = $service->create($this->user, [
            'type' => DebtType::Debt->value,
            'person' => 'Budi',
            'amount' => 1_000_000,
            'due_date' => now()->addDays(10)->toDateString(),
        ]);

        $this->source->refresh();
        $this->assertEquals(2_000_000, $this->source->current_balance); // K-11: Saldo tetap
        $this->assertEquals(DebtStatus::Unpaid, $debt->status);
        $this->assertEquals(1_000_000, $debt->remaining_amount);
    }

    public function test_paying_debt_installment_alters_balance_and_updates_status(): void
    {
        /** @var DebtService $service */
        $service = app(DebtService::class);

        $debt = $service->create($this->user, [
            'type' => DebtType::Debt->value,
            'person' => 'Andi',
            'amount' => 1_000_000,
        ]);

        // Cicilan pertama: 400.000 -> partial
        $service->pay($debt, $this->source, 400_000, now()->toDateString(), 'Cicilan 1');

        $this->source->refresh();
        $this->assertEquals(1_600_000, $this->source->current_balance);

        $debt->refresh();
        $this->assertEquals(400_000, $debt->paid_amount);
        $this->assertEquals(600_000, $debt->remaining_amount);
        $this->assertEquals(DebtStatus::Partial, $debt->status);

        // Cicilan kedua pelunasan: 600.000 -> paid
        $service->pay($debt, $this->source, 600_000, now()->toDateString(), 'Pelunasan');

        $this->source->refresh();
        $this->assertEquals(1_000_000, $this->source->current_balance);

        $debt->refresh();
        $this->assertEquals(1_000_000, $debt->paid_amount);
        $this->assertEquals(0, $debt->remaining_amount);
        $this->assertEquals(DebtStatus::Paid, $debt->status);
    }

    public function test_receiving_receivable_installment_increases_balance(): void
    {
        /** @var DebtService $service */
        $service = app(DebtService::class);

        $receivable = $service->create($this->user, [
            'type' => DebtType::Receivable->value,
            'person' => 'Citra',
            'amount' => 500_000,
        ]);

        // Menerima cicilan piutang -> saldo bertambah
        $service->pay($receivable, $this->source, 500_000, now()->toDateString());

        $this->source->refresh();
        $this->assertEquals(2_500_000, $this->source->current_balance);

        $receivable->refresh();
        $this->assertEquals(DebtStatus::Paid, $receivable->status);
    }

    public function test_payment_cannot_exceed_remaining_amount(): void
    {
        /** @var DebtService $service */
        $service = app(DebtService::class);

        $debt = $service->create($this->user, [
            'type' => DebtType::Debt->value,
            'person' => 'Dedi',
            'amount' => 500_000,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $service->pay($debt, $this->source, 600_000, now()->toDateString());
    }
}
