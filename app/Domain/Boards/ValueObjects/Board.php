<?php

declare(strict_types=1);

namespace App\Domain\Boards\ValueObjects;

readonly class Board
{
    public function __construct(
        public int $id,
        public int $workspaceId,
        public string $name,
        public ?string $description,
        public ?string $color,
        public string $privacy,
        public bool $isFavorite,
        
        public ?array $settings,
        public int $position,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            workspaceId: (int) $data['workspace_id'],
            name: (string) $data['name'],
            description: isset($data['description']) ? (string) $data['description'] : null,
            color: isset($data['color']) ? (string) $data['color'] : null,
            privacy: (string) ($data['privacy'] ?? 'private'),
            isFavorite: (bool) ($data['is_favorite'] ?? false),
            settings: $data['settings'] ?? null,
            position: (int) ($data['position'] ?? 0),
            createdAt: (string) $data['created_at'],
            updatedAt: (string) $data['updated_at'],
        );
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'workspace_id' => $this->workspaceId,
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'privacy' => $this->privacy,
            'is_favorite' => $this->isFavorite,
            'settings' => $this->settings,
            'position' => $this->position,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
