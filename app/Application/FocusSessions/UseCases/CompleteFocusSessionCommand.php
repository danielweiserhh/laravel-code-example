<?php

declare(strict_types=1);

namespace App\Application\FocusSessions\UseCases;

final class CompleteFocusSessionCommand
{
    public function __construct(
        public readonly int $sessionId,
    ) {}
}
