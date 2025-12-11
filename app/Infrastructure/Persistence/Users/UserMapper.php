<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Users;

use App\Domain\Users\Entities\UserEntity;
use App\Models\User;
use DateTimeImmutable;

final class UserMapper
{
    public static function toEntity(User $model): UserEntity
    {
        return new UserEntity(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            password: $model->password,
            googleId: $model->google_id,
            avatar: $model->avatar,
            phone: $model->phone,
            telegramUsername: $model->telegram_username,
            aiModel: $model->ai_model,
            speechModel: $model->speech_model,
            emailVerifiedAt: $model->email_verified_at
                ? DateTimeImmutable::createFromMutable($model->email_verified_at)
                : null,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: DateTimeImmutable::createFromMutable($model->updated_at),
        );
    }

    public static function toModel(UserEntity $entity, ?User $model = null): User
    {
        $model ??= new User();

        if ($entity->id > 0) {
            $model->id = $entity->id;
        }

        $model->name = $entity->name;
        $model->email = $entity->email;
        $model->password = $entity->password;
        $model->google_id = $entity->googleId;
        $model->avatar = $entity->avatar;
        $model->phone = $entity->phone;
        $model->telegram_username = $entity->telegramUsername;
        $model->ai_model = $entity->aiModel;
        $model->speech_model = $entity->speechModel;
        $model->email_verified_at = $entity->emailVerifiedAt;
        $model->created_at = $entity->createdAt;
        $model->updated_at = $entity->updatedAt;

        return $model;
    }
}
