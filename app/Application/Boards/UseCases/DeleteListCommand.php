<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

readonly class DeleteListCommand
{
    public function __construct(
        public int $listId,
    ) {}
}
