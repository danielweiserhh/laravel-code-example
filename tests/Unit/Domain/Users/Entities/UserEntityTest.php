<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Users\Entities;

use App\Domain\Exceptions\InvariantViolationException;
use App\Domain\Users\Entities\UserEntity;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UserEntityTest extends TestCase
{
    public function test_creates_valid_entity(): void
    {
        $now = new DateTimeImmutable();
        $entity = new UserEntity(
            id: 1,
            name: 'Test User',
            email: 'test@example.com',
            password: 'hashed',
            googleId: null,
            avatar: null,
            phone: null,
            telegramUsername: null,
            aiModel: null,
            speechModel: null,
            emailVerifiedAt: null,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertSame(1, $entity->id);
        $this->assertSame('Test User', $entity->name);
        $this->assertSame('test@example.com', $entity->email);
    }

    public function test_throws_when_id_is_not_positive(): void
    {
        $now = new DateTimeImmutable();

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('User ID must be positive');

        new UserEntity(
            id: 0,
            name: 'Test',
            email: 'test@example.com',
            password: null,
            googleId: null,
            avatar: null,
            phone: null,
            telegramUsername: null,
            aiModel: null,
            speechModel: null,
            emailVerifiedAt: null,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function test_throws_when_name_is_empty(): void
    {
        $now = new DateTimeImmutable();

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('User name cannot be empty');

        new UserEntity(
            id: 1,
            name: '',
            email: 'test@example.com',
            password: null,
            googleId: null,
            avatar: null,
            phone: null,
            telegramUsername: null,
            aiModel: null,
            speechModel: null,
            emailVerifiedAt: null,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function test_throws_when_email_is_invalid(): void
    {
        $now = new DateTimeImmutable();

        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('User email must be valid');

        new UserEntity(
            id: 1,
            name: 'Test',
            email: 'invalid-email',
            password: null,
            googleId: null,
            avatar: null,
            phone: null,
            telegramUsername: null,
            aiModel: null,
            speechModel: null,
            emailVerifiedAt: null,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function test_accepts_valid_email(): void
    {
        $now = new DateTimeImmutable();
        $entity = new UserEntity(
            id: 1,
            name: 'Test',
            email: 'user+tag@example.co.uk',
            password: null,
            googleId: null,
            avatar: null,
            phone: null,
            telegramUsername: null,
            aiModel: null,
            speechModel: null,
            emailVerifiedAt: null,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertSame('user+tag@example.co.uk', $entity->email);
    }
}
