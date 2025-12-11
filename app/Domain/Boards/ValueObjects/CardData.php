<?php

declare(strict_types=1);

namespace App\Domain\Boards\ValueObjects;

use DateTimeImmutable;

class CardData
{
    public function __construct(
        public readonly int $id,
        public readonly ?DateTimeImmutable $dueDate,
        public readonly bool $isCompleted,
        public readonly ChecklistDataCollection $checklists
    ) {}

    public static function fromArray(array $data): self
    {
        $checklists = new ChecklistDataCollection([]);
        if (isset($data['checklists']) && is_array($data['checklists']) && ! empty($data['checklists'])) {
            $checklists = ChecklistDataCollection::fromArray($data['checklists']);
        }

        $dueDate = null;
        if (isset($data['due_date']) && $data['due_date'] !== null) {
            $dueDate = is_string($data['due_date'])
                ? new DateTimeImmutable($data['due_date'])
                : $data['due_date'];
        }

        return new self(
            id: (int) $data['id'],
            dueDate: $dueDate,
            isCompleted: (bool) ($data['is_completed'] ?? false),
            checklists: $checklists
        );
    }
}
