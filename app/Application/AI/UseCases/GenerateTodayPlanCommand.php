<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

final class GenerateTodayPlanCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $workspaceId = null,
        public readonly string $language = 'ru',
    ) {}
}
