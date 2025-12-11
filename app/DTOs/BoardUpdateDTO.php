<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class BoardUpdateDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?string $color = null,
        public ?string $privacy = null,
        public ?bool $isFavorite = null,
        
        public ?array $settings = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: isset($data['name']) ? (string) $data['name'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            color: isset($data['color']) ? (string) $data['color'] : null,
            privacy: isset($data['privacy']) ? (string) $data['privacy'] : null,
            isFavorite: isset($data['is_favorite']) ? (bool) $data['is_favorite'] : null,
            settings: isset($data['settings']) && is_array($data['settings']) ? $data['settings'] : null,
        );
    }

    public function toArray(): array
    {
        $result = [];

        if ($this->name !== null) {
            $result['name'] = $this->name;
        }

        if ($this->description !== null) {
            $result['description'] = $this->description;
        }

        if ($this->color !== null) {
            $result['color'] = $this->color;
        }

        if ($this->privacy !== null) {
            $result['privacy'] = $this->privacy;
        }

        if ($this->isFavorite !== null) {
            $result['is_favorite'] = $this->isFavorite;
        }
        
        if ($this->settings !== null) {
            $result['settings'] = $this->settings;
        }

        return $result;
    }

    public function isEmpty(): bool
    {
        return $this->name === null
            && $this->description === null
            && $this->color === null
            && $this->privacy === null
            && $this->isFavorite === null
            && $this->settings === null;
    }
}
