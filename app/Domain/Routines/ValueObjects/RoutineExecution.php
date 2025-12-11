<?php

declare(strict_types=1);

namespace App\Domain\Routines\ValueObjects;

use App\Domain\Exceptions\InvariantViolationException;

readonly class RoutineExecution
{
    public function __construct(
        public int $id,
        public int $routineId,
        public int $userId,
        public string $date,
        public array $completedSteps,
        public bool $isCompleted,
        public ?string $startedAt,
        public ?string $completedAt,
        public string $createdAt,
        public string $updatedAt,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->routineId <= 0) {
            throw new InvariantViolationException(
                'routine_execution.routine_id.positive',
                'Routine execution routine ID must be positive',
                ['routine_id' => $this->routineId]
            );
        }

        if ($this->userId <= 0) {
            throw new InvariantViolationException(
                'routine_execution.user_id.positive',
                'Routine execution user ID must be positive',
                ['user_id' => $this->userId]
            );
        }

        if (empty($this->date)) {
            throw new InvariantViolationException(
                'routine_execution.date.not_empty',
                'Routine execution date cannot be empty',
                ['date' => $this->date]
            );
        }

        if (!is_array($this->completedSteps)) {
            throw new InvariantViolationException(
                'routine_execution.completed_steps.must_be_array',
                'Routine execution completed steps must be an array',
                ['completed_steps' => $this->completedSteps]
            );
        }
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['routine_id'])) {
            throw new InvariantViolationException(
                'routine_execution.data.missing_routine_id',
                'Routine execution data must contain routine_id',
                ['data' => array_keys($data)]
            );
        }

        if (!isset($data['user_id'])) {
            throw new InvariantViolationException(
                'routine_execution.data.missing_user_id',
                'Routine execution data must contain user_id',
                ['data' => array_keys($data)]
            );
        }

        if (!isset($data['date'])) {
            throw new InvariantViolationException(
                'routine_execution.data.missing_date',
                'Routine execution data must contain date',
                ['data' => array_keys($data)]
            );
        }

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : 0,
            routineId: (int) $data['routine_id'],
            userId: (int) $data['user_id'],
            date: (string) $data['date'],
            completedSteps: $data['completed_steps'] ?? [],
            isCompleted: isset($data['is_completed']) ? (bool) $data['is_completed'] : false,
            startedAt: isset($data['started_at']) && $data['started_at'] !== null ? (string) $data['started_at'] : null,
            completedAt: isset($data['completed_at']) && $data['completed_at'] !== null ? (string) $data['completed_at'] : null,
            createdAt: isset($data['created_at']) ? (string) $data['created_at'] : date('c'),
            updatedAt: isset($data['updated_at']) ? (string) $data['updated_at'] : date('c'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'routine_id' => $this->routineId,
            'user_id' => $this->userId,
            'date' => $this->date,
            'completed_steps' => $this->completedSteps,
            'is_completed' => $this->isCompleted,
            'started_at' => $this->startedAt,
            'completed_at' => $this->completedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
