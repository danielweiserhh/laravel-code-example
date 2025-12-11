<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Workspaces\Entities;

use App\Domain\Exceptions\InvariantViolationException;
use App\Domain\Workspaces\Entities\WorkspaceEntity;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class WorkspaceEntityTest extends TestCase
{
    public function test_creates_valid_entity(): void
    {
        $now = new DateTimeImmutable();
        $entity = new WorkspaceEntity(
            id: 1,
            name: 'Test Workspace',
            description: 'Description',
            slug: 'test-workspace',
            settings: ['theme' => 'dark'],
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertSame(1, $entity->id);
        $this->assertSame('Test Workspace', $entity->name);
        $this->assertSame('Description', $entity->description);
    }

    public function test_throws_when_id_is_not_positive(): void
    {
        $now = new DateTimeImmutable();

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Workspace ID must be positive');

        new WorkspaceEntity(
            id: 0,
            name: 'Test',
            description: null,
            slug: null,
            settings: null,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function test_throws_when_name_is_empty(): void
    {
        $now = new DateTimeImmutable();

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Workspace name cannot be empty');

        new WorkspaceEntity(
            id: 1,
            name: '',
            description: null,
            slug: null,
            settings: null,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function test_throws_when_name_is_whitespace_only(): void
    {
        $now = new DateTimeImmutable();

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Workspace name cannot be empty');

        new WorkspaceEntity(
            id: 1,
            name: '   ',
            description: null,
            slug: null,
            settings: null,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function test_with_name_creates_new_entity(): void
    {
        $now = new DateTimeImmutable();
        $original = new WorkspaceEntity(
            id: 1,
            name: 'Original',
            description: 'Desc',
            slug: 'slug',
            settings: null,
            createdAt: $now,
            updatedAt: $now,
        );

        $updated = $original->withName('New Name');

        $this->assertSame('New Name', $updated->name);
        $this->assertSame('Original', $original->name);
        $this->assertNotSame($original->updatedAt->getTimestamp(), $updated->updatedAt->getTimestamp());
    }

    public function test_with_description_creates_new_entity(): void
    {
        $now = new DateTimeImmutable();
        $original = new WorkspaceEntity(
            id: 1,
            name: 'Workspace',
            description: 'Old',
            slug: null,
            settings: null,
            createdAt: $now,
            updatedAt: $now,
        );

        $updated = $original->withDescription('New Description');

        $this->assertSame('New Description', $updated->description);
        $this->assertSame('Old', $original->description);
    }
}
