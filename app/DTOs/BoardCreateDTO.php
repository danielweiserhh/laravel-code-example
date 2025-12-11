<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class BoardCreateDTO
{
    public function __construct(
        public int $workspaceId,
        public string $name,
        public ?string $description = null,
        public ?string $color = null,
        public ?string $privacy = null,
        
        public ?array $settings = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            workspaceId: (int) $data['workspace_id'],
            name: (string) $data['name'],
            description: isset($data['description']) ? (string) $data['description'] : null,
            color: isset($data['color']) ? (string) $data['color'] : null,
            privacy: isset($data['privacy']) ? (string) $data['privacy'] : null,
            settings: isset($data['settings']) && is_array($data['settings']) ? $data['settings'] : null,
        );
    }
    
    public function toArray(): array
    {
        $result = [
            'workspace_id' => $this->workspaceId,
            'name' => $this->name,
        ];

        if ($this->description !== null) {
            $result['description'] = $this->description;
        }
        if ($this->color !== null) {
            $result['color'] = $this->color;
        }
        if ($this->privacy !== null) {
            $result['privacy'] = $this->privacy;
        }
        if ($this->settings !== null) {
            $result['settings'] = $this->settings;
        }

        return $result;
    }
}
