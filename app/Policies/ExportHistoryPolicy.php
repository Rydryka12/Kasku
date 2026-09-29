<?php

namespace App\Policies;

use App\Models\ExportHistory;
use App\Models\User;

class ExportHistoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isUser();
    }

    public function view(User $user, ExportHistory $exportHistory): bool
    {
        return $user->id === $exportHistory->user_id;
    }

    public function delete(User $user, ExportHistory $exportHistory): bool
    {
        return $user->id === $exportHistory->user_id;
    }
}
