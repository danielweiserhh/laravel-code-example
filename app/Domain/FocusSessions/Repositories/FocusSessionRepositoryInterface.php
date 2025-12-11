<?php

declare(strict_types=1);

namespace App\Domain\FocusSessions\Repositories;

use App\Domain\FocusSessions\ValueObjects\FocusSession as DomainFocusSession;
use App\Domain\Shared\ValueObjects\UserId;

interface FocusSessionRepositoryInterface
{    
    public function find(int $id): ?DomainFocusSession;
    
    public function findOrFail(int $id): DomainFocusSession;
    
    public function findByUserId(UserId $userId): array;
    
    public function findActiveByUserId(UserId $userId): array;
    
    public function create(array $payload, UserId $userId): DomainFocusSession;
    
    public function update(int $id, array $payload): DomainFocusSession;
    
    public function addParticipant(int $sessionId, int $userId): void;
    
    public function findByUserIdAndDate(UserId $userId, string $date): array;
    
    public function getTotalFocusTimeForDate(UserId $userId, string $date): int;
}
