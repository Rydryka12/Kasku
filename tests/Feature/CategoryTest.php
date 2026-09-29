<?php

namespace Tests\Feature;

use App\Enums\CategoryType;
use App\Enums\SourceType;
use App\Enums\TransactionType;
use App\Livewire\Categories\Index;
use App\Models\AccountSource;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CategoryService;
use Database\Seeders\DefaultCategorySeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_categories_are_cloned_on_user_creation(): void
    {
        $this->seed(DefaultCategorySeeder::class);

        $defaultCount = Category::whereNull('user_id')->count();
        $this->assertGreaterThan(0, $defaultCount);

        $user = User::factory()->create();
        app(CategoryService::class)->copyDefaultCategoriesToUser($user);

        $userCategoriesCount = Category::where('user_id', $user->id)->count();
        $this->assertEquals($defaultCount, $userCategoriesCount);
    }

    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('openCreateModal')
            ->set('name', 'Pekerjaan Lepas')
            ->set('type', 'income')
            ->set('color', '#0B7A55')
            ->set('icon', 'briefcase')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Pekerjaan Lepas',
            'type' => 'income',
        ]);
    }

    public function test_category_with_transactions_is_soft_archived(): void
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
            'name' => 'Kopi Harian',
            'type' => CategoryType::Expense,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'account_source_id' => $source->id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense,
            'amount' => 25000,
            'date' => now()->toDateString(),
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('delete', $category->id);

        $category->refresh();
        $this->assertNotNull($category->archived_at);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_user_cannot_access_or_modify_other_users_category(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryB = Category::create([
            'user_id' => $userB->id,
            'name' => 'Kategori Rahasia B',
            'type' => CategoryType::Expense,
        ]);

        $this->assertFalse($userA->can('update', $categoryB));
        $this->assertFalse($userA->can('delete', $categoryB));

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($userA)
            ->test(Index::class)
            ->call('delete', $categoryB->id);
    }
}
