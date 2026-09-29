<?php

namespace App\Livewire\Exports;

use App\Models\ExportHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Riwayat Export - Kasku')]
class Index extends Component
{
    use WithPagination;

    public function delete(int $id): void
    {
        $history = ExportHistory::where('user_id', Auth::id())->findOrFail($id);

        if (Storage::disk('local')->exists($history->file_path)) {
            Storage::disk('local')->delete($history->file_path);
        }

        $history->delete();
        $this->dispatch('toast', ['message' => 'Riwayat export berhasil dihapus', 'type' => 'success']);
    }

    public function render()
    {
        $histories = ExportHistory::where('user_id', Auth::id())
            ->latest('id')
            ->paginate(15);

        return view('livewire.exports.index', [
            'histories' => $histories,
        ]);
    }
}
