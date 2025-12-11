<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\BoardPrivacy;
use App\Enums\BoardRole;
use App\Models\Board;
use App\Models\User;

class BoardPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Board $board): bool
    {
        if ($board->privacy === BoardPrivacy::PUBLIC) {
            return true;
        }

        if ($board->privacy === BoardPrivacy::WORKSPACE) {
            return $board->workspace->members()->where('users.id', $user->id)->exists();
        }

        return $board->members()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Board $board): bool
    {
        return $board->members()
            ->where('users.id', $user->id)
            ->wherePivotIn('role', [BoardRole::OWNER->value, BoardRole::ADMIN->value])
            ->exists();
    }

    public function delete(User $user, Board $board): bool
    {
        return $board->members()
            ->where('users.id', $user->id)
            ->wherePivot('role', BoardRole::OWNER->value)
            ->exists();
    }

    public function manageMembers(User $user, Board $board): bool
    {
        return $this->update($user, $board);
    }
}
