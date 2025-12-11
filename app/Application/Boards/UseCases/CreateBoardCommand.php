<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

readonly class CreateBoardCommand
{
    public function __construct(
        public int $workspaceId,
        public int $userId,
        public string $name,
        public ?string $description = null,
        public ?string $color = null,
        public ?string $privacy = null,
        public ?array $settings = null,
    ) {}
}
