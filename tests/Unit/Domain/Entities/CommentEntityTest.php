<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entities;

use App\Domain\Comments\Entities\CommentEntity;
use App\Domain\Exceptions\InvariantViolationException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CommentEntityTest extends TestCase
{
    public function test_cannot_create_comment_with_zero_id(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Comment ID must be non-negative');

        new CommentEntity(
            id: 0,
            cardId: 1,
            userId: 1,
            text: 'Test comment',
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function test_cannot_create_comment_with_negative_id(): void
    {
        $this->expectException(InvariantViolationException::class);

        new CommentEntity(
            id: -1,
            cardId: 1,
            userId: 1,
            text: 'Test comment',
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function test_cannot_create_comment_with_empty_text(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Comment text cannot be empty');

        new CommentEntity(
            id: 1,
            cardId: 1,
            userId: 1,
            text: '   ',
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function test_cannot_create_comment_with_zero_card_id(): void
    {
        $this->expectException(InvariantViolationException::class);

        new CommentEntity(
            id: 1,
            cardId: 0,
            userId: 1,
            text: 'Test comment',
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function test_cannot_create_comment_with_zero_user_id(): void
    {
        $this->expectException(InvariantViolationException::class);

        new CommentEntity(
            id: 1,
            cardId: 1,
            userId: 0,
            text: 'Test comment',
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function test_can_create_valid_comment(): void
    {
        $now = new DateTimeImmutable();
        $comment = new CommentEntity(
            id: 1,
            cardId: 1,
            userId: 1,
            text: 'Test comment',
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertEquals(1, $comment->id);
        $this->assertEquals(1, $comment->cardId);
        $this->assertEquals(1, $comment->userId);
        $this->assertEquals('Test comment', $comment->text);
    }

    public function test_with_text_creates_new_entity_with_updated_text(): void
    {
        $now = new DateTimeImmutable();
        $comment = new CommentEntity(
            id: 1,
            cardId: 1,
            userId: 1,
            text: 'Original text',
            createdAt: $now,
            updatedAt: $now,
        );

        $updated = $comment->withText('Updated text');

        $this->assertNotSame($comment, $updated);
        $this->assertEquals('Updated text', $updated->text);
        $this->assertEquals('Original text', $comment->text);
    }
}
