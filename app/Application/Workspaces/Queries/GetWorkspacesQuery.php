<?php

declare(strict_types=1);

namespace App\Application\Workspaces\Queries;

final class GetWorkspacesQuery
{
    public function __construct(
        public readonly int $userId,
    ) {}
}
