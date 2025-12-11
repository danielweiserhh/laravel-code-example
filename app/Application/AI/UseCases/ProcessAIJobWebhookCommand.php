<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

final class ProcessAIJobWebhookCommand
{
    /**
     * @param array<string, mixed> $result
     */
    public function __construct(
        public readonly string $jobId,
        public readonly string $jobType,
        public readonly array $result,
        public readonly string $status,
        public readonly ?string $errorMessage = null
    ) {}
}
