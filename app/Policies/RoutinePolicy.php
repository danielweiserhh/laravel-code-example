<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Routine;
use App\Models\User;

class RoutinePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Routine $routine): bool
    {
        return $routine->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Routine $routine): bool
    {
        return $routine->user_id === $user->id;
    }

    public function delete(User $user, Routine $routine): bool
    {
        return $routine->user_id === $user->id;
    }
}
