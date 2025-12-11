<?php

declare(strict_types=1);

namespace App\Domain\AI\Entities;

use App\Domain\Exceptions\InvariantViolationException;
use DateTimeImmutable;

final class AIJobEntity
{
    public function __construct(
        public readonly string $id,
        public readonly int $userId,
        public readonly ?int $workspaceId,
        public readonly string $type,
        public readonly string $status,
        public readonly array $payload,
        public readonly ?array $result,
        public readonly ?string $errorMessage,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty(trim($this->id))) {
            throw new InvariantViolationException(
                'ai_job.id.not_empty',
                'AI job ID cannot be empty',
                ['id' => $this->id]
            );
        }

        if ($this->userId <= 0) {
            throw new InvariantViolationException(
                'ai_job.user_id.positive',
                'AI job user ID must be positive',
                ['user_id' => $this->userId]
            );
        }

        if (empty(trim($this->type))) {
            throw new InvariantViolationException(
                'ai_job.type.not_empty',
                'AI job type cannot be empty',
                ['type' => $this->type]
            );
        }

        if (empty(trim($this->status))) {
            throw new InvariantViolationException(
                'ai_job.status.not_empty',
                'AI job status cannot be empty',
                ['status' => $this->status]
            );
        }
    }

    public function withStatus(string $newStatus, ?array $result = null, ?string $errorMessage = null): self
    {
        return new self(
            id: $this->id,
            userId: $this->userId,
            workspaceId: $this->workspaceId,
            type: $this->type,
            status: $newStatus,
            payload: $this->payload,
            result: $result ?? $this->result,
            errorMessage: $errorMessage ?? $this->errorMessage,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }
}
