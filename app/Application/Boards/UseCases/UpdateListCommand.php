<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

readonly class UpdateListCommand
{
    public function __construct(
        public int $listId,
        public ?string $name = null,
        public ?int $position = null,
        public ?bool $isArchived = null,
    ) {}
}
