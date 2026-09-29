<?php

namespace Tests\Feature;

use App\Models\AccountSource;
use App\Models\ExportHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected AccountSource $source;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->user = User::factory()->create();
        $this->source = AccountSource::create([
            'user_id' => $this->user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'initial_balance' => 1_000_000,
            'current_balance' => 1_000_000,
            'color' => '#10B981',
        ]);
    }

    public function test_user_can_access_exports_history_page(): void
    {
        $this->actingAs($this->user)
            ->get(route('exports.index'))
            ->assertOk()
            ->assertSee('Riwayat Export');
    }

    public function test_generating_pdf_report_creates_export_history(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.export.pdf'));

        $response->assertOk();

        $this->assertDatabaseHas('export_histories', [
            'user_id' => $this->user->id,
            'type' => 'report_pdf',
        ]);
    }

    public function test_user_can_download_own_export_file(): void
    {
        $storagePath = "exports/{$this->user->id}/test.pdf";
        Storage::disk('local')->put($storagePath, 'PDF CONTENT');

        $history = ExportHistory::create([
            'user_id' => $this->user->id,
            'type' => 'report_pdf',
            'file_path' => $storagePath,
            'file_name' => 'test.pdf',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('exports.download', $history));

        $response->assertOk();
    }

    public function test_user_cannot_download_other_user_export(): void
    {
        $otherUser = User::factory()->create();
        $storagePath = "exports/{$otherUser->id}/test.pdf";
        Storage::disk('local')->put($storagePath, 'PDF CONTENT');

        $history = ExportHistory::create([
            'user_id' => $otherUser->id,
            'type' => 'report_pdf',
            'file_path' => $storagePath,
            'file_name' => 'test.pdf',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('exports.download', $history));

        $response->assertForbidden();
    }
}
