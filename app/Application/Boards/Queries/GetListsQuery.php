<?php

declare(strict_types=1);

namespace App\Application\Boards\Queries;

final class GetListsQuery
{
    public function __construct(
        public readonly ?int $boardId = null,
        public readonly bool $includeArchived = false
    ) {}
}
