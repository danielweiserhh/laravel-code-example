<?php

declare(strict_types=1);

namespace App\Domain\Routines\ValueObjects;

readonly class RoutineStep
{
    public function __construct(
        public int $id,
        public int $routineId,
        public string $title,
        public ?string $description,
        public int $position,
        public ?int $durationMinutes,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            routineId: (int) $data['routine_id'],
            title: (string) $data['title'],
            description: $data['description'] ?? null,
            position: (int) ($data['position'] ?? 0),
            durationMinutes: isset($data['duration_minutes']) ? (int) $data['duration_minutes'] : null,
            createdAt: (string) $data['created_at'],
            updatedAt: (string) $data['updated_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'routine_id' => $this->routineId,
            'title' => $this->title,
            'description' => $this->description,
            'position' => $this->position,
            'duration_minutes' => $this->durationMinutes,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
