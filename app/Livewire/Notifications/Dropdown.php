<?php

namespace App\Livewire\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dropdown extends Component
{
    public function markAsRead(string $id): void
    {
        $notification = Auth::user()?->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
            $url = $notification->data['url'] ?? null;
            if ($url) {
                $this->redirect($url, navigate: true);
            }
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()?->unreadNotifications->markAsRead();
        $this->dispatch('toast', ['message' => 'Semua notifikasi ditandai telah dibaca', 'type' => 'success']);
    }

    public function render()
    {
        $user = Auth::user();
        $unreadCount = $user?->unreadNotifications()->count() ?? 0;
        $notifications = $user?->notifications()->limit(15)->get() ?? collect();

        return view('livewire.notifications.dropdown', [
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }
}
