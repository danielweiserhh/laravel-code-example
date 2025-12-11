<?php

declare(strict_types=1);

namespace App\Application\Users\UseCases;

use App\Models\User;

final class LogoutUserCommand
{
    public function __construct(
        public readonly User $user,
        public readonly bool $deleteAllTokens = false,
    ) {}
}
