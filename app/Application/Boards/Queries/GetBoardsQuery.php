<?php

declare(strict_types=1);

namespace App\Application\Boards\Queries;

final class GetBoardsQuery
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $workspaceId = null,
    ) {}
}
