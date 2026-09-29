<?php

namespace App\Policies;

use App\Models\AccountSource;
use App\Models\User;

class AccountSourcePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isUser();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AccountSource $accountSource): bool
    {
        return $accountSource->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isUser();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AccountSource $accountSource): bool
    {
        return $accountSource->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AccountSource $accountSource): bool
    {
        return $accountSource->user_id === $user->id;
    }
}
