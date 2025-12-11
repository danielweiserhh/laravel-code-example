<?php

declare(strict_types=1);

namespace App\Application\Comments\UseCases;

final class UpdateCommentCommand
{
    public function __construct(
        public readonly int $commentId,
        public readonly string $content
    ) {}
}
