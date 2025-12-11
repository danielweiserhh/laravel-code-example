<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

final class DeleteCardCommand
{
    public function __construct(
        public readonly int $cardId
    ) {}
}
