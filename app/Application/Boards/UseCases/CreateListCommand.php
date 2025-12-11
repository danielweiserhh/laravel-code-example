<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

readonly class CreateListCommand
{
    public function __construct(
        public int $boardId,
        public string $name,
        public ?int $position = null,
    ) {}
}
