<?php

namespace App\Notifications;

use App\Models\RecurringTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RecurringFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public RecurringTransaction $recurring,
        public string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'recurring_failed',
            'title' => 'Transaksi Berulang Gagal',
            'message' => "Transaksi berulang '{$this->recurring->description}' gagal dijalankan: {$this->reason}",
            'recurring_id' => $this->recurring->id,
            'url' => route('recurring.index'),
        ];
    }
}
