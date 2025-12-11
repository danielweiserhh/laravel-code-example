<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entities;

use App\Domain\Boards\Entities\ChecklistEntity;
use App\Domain\Exceptions\InvariantViolationException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ChecklistEntityTest extends TestCase
{
    public function test_cannot_create_checklist_with_negative_position(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Checklist position must be non-negative');

        new ChecklistEntity(
            id: 1,
            cardId: 1,
            title: 'Test checklist',
            position: -1,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function test_cannot_create_checklist_with_empty_title(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Checklist title cannot be empty');

        new ChecklistEntity(
            id: 1,
            cardId: 1,
            title: '   ',
            position: 0,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function test_cannot_create_checklist_with_zero_card_id(): void
    {
        $this->expectException(InvariantViolationException::class);

        new ChecklistEntity(
            id: 1,
            cardId: 0,
            title: 'Test checklist',
            position: 0,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function test_can_create_valid_checklist(): void
    {
        $now = new DateTimeImmutable();
        $checklist = new ChecklistEntity(
            id: 1,
            cardId: 1,
            title: 'Test checklist',
            position: 0,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertEquals(1, $checklist->id);
        $this->assertEquals(1, $checklist->cardId);
        $this->assertEquals('Test checklist', $checklist->title);
        $this->assertEquals(0, $checklist->position);
    }

    public function test_with_position_creates_new_entity(): void
    {
        $now = new DateTimeImmutable();
        $checklist = new ChecklistEntity(
            id: 1,
            cardId: 1,
            title: 'Test checklist',
            position: 0,
            createdAt: $now,
            updatedAt: $now,
        );

        $updated = $checklist->withPosition(5);

        $this->assertNotSame($checklist, $updated);
        $this->assertEquals(5, $updated->position);
        $this->assertEquals(0, $checklist->position);
    }

    public function test_cannot_set_negative_position(): void
    {
        $now = new DateTimeImmutable();
        $checklist = new ChecklistEntity(
            id: 1,
            cardId: 1,
            title: 'Test checklist',
            position: 0,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->expectException(InvariantViolationException::class);

        $checklist->withPosition(-1);
    }
}
