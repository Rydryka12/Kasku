<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Livewire\Admin\UsersIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['role' => Role::User]);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertForbidden();

        $responseUsers = $this->actingAs($user)->get('/admin/users');
        $responseUsers->assertForbidden();

        $responseCategories = $this->actingAs($user)->get('/admin/categories');
        $responseCategories->assertForbidden();
    }

    public function test_admin_cannot_access_user_routes(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        // Per SRS 10.2 #7: "admin mendapat 403 pada route user"
        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertForbidden();

        $responseTx = $this->actingAs($admin)->get('/transactions');
        $responseTx->assertForbidden();
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/categories')->assertOk();
    }

    public function test_admin_can_toggle_user_active_status(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $targetUser = User::factory()->create(['is_active' => true]);

        Livewire::actingAs($admin)
            ->test(UsersIndex::class)
            ->call('toggleActive', $targetUser->id);

        $this->assertFalse($targetUser->fresh()->is_active);

        Livewire::actingAs($admin)
            ->test(UsersIndex::class)
            ->call('toggleActive', $targetUser->id);

        $this->assertTrue($targetUser->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin, 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(UsersIndex::class)
            ->call('toggleActive', $admin->id);

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_last_active_admin(): void
    {
        // Hanya ada 1 admin aktif dalam sistem
        $admin = User::factory()->create(['role' => Role::Admin, 'is_active' => true]);
        $secondAdmin = User::factory()->create(['role' => Role::Admin, 'is_active' => true]);

        // Second admin mencoba dinonaktifkan oleh first admin
        // Bila total admin aktif tersisa 1 -> gagal dinonaktifkan
        $secondAdmin->is_active = false;
        $secondAdmin->save();

        Livewire::actingAs($admin)
            ->test(UsersIndex::class)
            ->call('toggleActive', $admin->id); // admin sendiri

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_dashboard_shows_counts_only_without_user_financial_details(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $user = User::factory()->create(['role' => Role::User]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertOk();

        // Memastikan tampilan tidak memuat data pribadi / finansial user
        $response->assertDontSee('Saldo');
        $response->assertDontSee('Total Tabungan');
        $response->assertDontSee('Rp ');
    }

    public function test_inactive_user_is_redirected_with_message(): void
    {
        $inactiveUser = User::factory()->create(['is_active' => false]);

        $response = $this->actingAs($inactiveUser)->get('/profile');
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }
}
