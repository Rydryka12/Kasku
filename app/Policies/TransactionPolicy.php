<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isUser();
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $transaction->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isUser();
    }

    public function update(User $user, Transaction $transaction): bool
    {
        // Transaksi penyesuaian rekonsiliasi tidak boleh diubah (K-04, SRS 7)
        if ($transaction->is_adjustment) {
            return false;
        }

        // Mutasi hasil transfer tidak boleh diedit langsung dari form transaksi biasa (SRS 4.5.3)
        if ($transaction->transfer_id !== null) {
            return false;
        }

        return $transaction->user_id === $user->id;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        // Transaksi penyesuaian rekonsiliasi tidak boleh dihapus (K-04, SRS 7)
        if ($transaction->is_adjustment) {
            return false;
        }

        if ($transaction->transfer_id !== null) {
            return false;
        }

        return $transaction->user_id === $user->id;
    }
}
