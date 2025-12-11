<?php

declare(strict_types=1);

namespace App\Application\Inbox\Queries;

use App\Domain\Inbox\Repositories\InboxItemRepositoryInterface;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use App\Domain\Shared\ValueObjects\UserId;

final class GetInboxItemsHandler
{
    public function __construct(
        private readonly InboxItemRepositoryInterface $inboxRepository,
    ) {}

    public function handle(GetInboxItemsQuery $query): PaginatedResult
    {
        $userId = new UserId($query->userId);

        if ($query->unprocessedOnly) {
            return $this->inboxRepository->getUnprocessedForUserPaginated($userId, $query->perPage);
        }

        return $this->inboxRepository->getAllForUserPaginated($userId, $query->perPage);
    }
}
