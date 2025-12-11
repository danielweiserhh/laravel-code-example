<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

final class DecomposeCardCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly int $cardId,
        public readonly string $language = 'ru',
    ) {}
}
