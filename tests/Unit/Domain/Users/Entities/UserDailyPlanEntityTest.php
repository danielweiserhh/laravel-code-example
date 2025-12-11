<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Users\Entities;

use App\Domain\Exceptions\InvariantViolationException;
use App\Domain\Users\Entities\UserDailyPlanEntity;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UserDailyPlanEntityTest extends TestCase
{
    public function test_creates_valid_entity(): void
    {
        $now = new DateTimeImmutable();
        $entity = new UserDailyPlanEntity(
            id: 1,
            userId: 10,
            planDate: '2024-01-15',
            bigThree: [['id' => 1, 'title' => 'Task']],
            noteForUser: 'Note',
            aiJobId: 'job-123',
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertSame(1, $entity->id);
        $this->assertSame(10, $entity->userId);
        $this->assertSame('2024-01-15', $entity->planDate);
    }

    public function test_throws_when_id_is_not_positive(): void
    {
        $now = new DateTimeImmutable();

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('UserDailyPlan ID must be positive');

        new UserDailyPlanEntity(
            id: 0,
            userId: 1,
            planDate: '2024-01-15',
            bigThree: null,
            noteForUser: null,
            aiJobId: null,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function test_throws_when_user_id_is_not_positive(): void
    {
        $now = new DateTimeImmutable();

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('UserDailyPlan user ID must be positive');

        new UserDailyPlanEntity(
            id: 1,
            userId: 0,
            planDate: '2024-01-15',
            bigThree: null,
            noteForUser: null,
            aiJobId: null,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function test_throws_when_plan_date_is_empty(): void
    {
        $now = new DateTimeImmutable();

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('UserDailyPlan plan date cannot be empty');

        new UserDailyPlanEntity(
            id: 1,
            userId: 1,
            planDate: '',
            bigThree: null,
            noteForUser: null,
            aiJobId: null,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function test_with_big_three_creates_new_entity(): void
    {
        $now = new DateTimeImmutable();
        $original = new UserDailyPlanEntity(
            id: 1,
            userId: 1,
            planDate: '2024-01-15',
            bigThree: null,
            noteForUser: null,
            aiJobId: null,
            createdAt: $now,
            updatedAt: $now,
        );

        $newBigThree = [['id' => 1, 'title' => 'Task']];
        $updated = $original->withBigThree($newBigThree);

        $this->assertSame($newBigThree, $updated->bigThree);
        $this->assertNull($original->bigThree);
        $this->assertNotSame($original->updatedAt->getTimestamp(), $updated->updatedAt->getTimestamp());
    }

    public function test_with_note_for_user_creates_new_entity(): void
    {
        $now = new DateTimeImmutable();
        $original = new UserDailyPlanEntity(
            id: 1,
            userId: 1,
            planDate: '2024-01-15',
            bigThree: null,
            noteForUser: null,
            aiJobId: null,
            createdAt: $now,
            updatedAt: $now,
        );

        $updated = $original->withNoteForUser('New note');

        $this->assertSame('New note', $updated->noteForUser);
        $this->assertNull($original->noteForUser);
    }
}
