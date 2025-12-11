<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

final class MoveCardCommand
{
    public function __construct(
        public readonly int $cardId,
        public readonly int $toListId,
        public readonly ?int $toPosition = null
    ) {}
}
