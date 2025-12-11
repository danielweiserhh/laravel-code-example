<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\InboxItem;
use App\Models\User;

class InboxItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, InboxItem $inboxItem): bool
    {
        return $inboxItem->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, InboxItem $inboxItem): bool
    {
        return $inboxItem->user_id === $user->id;
    }

    public function delete(User $user, InboxItem $inboxItem): bool
    {
        return $inboxItem->user_id === $user->id;
    }
}
