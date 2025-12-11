<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

final class CreateAIJobCommand
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly int $userId,
        public readonly ?int $workspaceId,
        public readonly string $type,
        public readonly array $payload
    ) {}
}
