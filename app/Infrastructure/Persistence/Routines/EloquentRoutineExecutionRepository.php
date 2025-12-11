<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Routines;

use App\Domain\Routines\Repositories\RoutineExecutionRepositoryInterface;
use App\Domain\Routines\ValueObjects\RoutineExecution as DomainRoutineExecution;
use App\Models\RoutineExecution;
use Carbon\Carbon;

final class EloquentRoutineExecutionRepository implements RoutineExecutionRepositoryInterface
{
    private function toDomain(RoutineExecution $model): DomainRoutineExecution
    {
        return DomainRoutineExecution::fromArray([
            'id' => $model->id,
            'routine_id' => $model->routine_id,
            'user_id' => $model->user_id,
            'date' => $model->date->toDateString(),
            'completed_steps' => $model->completed_steps ?? [],
            'is_completed' => $model->is_completed ?? false,
            'started_at' => $model->started_at?->toIso8601String(),
            'completed_at' => $model->completed_at?->toIso8601String(),
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
        ]);
    }

    public function findByUserAndDate(int $userId, string $date): array
    {
        $models = RoutineExecution::where('user_id', $userId)
            ->where('date', $date)
            ->get();

        return $models->map(fn (RoutineExecution $model) => $this->toDomain($model))->all();
    }

    public function findByRoutineAndDate(int $routineId, int $userId, string $date): ?DomainRoutineExecution
    {
        $model = RoutineExecution::where('routine_id', $routineId)
            ->where('user_id', $userId)
            ->where('date', $date)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function createOrUpdate(array $payload): DomainRoutineExecution
    {
        $model = RoutineExecution::updateOrCreate(
            [
                'routine_id' => (int) $payload['routine_id'],
                'user_id' => (int) $payload['user_id'],
                'date' => $payload['date'],
            ],
            [
                'completed_steps' => $payload['completed_steps'] ?? [],
                'is_completed' => $payload['is_completed'] ?? false,
                'started_at' => isset($payload['started_at']) ? Carbon::parse($payload['started_at']) : null,
                'completed_at' => isset($payload['completed_at']) ? Carbon::parse($payload['completed_at']) : null,
            ]
        );

        return $this->toDomain($model->fresh());
    }

    public function deleteByRoutine(int $routineId): void
    {
        RoutineExecution::where('routine_id', $routineId)->delete();
    }
}
