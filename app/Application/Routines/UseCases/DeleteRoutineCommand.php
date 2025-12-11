<?php

declare(strict_types=1);

namespace App\Application\Routines\UseCases;

readonly class DeleteRoutineCommand
{
    public function __construct(
        public int $routineId,
    ) {}
}
