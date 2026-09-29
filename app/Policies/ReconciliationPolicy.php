<?php

namespace App\Policies;

use App\Models\Reconciliation;
use App\Models\User;

class ReconciliationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isUser();
    }

    public function view(User $user, Reconciliation $reconciliation): bool
    {
        return $user->id === $reconciliation->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isUser();
    }

    public function delete(User $user, Reconciliation $reconciliation): bool
    {
        return $user->id === $reconciliation->user_id;
    }
}
