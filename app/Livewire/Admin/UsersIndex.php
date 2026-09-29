<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class UsersIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            $this->dispatch('toast', ['message' => 'Anda tidak dapat menonaktifkan akun sendiri.', 'type' => 'error']);

            return;
        }

        // FR-ADM-06: Tidak dapat menonaktifkan admin terakhir
        if ($user->isAdmin() && $user->is_active) {
            $activeAdminCount = User::where('role', 'admin')->where('is_active', true)->count();
            if ($activeAdminCount <= 1) {
                $this->dispatch('toast', ['message' => 'Tidak dapat menonaktifkan administrator terakhir dalam sistem.', 'type' => 'error']);

                return;
            }
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $this->dispatch('toast', ['message' => "Pengguna {$user->name} berhasil {$status}", 'type' => 'info']);
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('livewire.admin.users-index', [
            'users' => $users,
        ]);
    }
}
