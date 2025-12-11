<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Comments\Collections;

use App\Domain\Comments\Collections\CommentCollection;
use App\Domain\Comments\Entities\CommentEntity;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CommentCollectionTest extends TestCase
{
    public function test_is_empty_returns_true_for_empty_collection(): void
    {
        $collection = new CommentCollection([]);

        $this->assertTrue($collection->isEmpty());
        $this->assertSame(0, $collection->count());
    }

    public function test_is_empty_returns_false_for_non_empty_collection(): void
    {
        $comment = new CommentEntity(
            id: 1,
            cardId: 10,
            userId: 1,
            text: 'Test comment',
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $collection = new CommentCollection([$comment]);

        $this->assertFalse($collection->isEmpty());
        $this->assertSame(1, $collection->count());
    }

    public function test_get_by_card_id_filters_comments_correctly(): void
    {
        $comment1 = new CommentEntity(
            id: 1,
            cardId: 10,
            userId: 1,
            text: 'Comment for card 10',
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $comment2 = new CommentEntity(
            id: 2,
            cardId: 20,
            userId: 1,
            text: 'Comment for card 20',
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $comment3 = new CommentEntity(
            id: 3,
            cardId: 10,
            userId: 2,
            text: 'Another comment for card 10',
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $collection = new CommentCollection([$comment1, $comment2, $comment3]);

        $filtered = $collection->getByCardId(10);

        $this->assertCount(2, $filtered);
        $this->assertSame(1, $filtered[0]->id);
        $this->assertSame(3, $filtered[1]->id);
    }

    public function test_get_by_card_id_returns_empty_array_when_no_matches(): void
    {
        $comment = new CommentEntity(
            id: 1,
            cardId: 10,
            userId: 1,
            text: 'Comment',
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $collection = new CommentCollection([$comment]);

        $filtered = $collection->getByCardId(999);

        $this->assertIsArray($filtered);
        $this->assertCount(0, $filtered);
    }

    public function test_to_array_returns_all_comments(): void
    {
        $comment1 = new CommentEntity(
            id: 1,
            cardId: 10,
            userId: 1,
            text: 'Comment 1',
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $comment2 = new CommentEntity(
            id: 2,
            cardId: 10,
            userId: 1,
            text: 'Comment 2',
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $collection = new CommentCollection([$comment1, $comment2]);

        $array = $collection->toArray();

        $this->assertCount(2, $array);
        $this->assertSame($comment1, $array[0]);
        $this->assertSame($comment2, $array[1]);
    }
}
