<?php

declare(strict_types=1);

namespace App\Application\Comments\UseCases;

final class CreateCommentCommand
{
    public function __construct(
        public readonly int $cardId,
        public readonly int $userId,
        public readonly string $content
    ) {}
}
