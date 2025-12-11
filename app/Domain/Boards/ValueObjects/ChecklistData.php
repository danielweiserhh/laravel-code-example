<?php

declare(strict_types=1);

namespace App\Domain\Boards\ValueObjects;

readonly class ChecklistData
{
    public function __construct(
        public int $id,
        public ChecklistItemDataCollection $items = new ChecklistItemDataCollection([])
    ) {}

    public static function fromArray(array $data): self
    {
        $items = new ChecklistItemDataCollection([]);
        
        if (isset($data['items']) && is_array($data['items'])) {
            $items = ChecklistItemDataCollection::fromArray($data['items']);
        }

        return new self(
            id: (int) $data['id'],
            items: $items
        );
    }
}
