<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\Entities;

use App\Domain\Exceptions\InvariantViolationException;
use DateTimeImmutable;

final class WorkspaceEntity
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $slug,
        public readonly ?array $settings,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->id <= 0) {
            throw new InvariantViolationException(
                'workspace.id.positive',
                'Workspace ID must be positive',
                ['id' => $this->id]
            );
        }

        if (trim($this->name) === '') {
            throw new InvariantViolationException(
                'workspace.name.not_empty',
                'Workspace name cannot be empty',
                ['name' => $this->name]
            );
        }
    }

    public function withName(string $newName): self
    {
        return new self(
            id: $this->id,
            name: $newName,
            description: $this->description,
            slug: $this->slug,
            settings: $this->settings,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function withDescription(?string $newDescription): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            description: $newDescription,
            slug: $this->slug,
            settings: $this->settings,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }
}
