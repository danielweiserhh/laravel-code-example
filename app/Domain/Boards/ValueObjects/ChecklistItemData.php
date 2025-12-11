<?php

declare(strict_types=1);

namespace App\Domain\Boards\ValueObjects;

readonly class ChecklistItemData
{
    public function __construct(
        public int $id,
        public bool $isCompleted
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            isCompleted: (bool) ($data['is_completed'] ?? false)
        );
    }
}
