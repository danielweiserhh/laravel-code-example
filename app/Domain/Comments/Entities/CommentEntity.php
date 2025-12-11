<?php

declare(strict_types=1);

namespace App\Domain\Comments\Entities;

use App\Domain\Exceptions\InvariantViolationException;
use DateTimeImmutable;

final class CommentEntity
{
    public function __construct(
        public readonly int $id,
        public readonly int $cardId,
        public readonly int $userId,
        public readonly string $text,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->id < 0) {
            throw new InvariantViolationException(
                'comment.id.non_negative',
                'Comment ID must be non-negative',
                ['id' => $this->id]
            );
        }

        if ($this->cardId <= 0) {
            throw new InvariantViolationException(
                'comment.card_id.positive',
                'Comment card ID must be positive',
                ['card_id' => $this->cardId]
            );
        }

        if ($this->userId <= 0) {
            throw new InvariantViolationException(
                'comment.user_id.positive',
                'Comment user ID must be positive',
                ['user_id' => $this->userId]
            );
        }

        if (trim($this->text) === '') {
            throw new InvariantViolationException(
                'comment.text.not_empty',
                'Comment text cannot be empty',
                ['text' => $this->text]
            );
        }
    }

    public function withText(string $newText): self
    {
        return new self(
            id: $this->id,
            cardId: $this->cardId,
            userId: $this->userId,
            text: $newText,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }
}
