<?php

declare(strict_types=1);

namespace App\Application\FocusSessions\UseCases;

final class CreateFocusSessionCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $cardId = null,
        public readonly int $durationMinutes = 25,
        public readonly bool $isGroup = false,
        public readonly ?string $videoLink = null,
    ) {}
}
