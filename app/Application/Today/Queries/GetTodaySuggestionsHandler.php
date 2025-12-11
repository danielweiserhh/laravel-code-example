<?php

declare(strict_types=1);

namespace App\Application\Today\Queries;

use App\Domain\Inbox\Repositories\InboxItemRepositoryInterface;
use App\Domain\Shared\ValueObjects\UserId;

final class GetTodaySuggestionsHandler
{
    public function __construct(
        private readonly InboxItemRepositoryInterface $inboxItemRepository
    ) {}

    
    public function handle(GetTodaySuggestionsQuery $query): array
    {
        $userId = new UserId($query->userId);

        
        $inboxItems = $this->inboxItemRepository->getUnprocessedForUserExcluding(
            $userId,
            $query->excludedIds,
            10
        );

        return array_map(function ($item) {
            return [
                'id' => $item->id,
                'content' => $item->content,
                'created_at' => $item->createdAt,
            ];
        }, $inboxItems);
    }
}
