<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Users;

use App\Domain\Users\Entities\UserEntity;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Models\User;
use DateTimeImmutable;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function find(int $id): ?UserEntity
    {
        $model = User::find($id);

        return $model ? UserMapper::toEntity($model) : null;
    }

    public function findOrFail(int $id): UserEntity
    {
        $model = User::findOrFail($id);

        return UserMapper::toEntity($model);
    }

    public function findByEmail(string $email): ?UserEntity
    {
        $model = User::where('email', $email)->first();

        return $model ? UserMapper::toEntity($model) : null;
    }

    public function create(array $data): UserEntity
    {
        $now = new DateTimeImmutable();
        $user = User::create($data);

        return UserMapper::toEntity($user->fresh());
    }

    public function save(UserEntity $user): UserEntity
    {
        $model = $user->id > 0 ? User::find($user->id) : null;
        $model = UserMapper::toModel($user, $model);
        $model->save();

        return UserMapper::toEntity($model->fresh());
    }
}
