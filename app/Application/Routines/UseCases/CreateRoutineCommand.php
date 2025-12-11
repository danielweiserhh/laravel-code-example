<?php

declare(strict_types=1);

namespace App\Application\Routines\UseCases;

readonly class CreateRoutineCommand
{
    
    public function __construct(
        public int $userId,
        public string $name,
        public ?int $workspaceId = null,
        public ?string $description = null,
        public ?string $type = null,
        
        public ?array $settings = null,
        public ?array $steps = null,
    ) {}
}
