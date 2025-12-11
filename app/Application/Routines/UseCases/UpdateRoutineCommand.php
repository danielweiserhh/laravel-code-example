<?php

declare(strict_types=1);

namespace App\Application\Routines\UseCases;

readonly class UpdateRoutineCommand
{
    public function __construct(
        public int $routineId,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $type = null,
        
        public ?array $settings = null,
    ) {}
}
