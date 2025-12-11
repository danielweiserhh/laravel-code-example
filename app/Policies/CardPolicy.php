<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Card;
use App\Models\User;

class CardPolicy
{
    public function view(User $user, Card $card): bool
    {
        $card->loadMissing('board');
        
        $board = $card->board;
        
        if (! $board) {
            return false;
        }

        $board->loadMissing('members');

        return $board->members()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Card $card): bool
    {
        return $this->view($user, $card);
    }

    public function delete(User $user, Card $card): bool
    {
        return $this->update($user, $card);
    }
}
