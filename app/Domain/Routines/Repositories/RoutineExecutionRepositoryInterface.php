<?php

declare(strict_types=1);

namespace App\Domain\Routines\Repositories;

use App\Domain\Routines\ValueObjects\RoutineExecution as DomainRoutineExecution;

interface RoutineExecutionRepositoryInterface
{
    public function findByUserAndDate(int $userId, string $date): array;

    public function findByRoutineAndDate(int $routineId, int $userId, string $date): ?DomainRoutineExecution;

    public function createOrUpdate(array $payload): DomainRoutineExecution;

    public function deleteByRoutine(int $routineId): void;
}
