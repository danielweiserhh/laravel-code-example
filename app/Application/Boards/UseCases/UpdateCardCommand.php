<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

readonly class UpdateCardCommand
{
    public function __construct(
        public int $cardId,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $startDate = null,
        public ?string $dueDate = null,
        public ?int $energyLevel = null,
        public ?string $taskType = null,
        public ?bool $isCompleted = null,
        
        public ?array $assignees = null,
        
        public ?array $customFields = null,
    ) {}
}
