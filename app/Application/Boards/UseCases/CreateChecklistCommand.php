<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

final class CreateChecklistCommand
{
    public function __construct(
        public readonly int $cardId,
        public readonly string $title,
        public readonly ?array $items = null
    ) {}
}
