<?php

declare(strict_types=1);

namespace App\Domain\AI\ValueObjects;


readonly class AIJob
{
    public function __construct(
        public string $id,
        public int $userId,
        public ?int $workspaceId,
        public string $type,
        public array $payload,
        public string $status,
        public ?array $result,
        public ?string $errorMessage,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            userId: (int) $data['user_id'],
            workspaceId: isset($data['workspace_id']) ? (int) $data['workspace_id'] : null,
            type: (string) $data['type'],
            payload: $data['payload'] ?? [],
            status: (string) $data['status'],
            result: $data['result'] ?? null,
            errorMessage: $data['error_message'] ?? null,
            createdAt: (string) $data['created_at'],
            updatedAt: (string) $data['updated_at'],
        );
    }

    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'workspace_id' => $this->workspaceId,
            'type' => $this->type,
            'payload' => $this->payload,
            'status' => $this->status,
            'result' => $this->result,
            'error_message' => $this->errorMessage,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
