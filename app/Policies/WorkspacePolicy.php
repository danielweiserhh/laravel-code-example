<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Workspace $workspace): bool
    {
        return $workspace->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Workspace $workspace): bool
    {
        return $workspace->members()
            ->where('users.id', $user->id)
            ->wherePivotIn('role', [WorkspaceRole::OWNER->value, WorkspaceRole::ADMIN->value])
            ->exists();
    }

    public function delete(User $user, Workspace $workspace): bool
    {
        return $workspace->members()
            ->where('users.id', $user->id)
            ->wherePivot('role', WorkspaceRole::OWNER->value)
            ->exists();
    }

    public function manageMembers(User $user, Workspace $workspace): bool
    {
        return $this->update($user, $workspace);
    }
}
