<?php

declare(strict_types=1);

namespace App\Application\FocusSessions\Queries;

final class GetFocusSessionsQuery
{
    public function __construct(
        public readonly int $userId,
    ) {}
}
