<?php

namespace Tests\Feature;

use App\Enums\SourceType;
use App\Livewire\Sources\Index;
use App\Models\AccountSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountSourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_account_source(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('openCreateModal')
            ->set('name', 'Dompet Utama')
            ->set('type', 'cash')
            ->set('initial_balance', 150000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('account_sources', [
            'user_id' => $user->id,
            'name' => 'Dompet Utama',
            'type' => 'cash',
            'initial_balance' => 150000,
            'current_balance' => 150000,
        ]);
    }

    public function test_archiving_blocked_when_balance_greater_than_zero(): void
    {
        $user = User::factory()->create();

        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'Bank Jago',
            'type' => SourceType::Bank,
            'initial_balance' => 50000,
            'current_balance' => 50000,
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('toggleArchive', $source->id);

        $source->refresh();
        // Should not be archived because balance is 50.000 > 0 (K-05)
        $this->assertNull($source->archived_at);
    }

    public function test_archiving_allowed_when_balance_is_zero(): void
    {
        $user = User::factory()->create();

        $source = AccountSource::create([
            'user_id' => $user->id,
            'name' => 'Bank Mandiri',
            'type' => SourceType::Bank,
            'initial_balance' => 0,
            'current_balance' => 0,
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('toggleArchive', $source->id);

        $source->refresh();
        $this->assertNotNull($source->archived_at);
    }

    public function test_user_cannot_access_other_users_account_source(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $sourceB = AccountSource::create([
            'user_id' => $userB->id,
            'name' => 'BCA B',
            'type' => SourceType::Bank,
            'initial_balance' => 100000,
            'current_balance' => 100000,
        ]);

        $this->assertFalse($userA->can('view', $sourceB));
        $this->assertFalse($userA->can('update', $sourceB));
        $this->assertFalse($userA->can('delete', $sourceB));

        // When navigating to /sources/{sourceB}
        $response = $this->actingAs($userA)->get(route('sources.show', $sourceB));
        $response->assertForbidden();
    }
}
