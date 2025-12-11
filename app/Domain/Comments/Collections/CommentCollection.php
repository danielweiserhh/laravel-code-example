<?php

declare(strict_types=1);

namespace App\Domain\Comments\Collections;

use App\Domain\Comments\Entities\CommentEntity;

final class CommentCollection extends \ArrayObject
{
    /**
     * @param CommentEntity[] $comments
     */
    public function __construct(array $comments = [])
    {
        parent::__construct($comments);
    }

    /**
     * @return CommentEntity[]
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    public function count(): int
    {
        return parent::count();
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * @return CommentEntity[]
     */
    public function getByCardId(int $cardId): array
    {
        return array_filter(
            $this->toArray(),
            fn (CommentEntity $comment) => $comment->cardId === $cardId
        );
    }
}
