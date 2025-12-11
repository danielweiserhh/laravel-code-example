<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

readonly class PaginatedResult
{
    public function __construct(
        public array $items,
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {
        if ($currentPage < 1) {
            throw new \InvalidArgumentException('Current page must be >= 1');
        }

        if ($perPage < 1) {
            throw new \InvalidArgumentException('Per page must be >= 1');
        }
        
        if ($total < 0) {
            throw new \InvalidArgumentException('Total must be >= 0');
        }
    }

    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}
