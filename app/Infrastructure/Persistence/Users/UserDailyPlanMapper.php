<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Users;

use App\Domain\Users\Entities\UserDailyPlanEntity;
use App\Models\UserDailyPlan;
use DateTimeImmutable;

final class UserDailyPlanMapper
{
    public static function toEntity(UserDailyPlan $model): UserDailyPlanEntity
    {
        return new UserDailyPlanEntity(
            id: $model->id,
            userId: $model->user_id,
            planDate: $model->plan_date->format('Y-m-d'),
            bigThree: $model->big_three,
            noteForUser: $model->note_for_user,
            aiJobId: $model->ai_job_id,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: DateTimeImmutable::createFromMutable($model->updated_at),
        );
    }

    public static function toModel(UserDailyPlanEntity $entity, ?UserDailyPlan $model = null): UserDailyPlan
    {
        $model ??= new UserDailyPlan();

        if ($entity->id > 0) {
            $model->id = $entity->id;
        }

        $model->user_id = $entity->userId;
        $model->plan_date = $entity->planDate;
        $model->big_three = $entity->bigThree;
        $model->note_for_user = $entity->noteForUser;
        $model->ai_job_id = $entity->aiJobId;
        $model->created_at = $entity->createdAt;
        $model->updated_at = $entity->updatedAt;

        return $model;
    }
}
