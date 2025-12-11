<?php

declare(strict_types=1);

namespace App\Application\Workspaces\UseCases;

readonly class UpdateWorkspaceCommand
{
    public function __construct(
        public int $workspaceId,
        public ?string $name = null,
        public ?string $description = null,
        
        public ?array $settings = null,
    ) {}
}
