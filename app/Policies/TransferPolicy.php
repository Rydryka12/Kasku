<?php

namespace App\Policies;

use App\Models\Transfer;
use App\Models\User;

class TransferPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isUser();
    }

    public function view(User $user, Transfer $transfer): bool
    {
        return $transfer->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isUser();
    }

    public function update(User $user, Transfer $transfer): bool
    {
        return $transfer->user_id === $user->id;
    }

    public function delete(User $user, Transfer $transfer): bool
    {
        return $transfer->user_id === $user->id;
    }
}
