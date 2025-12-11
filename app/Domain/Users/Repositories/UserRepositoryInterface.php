<?php

declare(strict_types=1);

namespace App\Domain\Users\Repositories;

use App\Domain\Users\Entities\UserEntity;

interface UserRepositoryInterface
{
    public function find(int $id): ?UserEntity;

    public function findOrFail(int $id): UserEntity;

    public function findByEmail(string $email): ?UserEntity;

    public function create(array $data): UserEntity;

    public function save(UserEntity $user): UserEntity;
}
