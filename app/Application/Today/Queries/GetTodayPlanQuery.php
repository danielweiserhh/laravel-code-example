<?php

declare(strict_types=1);

namespace App\Application\Today\Queries;

final class GetTodayPlanQuery
{
    public function __construct(
        public readonly int $userId
    ) {}
}
