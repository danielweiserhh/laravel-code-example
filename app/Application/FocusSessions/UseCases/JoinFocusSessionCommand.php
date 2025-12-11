<?php

declare(strict_types=1);

namespace App\Application\FocusSessions\UseCases;

final class JoinFocusSessionCommand
{
    public function __construct(
        public readonly int $sessionId,
        public readonly int $userId,
    ) {}
}
