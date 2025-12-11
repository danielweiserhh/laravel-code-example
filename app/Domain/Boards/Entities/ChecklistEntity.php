<?php

declare(strict_types=1);

namespace App\Domain\Boards\Entities;

use App\Domain\Exceptions\InvariantViolationException;
use DateTimeImmutable;

final class ChecklistEntity
{
    public function __construct(
        public readonly int $id,
        public readonly int $cardId,
        public readonly string $title,
        public readonly int $position,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->id <= 0) {
            throw new InvariantViolationException(
                'checklist.id.positive',
                'Checklist ID must be positive',
                ['id' => $this->id]
            );
        }

        if ($this->cardId <= 0) {
            throw new InvariantViolationException(
                'checklist.card_id.positive',
                'Checklist card ID must be positive',
                ['card_id' => $this->cardId]
            );
        }

        if (trim($this->title) === '') {
            throw new InvariantViolationException(
                'checklist.title.not_empty',
                'Checklist title cannot be empty',
                ['title' => $this->title]
            );
        }

        if ($this->position < 0) {
            throw new InvariantViolationException(
                'checklist.position.non_negative',
                'Checklist position must be non-negative',
                ['position' => $this->position]
            );
        }
    }

    public function withPosition(int $newPosition): self
    {
        if ($newPosition < 0) {
            throw new InvariantViolationException(
                'checklist.position.non_negative',
                'Checklist position must be non-negative',
                ['position' => $newPosition]
            );
        }

        return new self(
            id: $this->id,
            cardId: $this->cardId,
            title: $this->title,
            position: $newPosition,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function withTitle(string $newTitle): self
    {
        if (trim($newTitle) === '') {
            throw new InvariantViolationException(
                'checklist.title.not_empty',
                'Checklist title cannot be empty',
                ['title' => $newTitle]
            );
        }

        return new self(
            id: $this->id,
            cardId: $this->cardId,
            title: $newTitle,
            position: $this->position,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }
}
