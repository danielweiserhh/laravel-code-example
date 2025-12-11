<?php

declare(strict_types=1);

namespace App\Domain\FocusSessions\ValueObjects;

readonly class FocusSession
{
    public function __construct(
        public int $id,
        public int $userId,
        public ?int $cardId,
        public int $durationMinutes,
        public ?string $startedAt,
        public ?string $endedAt,
        public string $status,
        public bool $isGroup,
        public ?string $videoLink,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            userId: (int) $data['user_id'],
            cardId: isset($data['card_id']) ? (int) $data['card_id'] : null,
            durationMinutes: (int) ($data['duration_minutes'] ?? 25),
            startedAt: $data['started_at'] ?? null,
            endedAt: $data['ended_at'] ?? null,
            status: (string) ($data['status'] ?? 'pending'),
            isGroup: (bool) ($data['is_group'] ?? false),
            videoLink: $data['video_link'] ?? null,
            createdAt: (string) $data['created_at'],
            updatedAt: (string) $data['updated_at'],
        );
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'card_id' => $this->cardId,
            'duration_minutes' => $this->durationMinutes,
            'started_at' => $this->startedAt,
            'ended_at' => $this->endedAt,
            'status' => $this->status,
            'is_group' => $this->isGroup,
            'video_link' => $this->videoLink,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }
    
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
    
    public function canBeStarted(): bool
    {
        return $this->isPending() || $this->isPaused();
    }
    
    public function canBePaused(): bool
    {
        return $this->isActive();
    }
    
    public function canBeCompleted(): bool
    {
        return $this->isActive() || $this->isPaused();
    }
}
