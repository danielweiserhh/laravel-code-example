<?php

declare(strict_types=1);

namespace App\Domain\Boards\ValueObjects;

use App\Domain\Exceptions\InvariantViolationException;

readonly class Card
{
    public function __construct(
        public int $id,
        public int $listId,
        public int $boardId,
        public string $title,
        public ?string $description,
        public int $position,
        public ?string $startDate,
        public ?string $dueDate,
        public ?string $energyLevel,
        public ?string $taskType,
        public bool $isCompleted,
        public ?string $completedAt,
        public ?int $coverAttachmentId,
        public ?array $customFields,
        public string $createdAt,
        public string $updatedAt,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->id <= 0) {
            throw new InvariantViolationException(
                'card.id.positive',
                'Card ID must be positive',
                ['id' => $this->id]
            );
        }

        if ($this->listId <= 0) {
            throw new InvariantViolationException(
                'card.list_id.positive',
                'Card list ID must be positive',
                ['list_id' => $this->listId]
            );
        }

        if ($this->boardId <= 0) {
            throw new InvariantViolationException(
                'card.board_id.positive',
                'Card board ID must be positive',
                ['board_id' => $this->boardId]
            );
        }

        if (trim($this->title) === '') {
            throw new InvariantViolationException(
                'card.title.not_empty',
                'Card title cannot be empty',
                ['title' => $this->title]
            );
        }

        if ($this->position < 0) {
            throw new InvariantViolationException(
                'card.position.non_negative',
                'Card position must be non-negative',
                ['position' => $this->position]
            );
        }
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['id'])) {
            throw new InvariantViolationException(
                'card.data.missing_id',
                'Card data must contain id',
                ['data' => array_keys($data)]
            );
        }

        if (!isset($data['list_id'])) {
            throw new InvariantViolationException(
                'card.data.missing_list_id',
                'Card data must contain list_id',
                ['data' => array_keys($data)]
            );
        }

        if (!isset($data['board_id'])) {
            throw new InvariantViolationException(
                'card.data.missing_board_id',
                'Card data must contain board_id',
                ['data' => array_keys($data)]
            );
        }

        if (!isset($data['title'])) {
            throw new InvariantViolationException(
                'card.data.missing_title',
                'Card data must contain title',
                ['data' => array_keys($data)]
            );
        }

        if (!isset($data['created_at'])) {
            throw new InvariantViolationException(
                'card.data.missing_created_at',
                'Card data must contain created_at',
                ['data' => array_keys($data)]
            );
        }

        if (!isset($data['updated_at'])) {
            throw new InvariantViolationException(
                'card.data.missing_updated_at',
                'Card data must contain updated_at',
                ['data' => array_keys($data)]
            );
        }

        return new self(
            id: (int) $data['id'],
            listId: (int) $data['list_id'],
            boardId: (int) $data['board_id'],
            title: (string) $data['title'],
            description: isset($data['description']) && $data['description'] !== null ? (string) $data['description'] : null,
            position: isset($data['position']) ? (int) $data['position'] : 0,
            startDate: isset($data['start_date']) && $data['start_date'] !== null ? (string) $data['start_date'] : null,
            dueDate: isset($data['due_date']) && $data['due_date'] !== null ? (string) $data['due_date'] : null,
            energyLevel: isset($data['energy_level']) && $data['energy_level'] !== null ? (string) $data['energy_level'] : null,
            taskType: isset($data['task_type']) && $data['task_type'] !== null ? (string) $data['task_type'] : null,
            isCompleted: isset($data['is_completed']) ? (bool) $data['is_completed'] : false,
            completedAt: isset($data['completed_at']) && $data['completed_at'] !== null ? (string) $data['completed_at'] : null,
            coverAttachmentId: isset($data['cover_attachment_id']) && $data['cover_attachment_id'] !== null ? (int) $data['cover_attachment_id'] : null,
            customFields: $data['custom_fields'] ?? null,
            createdAt: (string) $data['created_at'],
            updatedAt: (string) $data['updated_at'],
        );
    }

    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'list_id' => $this->listId,
            'board_id' => $this->boardId,
            'title' => $this->title,
            'description' => $this->description,
            'position' => $this->position,
            'start_date' => $this->startDate,
            'due_date' => $this->dueDate,
            'energy_level' => $this->energyLevel,
            'task_type' => $this->taskType,
            'is_completed' => $this->isCompleted,
            'completed_at' => $this->completedAt,
            'cover_attachment_id' => $this->coverAttachmentId,
            'custom_fields' => $this->customFields,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
