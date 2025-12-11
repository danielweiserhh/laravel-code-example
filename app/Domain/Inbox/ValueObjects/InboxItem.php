<?php

declare(strict_types=1);

namespace App\Domain\Inbox\ValueObjects;

readonly class InboxItem
{
    public function __construct(
        public int $id,
        public int $userId,
        public ?int $workspaceId,
        public string $content,
        public string $source,
        public bool $isProcessed,
        public ?int $convertedToCardId,
        
        public ?array $aiSuggestions,
        public string $createdAt,
        public string $updatedAt,
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            userId: (int) $data['user_id'],
            workspaceId: isset($data['workspace_id']) ? (int) $data['workspace_id'] : null,
            content: (string) $data['content'],
            source: (string) ($data['source'] ?? 'manual'),
            isProcessed: (bool) ($data['is_processed'] ?? false),
            convertedToCardId: isset($data['converted_to_card_id']) ? (int) $data['converted_to_card_id'] : null,
            aiSuggestions: $data['ai_suggestions'] ?? null,
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
            'content' => $this->content,
            'source' => $this->source,
            'is_processed' => $this->isProcessed,
            'converted_to_card_id' => $this->convertedToCardId,
            'ai_suggestions' => $this->aiSuggestions,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
