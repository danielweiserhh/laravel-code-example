<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\AI;

use App\Domain\AI\Entities\AIJobEntity;
use App\Enums\AIJobType;
use App\Enums\JobStatus;
use App\Models\AIJob;
use DateTimeImmutable;

final class AIJobMapper
{
    public static function toEntity(AIJob $model): AIJobEntity
    {
        return new AIJobEntity(
            id: (string) $model->id,
            userId: $model->user_id,
            workspaceId: $model->workspace_id,
            type: $model->type instanceof AIJobType ? $model->type->value : (string) $model->type,
            status: $model->status instanceof JobStatus ? $model->status->value : (string) $model->status,
            payload: $model->payload ?? [],
            result: $model->result,
            errorMessage: $model->error_message,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: DateTimeImmutable::createFromMutable($model->updated_at),
        );
    }

    public static function toModel(AIJobEntity $entity, ?AIJob $model = null): AIJob
    {
        $model ??= new AIJob();

        if ($entity->id !== '0' && $entity->id !== '') {
            $model->id = $entity->id;
        }
        
        $model->user_id = $entity->userId;
        $model->workspace_id = $entity->workspaceId;
        $model->type = AIJobType::tryFrom($entity->type) ?? $entity->type;
        $model->status = JobStatus::tryFrom($entity->status) ?? $entity->status;
        $model->payload = $entity->payload;
        $model->result = $entity->result;
        $model->error_message = $entity->errorMessage;
        $model->created_at = $entity->createdAt;
        $model->updated_at = $entity->updatedAt;

        return $model;
    }
}

