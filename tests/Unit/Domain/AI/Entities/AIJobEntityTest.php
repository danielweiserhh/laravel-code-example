<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\AI\Entities;

use App\Domain\AI\Entities\AIJobEntity;
use App\Domain\Exceptions\InvariantViolationException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AIJobEntityTest extends TestCase
{
    public function test_creates_valid_entity(): void
    {
        $entity = new AIJobEntity(
            id: 'job-123',
            userId: 1,
            workspaceId: 10,
            type: 'decompose',
            status: 'pending',
            payload: ['card_id' => 1],
            result: null,
            errorMessage: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $this->assertSame('job-123', $entity->id);
        $this->assertSame(1, $entity->userId);
        $this->assertSame('pending', $entity->status);
    }

    public function test_throws_when_id_is_empty(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('AI job ID cannot be empty');

        new AIJobEntity(
            id: '',
            userId: 1,
            workspaceId: null,
            type: 'decompose',
            status: 'pending',
            payload: [],
            result: null,
            errorMessage: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function test_throws_when_user_id_is_not_positive(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('AI job user ID must be positive');

        new AIJobEntity(
            id: 'job-123',
            userId: 0,
            workspaceId: null,
            type: 'decompose',
            status: 'pending',
            payload: [],
            result: null,
            errorMessage: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function test_throws_when_type_is_empty(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('AI job type cannot be empty');

        new AIJobEntity(
            id: 'job-123',
            userId: 1,
            workspaceId: null,
            type: '',
            status: 'pending',
            payload: [],
            result: null,
            errorMessage: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function test_throws_when_status_is_empty(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('AI job status cannot be empty');

        new AIJobEntity(
            id: 'job-123',
            userId: 1,
            workspaceId: null,
            type: 'decompose',
            status: '',
            payload: [],
            result: null,
            errorMessage: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function test_with_status_creates_new_entity_with_updated_status(): void
    {
        $original = new AIJobEntity(
            id: 'job-123',
            userId: 1,
            workspaceId: null,
            type: 'decompose',
            status: 'pending',
            payload: ['card_id' => 1],
            result: null,
            errorMessage: null,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $updated = $original->withStatus('completed', ['result' => 'success']);

        $this->assertSame('completed', $updated->status);
        $this->assertSame(['result' => 'success'], $updated->result);
        $this->assertSame('pending', $original->status);
        $this->assertNotSame($original->updatedAt->getTimestamp(), $updated->updatedAt->getTimestamp());
    }

    public function test_with_status_preserves_error_message_when_provided(): void
    {
        $original = new AIJobEntity(
            id: 'job-123',
            userId: 1,
            workspaceId: null,
            type: 'decompose',
            status: 'pending',
            payload: [],
            result: null,
            errorMessage: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $updated = $original->withStatus('failed', null, 'Processing error');

        $this->assertSame('failed', $updated->status);
        $this->assertSame('Processing error', $updated->errorMessage);
    }
}
