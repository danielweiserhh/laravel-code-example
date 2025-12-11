<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FocusSession;
use App\Models\User;

class FocusSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FocusSession $focusSession): bool
    {
        return $focusSession->user_id === $user->id
            || $focusSession->participants()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FocusSession $focusSession): bool
    {
        return $focusSession->user_id === $user->id;
    }

    public function delete(User $user, FocusSession $focusSession): bool
    {
        return $focusSession->user_id === $user->id;
    }
}
