<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exceptions;

use App\Domain\Exceptions\EntityNotFoundException;
use PHPUnit\Framework\TestCase;

final class EntityNotFoundExceptionTest extends TestCase
{
    public function test_creates_exception_with_default_message(): void
    {
        $exception = new EntityNotFoundException('Card', 123);

        $this->assertSame('Card with ID 123 not found', $exception->getMessage());
        $this->assertSame('Card', $exception->getEntityType());
        $this->assertSame(123, $exception->getEntityId());
    }

    public function test_creates_exception_with_custom_message(): void
    {
        $exception = new EntityNotFoundException(
            'Board',
            456,
            'Доска не найдена'
        );

        $this->assertSame('Доска не найдена', $exception->getMessage());
        $this->assertSame('Board', $exception->getEntityType());
        $this->assertSame(456, $exception->getEntityId());
    }

    public function test_creates_exception_with_string_id(): void
    {
        $exception = new EntityNotFoundException('AIJob', 'job-123');

        $this->assertSame('AIJob with ID job-123 not found', $exception->getMessage());
        $this->assertSame('AIJob', $exception->getEntityType());
        $this->assertSame('job-123', $exception->getEntityId());
    }

    public function test_get_context_returns_entity_info(): void
    {
        $exception = new EntityNotFoundException('User', 789);

        $context = $exception->getContext();

        $this->assertArrayHasKey('entity_type', $context);
        $this->assertArrayHasKey('entity_id', $context);
        $this->assertSame('User', $context['entity_type']);
        $this->assertSame(789, $context['entity_id']);
    }
}
