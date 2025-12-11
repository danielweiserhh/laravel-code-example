<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Users;

use App\Domain\Users\Entities\UserDailyPlanEntity;
use App\Domain\Users\Entities\UserEntity;
use App\Domain\Users\Repositories\UserDailyPlanRepositoryInterface;
use App\Models\UserDailyPlan;
use DateTimeImmutable;

final class EloquentUserDailyPlanRepository implements UserDailyPlanRepositoryInterface
{
    public function findForUserAndDate(UserEntity $user, string $date): ?UserDailyPlanEntity
    {
        $model = UserDailyPlan::where('user_id', $user->id)
            ->whereDate('plan_date', $date)
            ->latest('id')
            ->first();

        return $model ? UserDailyPlanMapper::toEntity($model) : null;
    }

    public function save(UserDailyPlanEntity $plan): UserDailyPlanEntity
    {
        $model = $plan->id > 0 ? UserDailyPlan::find($plan->id) : null;
        $model = UserDailyPlanMapper::toModel($plan, $model);
        $model->save();

        return UserDailyPlanMapper::toEntity($model->fresh());
    }

    public function updateOrCreate(array $attributes, array $values): UserDailyPlanEntity
    {
        $now = new DateTimeImmutable();
        $model = UserDailyPlan::updateOrCreate($attributes, $values);

        return UserDailyPlanMapper::toEntity($model->fresh());
    }
}
