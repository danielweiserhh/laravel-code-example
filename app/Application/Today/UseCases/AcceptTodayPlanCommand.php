<?php

declare(strict_types=1);

namespace App\Application\Today\UseCases;

final class AcceptTodayPlanCommand
{
    
    public function __construct(
        public readonly int $userId,
        public readonly array $bigThree,
        public readonly ?string $noteForUser,
        public readonly ?string $jobId
    ) {}
}
