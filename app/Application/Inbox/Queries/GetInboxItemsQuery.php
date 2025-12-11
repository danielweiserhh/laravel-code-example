<?php

declare(strict_types=1);

namespace App\Application\Inbox\Queries;

readonly class GetInboxItemsQuery
{
    public function __construct(
        public int $userId,
        public int $perPage = 10,
        public bool $unprocessedOnly = false,
    ) {}
}
