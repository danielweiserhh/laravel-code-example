<?php

declare(strict_types=1);

namespace App\Domain\AI\Repositories;

use App\Domain\AI\Entities\AIJobEntity;

interface AIJobRepositoryInterface
{
    public function create(array $data): AIJobEntity;

    public function save(AIJobEntity $job): AIJobEntity;

    public function find(string $id): ?AIJobEntity;
}
