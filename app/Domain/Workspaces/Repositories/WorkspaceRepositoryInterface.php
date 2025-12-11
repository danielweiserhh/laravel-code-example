<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\Repositories;

use App\Domain\Workspaces\Entities\WorkspaceEntity;

interface WorkspaceRepositoryInterface
{
    public function find(int $id): ?WorkspaceEntity;

    public function findOrFail(int $id): WorkspaceEntity;

    public function save(WorkspaceEntity $workspace): WorkspaceEntity;

    public function delete(int $id): bool;
}
