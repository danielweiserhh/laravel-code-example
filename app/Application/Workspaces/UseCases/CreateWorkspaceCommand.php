<?php

declare(strict_types=1);

namespace App\Application\Workspaces\UseCases;

readonly class CreateWorkspaceCommand
{
    public function __construct(
        public int $userId,
        public string $name,
        public ?string $description = null,
        
        public ?array $settings = null,
    ) {}
}
