<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

final class CreateCardCommand
{
    public function __construct(
        public readonly int $listId,
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly ?int $position = null,
        public readonly ?int $assigneeId = null,
        public readonly ?array $checklist = null,
        public readonly ?string $startDate = null,
        public readonly ?string $dueDate = null,
        public readonly ?string $energyLevel = null,
        public readonly ?string $taskType = null,
        public readonly ?array $customFields = null
    ) {}
}
