<?php

declare(strict_types=1);

namespace App\Domain\Inbox\Repositories;

use App\Domain\Inbox\ValueObjects\InboxItem as DomainInboxItem;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use App\Domain\Shared\ValueObjects\UserId;

interface InboxItemRepositoryInterface
{
    public function find(int $id): ?DomainInboxItem;

    public function findOrFail(int $id): DomainInboxItem;

    public function save(DomainInboxItem $item): DomainInboxItem;

    public function getUnprocessedForUser(UserId $userId): array;

    public function getUnprocessedForUserPaginated(UserId $userId, int $perPage = 10): PaginatedResult;

    public function getUnprocessedForUserExcluding(UserId $userId, array $excludedIds = [], int $limit = 10): array;

    public function getAllForUserPaginated(UserId $userId, int $perPage = 10): PaginatedResult;

    public function create(array $payload, UserId $userId): DomainInboxItem;

    public function update(int $id, array $payload): DomainInboxItem;

    public function delete(int $id): bool;

    public function updateAISuggestions(int $id, array $suggestions): bool;

    public function updateConvertedToCardId(int $id, int $cardId): bool;
}
