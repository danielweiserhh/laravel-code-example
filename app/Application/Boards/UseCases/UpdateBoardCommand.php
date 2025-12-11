<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

readonly class UpdateBoardCommand
{
    public function __construct(
        public int $boardId,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $color = null,
        public ?string $privacy = null,
        public ?bool $isFavorite = null,
        
        public ?array $settings = null,
    ) {}
}
