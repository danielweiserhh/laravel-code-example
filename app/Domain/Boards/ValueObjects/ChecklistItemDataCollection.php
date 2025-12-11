<?php

declare(strict_types=1);

namespace App\Domain\Boards\ValueObjects;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

final class ChecklistItemDataCollection implements Countable, IteratorAggregate
{   
    public function __construct(
        private array $items = []
    ) {}
    
    public static function fromArray(array $data): self
    {
        $items = array_map(static fn (array $item): ChecklistItemData => ChecklistItemData::fromArray($item), $data);

        return new self($items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }
}
