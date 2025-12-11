<?php

declare(strict_types=1);

namespace App\Application\FocusSessions\Queries;

use App\Domain\FocusSessions\Repositories\FocusSessionRepositoryInterface;
use App\Domain\FocusSessions\ValueObjects\FocusSession as DomainFocusSession;
use App\Domain\Shared\ValueObjects\UserId;

final class GetFocusSessionsHandler
{
    public function __construct(
        private readonly FocusSessionRepositoryInterface $sessionRepository
    ) {}

    
    public function handle(GetFocusSessionsQuery $query): array
    {
        $userId = new UserId($query->userId);

        return $this->sessionRepository->findByUserId($userId);
    }
}
