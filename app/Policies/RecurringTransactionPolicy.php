<?php

namespace App\Policies;

use App\Models\RecurringTransaction;
use App\Models\User;

class RecurringTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isUser();
    }

    public function view(User $user, RecurringTransaction $recurring): bool
    {
        return $user->id === $recurring->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isUser();
    }

    public function update(User $user, RecurringTransaction $recurring): bool
    {
        return $user->id === $recurring->user_id;
    }

    public function delete(User $user, RecurringTransaction $recurring): bool
    {
        return $user->id === $recurring->user_id;
    }
}
