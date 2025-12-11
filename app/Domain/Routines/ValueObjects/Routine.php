<?php

declare(strict_types=1);

namespace App\Domain\Routines\ValueObjects;

readonly class Routine
{
    public function __construct(
        public int $id,
        public int $userId,
        public ?int $workspaceId,
        public string $name,
        public ?string $description,
        public string $type,
        public bool $isActive,
        public ?array $settings,
        public array $steps,
        public string $createdAt,
        public string $updatedAt,
    ) {}
    
    public static function fromArray(array $data): self
    {
        $steps = [];

        if (isset($data['steps']) && is_array($data['steps'])) {
            foreach ($data['steps'] as $step) {
                $steps[] = $step instanceof RoutineStep
                    ? $step
                    : RoutineStep::fromArray($step);
            }
        }

        return new self(
            id: (int) $data['id'],
            userId: (int) $data['user_id'],
            workspaceId: isset($data['workspace_id']) ? (int) $data['workspace_id'] : null,
            name: (string) $data['name'],
            description: $data['description'] ?? null,
            type: (string) ($data['type'] ?? 'custom'),
            isActive: (bool) ($data['is_active'] ?? true),
            settings: $data['settings'] ?? null,
            steps: $steps,
            createdAt: (string) $data['created_at'],
            updatedAt: (string) $data['updated_at'],
        );
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'workspace_id' => $this->workspaceId,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'is_active' => $this->isActive,
            'settings' => $this->settings,
            'steps' => array_map(fn (RoutineStep $s) => $s->toArray(), $this->steps),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
    
    public function isType(string $type): bool
    {
        return $this->type === $type;
    }
    
    public function hasSteps(): bool
    {
        return count($this->steps) > 0;
    }
    
    public function getTotalDurationMinutes(): int
    {
        $total = 0;
        
        foreach ($this->steps as $step) {
            $total += $step->durationMinutes ?? 0;
        }

        return $total;
    }
}
