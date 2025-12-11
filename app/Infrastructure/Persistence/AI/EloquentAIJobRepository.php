<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\AI;

use App\Domain\AI\Entities\AIJobEntity;
use App\Domain\AI\Repositories\AIJobRepositoryInterface;
use App\Models\AIJob;
use DateTimeImmutable;

final class EloquentAIJobRepository implements AIJobRepositoryInterface
{
    public function create(array $data): AIJobEntity
    {
        $job = AIJob::create($data);

        return AIJobMapper::toEntity($job->fresh());
    }

    public function save(AIJobEntity $job): AIJobEntity
    {
        $model = AIJob::find($job->id) ?? new AIJob();
        $model = AIJobMapper::toModel($job, $model);
        $model->save();

        return AIJobMapper::toEntity($model->fresh());
    }

    public function find(string $id): ?AIJobEntity
    {
        $job = AIJob::find($id);

        return $job ? AIJobMapper::toEntity($job) : null;
    }
}
