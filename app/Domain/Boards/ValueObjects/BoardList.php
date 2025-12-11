<?php

declare(strict_types=1);

namespace App\Domain\Boards\ValueObjects;

readonly class BoardList
{
    public function __construct(
        public int $id,
        public int $boardId,
        public string $name,
        public int $position,
        public bool $isArchived,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            boardId: (int) $data['board_id'],
            name: (string) $data['name'],
            position: (int) ($data['position'] ?? 0),
            isArchived: (bool) ($data['is_archived'] ?? false),
            createdAt: (string) $data['created_at'],
            updatedAt: (string) $data['updated_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'board_id' => $this->boardId,
            'name' => $this->name,
            'position' => $this->position,
            'is_archived' => $this->isArchived,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
