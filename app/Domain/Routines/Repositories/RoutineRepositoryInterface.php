<?php

declare(strict_types=1);

namespace App\Domain\Routines\Repositories;

use App\Domain\Routines\ValueObjects\Routine as DomainRoutine;
use App\Domain\Shared\ValueObjects\UserId;

interface RoutineRepositoryInterface
{
    public function find(int $id): ?DomainRoutine;
    
    public function findOrFail(int $id): DomainRoutine;
    
    public function findByUserId(UserId $userId): array;
    
    public function getAllForUser(int $userId): array;
    
    public function findActiveByUserId(UserId $userId): array;
    
    public function create(array $payload, UserId $userId): DomainRoutine;
    
    public function update(int $id, array $payload): DomainRoutine;
    
    public function delete(int $id): bool;
    
    public function addStep(int $routineId, array $stepData): DomainRoutine;
    
    public function updateSteps(int $routineId, array $steps): DomainRoutine;
    
    public function toggleActive(int $id, bool $isActive): DomainRoutine;
}
