<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Card;
use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Comment $comment): bool
    {
        return $user->can('view', $comment->card);
    }

    public function create(User $user, ?Card $card = null): bool
    {
        if ($card) {
            return $user->can('view', $card);
        }

        return true;
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        
        return $comment->user_id === $user->id || $user->can('update', $comment->card);
    }
}
