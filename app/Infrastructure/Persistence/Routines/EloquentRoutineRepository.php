<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Routines;

use App\Domain\Routines\Repositories\RoutineRepositoryInterface;
use App\Domain\Routines\ValueObjects\Routine as DomainRoutine;
use App\Domain\Routines\ValueObjects\RoutineStep as DomainRoutineStep;
use App\Domain\Shared\ValueObjects\UserId;
use App\Models\Routine;
use App\Models\RoutineStep;

final class EloquentRoutineRepository implements RoutineRepositoryInterface
{
    private function toDomain(Routine $model): DomainRoutine
    {
        $steps = [];

        if ($model->relationLoaded('steps')) {
            foreach ($model->steps as $step) {
                $steps[] = $this->stepToDomain($step);
            }
        }

        return DomainRoutine::fromArray([
            'id' => $model->id,
            'user_id' => $model->user_id,
            'workspace_id' => $model->workspace_id,
            'name' => $model->name,
            'description' => $model->description,
            'type' => $model->type,
            'is_active' => $model->is_active,
            'settings' => $model->settings,
            'steps' => $steps,
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
        ]);
    }
    
    private function stepToDomain(RoutineStep $model): DomainRoutineStep
    {
        return DomainRoutineStep::fromArray([
            'id' => $model->id,
            'routine_id' => $model->routine_id,
            'title' => $model->title,
            'description' => $model->description,
            'position' => $model->position,
            'duration_minutes' => $model->duration_minutes,
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
        ]);
    }

    public function find(int $id): ?DomainRoutine
    {
        $model = Routine::with('steps')->find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findOrFail(int $id): DomainRoutine
    {
        $model = Routine::with('steps')->findOrFail($id);

        return $this->toDomain($model);
    }
    
    public function findByUserId(UserId $userId): array
    {
        return $this->getAllForUser($userId->value);
    }
    
    public function getAllForUser(int $userId): array
    {
        $models = Routine::where('user_id', $userId)
            ->with('steps')
            ->orderBy('created_at', 'desc')
            ->get();

        $result = [];

        foreach ($models as $model) {
            $result[] = $this->toDomain($model);
        }

        return $result;
    }
    
    public function findActiveByUserId(UserId $userId): array
    {
        $models = Routine::where('user_id', $userId->value)
            ->where('is_active', true)
            ->with('steps')
            ->orderBy('created_at', 'desc')
            ->get();

        $result = [];
        foreach ($models as $model) {
            $result[] = $this->toDomain($model);
        }

        return $result;
    }
    
    public function create(array $payload, UserId $userId): DomainRoutine
    {
        $model = new Routine;
        $model->user_id = $userId->value;
        $model->workspace_id = $payload['workspace_id'] ?? null;
        $model->name = (string) $payload['name'];
        $model->description = $payload['description'] ?? null;
        $model->type = $payload['type'] ?? 'custom';
        $model->is_active = $payload['is_active'] ?? true;
        $model->settings = $payload['settings'] ?? [];
        $model->save();
        
        if (isset($payload['steps']) && is_array($payload['steps'])) {
            foreach ($payload['steps'] as $index => $stepData) {
                $model->steps()->create([
                    'title' => $stepData['title'],
                    'description' => $stepData['description'] ?? null,
                    'position' => $stepData['position'] ?? $index,
                    'duration_minutes' => $stepData['duration_minutes'] ?? null,
                ]);
            }
        }

        return $this->toDomain($model->fresh('steps'));
    }

    
    public function update(int $id, array $payload): DomainRoutine
    {
        $model = Routine::findOrFail($id);

        if (array_key_exists('name', $payload)) {
            $model->name = (string) $payload['name'];
        }

        if (array_key_exists('description', $payload)) {
            $model->description = $payload['description'];
        }

        if (array_key_exists('type', $payload)) {
            $model->type = $payload['type'];
        }

        if (array_key_exists('is_active', $payload)) {
            $model->is_active = (bool) $payload['is_active'];
        }

        if (array_key_exists('settings', $payload)) {
            $model->settings = $payload['settings'];
        }

        $model->save();

        return $this->toDomain($model->fresh('steps'));
    }

    public function delete(int $id): bool
    {
        $model = Routine::findOrFail($id);

        return (bool) $model->delete();
    }

    
    public function addStep(int $routineId, array $stepData): DomainRoutine
    {
        $model = Routine::findOrFail($routineId);
        $maxPosition = $model->steps()->max('position') ?? -1;

        $model->steps()->create([
            'title' => $stepData['title'],
            'description' => $stepData['description'] ?? null,
            'position' => $stepData['position'] ?? $maxPosition + 1,
            'duration_minutes' => $stepData['duration_minutes'] ?? null,
        ]);

        return $this->toDomain($model->fresh('steps'));
    }
    
    public function updateSteps(int $routineId, array $steps): DomainRoutine
    {
        $model = Routine::findOrFail($routineId);
        $model->steps()->delete();
        
        foreach ($steps as $index => $stepData) {
            $model->steps()->create([
                'title' => $stepData['title'],
                'description' => $stepData['description'] ?? null,
                'position' => $stepData['position'] ?? $index,
                'duration_minutes' => $stepData['duration_minutes'] ?? null,
            ]);
        }

        return $this->toDomain($model->fresh('steps'));
    }

    public function toggleActive(int $id, bool $isActive): DomainRoutine
    {
        $model = Routine::findOrFail($id);
        $model->is_active = $isActive;
        $model->save();

        return $this->toDomain($model->fresh('steps'));
    }
}
