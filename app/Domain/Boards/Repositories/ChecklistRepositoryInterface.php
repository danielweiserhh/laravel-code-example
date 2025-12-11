<?php

declare(strict_types=1);

namespace App\Domain\Boards\Repositories;

use App\Domain\Boards\Entities\ChecklistEntity;

interface ChecklistRepositoryInterface
{
    public function find(int $id): ?ChecklistEntity;

    public function findOrFail(int $id): ChecklistEntity;

    public function save(ChecklistEntity $checklist): ChecklistEntity;

    public function delete(int $id): void;
}
