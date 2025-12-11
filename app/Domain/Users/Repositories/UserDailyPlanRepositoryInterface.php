<?php

declare(strict_types=1);

namespace App\Domain\Users\Repositories;

use App\Domain\Users\Entities\UserDailyPlanEntity;
use App\Domain\Users\Entities\UserEntity;

interface UserDailyPlanRepositoryInterface
{
    public function findForUserAndDate(UserEntity $user, string $date): ?UserDailyPlanEntity;

    public function save(UserDailyPlanEntity $plan): UserDailyPlanEntity;

    public function updateOrCreate(array $attributes, array $values): UserDailyPlanEntity;
}
