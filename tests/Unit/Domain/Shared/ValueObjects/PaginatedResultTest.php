<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Shared\ValueObjects;

use App\Domain\Shared\ValueObjects\PaginatedResult;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PaginatedResultTest extends TestCase
{
    public function test_creates_valid_paginated_result(): void
    {
        $result = new PaginatedResult(
            items: ['item1', 'item2'],
            currentPage: 1,
            perPage: 10,
            total: 2,
            lastPage: 1,
        );

        $this->assertCount(2, $result->items);
        $this->assertSame(1, $result->currentPage);
        $this->assertSame(10, $result->perPage);
        $this->assertSame(2, $result->total);
        $this->assertSame(1, $result->lastPage);
    }

    public function test_has_more_pages_returns_true_when_not_on_last_page(): void
    {
        $result = new PaginatedResult(
            items: ['item1'],
            currentPage: 1,
            perPage: 10,
            total: 25,
            lastPage: 3,
        );

        $this->assertTrue($result->hasMorePages());
    }

    public function test_has_more_pages_returns_false_when_on_last_page(): void
    {
        $result = new PaginatedResult(
            items: ['item1'],
            currentPage: 3,
            perPage: 10,
            total: 25,
            lastPage: 3,
        );

        $this->assertFalse($result->hasMorePages());
    }

    public function test_is_empty_returns_true_when_no_items(): void
    {
        $result = new PaginatedResult(
            items: [],
            currentPage: 1,
            perPage: 10,
            total: 0,
            lastPage: 1,
        );

        $this->assertTrue($result->isEmpty());
    }

    public function test_is_empty_returns_false_when_has_items(): void
    {
        $result = new PaginatedResult(
            items: ['item1'],
            currentPage: 1,
            perPage: 10,
            total: 1,
            lastPage: 1,
        );

        $this->assertFalse($result->isEmpty());
    }

    public function test_throws_when_current_page_is_less_than_one(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Current page must be >= 1');

        new PaginatedResult(
            items: [],
            currentPage: 0,
            perPage: 10,
            total: 0,
            lastPage: 1,
        );
    }

    public function test_throws_when_per_page_is_less_than_one(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Per page must be >= 1');

        new PaginatedResult(
            items: [],
            currentPage: 1,
            perPage: 0,
            total: 0,
            lastPage: 1,
        );
    }

    public function test_throws_when_total_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Total must be >= 0');

        new PaginatedResult(
            items: [],
            currentPage: 1,
            perPage: 10,
            total: -1,
            lastPage: 1,
        );
    }
}
