<?php

declare(strict_types=1);

namespace App\Domain\Inbox\ValueObjects;

readonly class InboxItemData
{
    public function __construct(
        public int $id,
        public string $content,
        public bool $isProcessed
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            content: (string) $data['content'],
            isProcessed: (bool) ($data['is_processed'] ?? false)
        );
    }
}
