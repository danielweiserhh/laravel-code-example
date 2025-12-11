<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Workspaces;

use App\Domain\Workspaces\Entities\WorkspaceEntity;
use App\Domain\Workspaces\Repositories\WorkspaceRepositoryInterface;
use App\Models\Workspace;

final class EloquentWorkspaceRepository implements WorkspaceRepositoryInterface
{
    public function find(int $id): ?WorkspaceEntity
    {
        $model = Workspace::find($id);

        return $model ? WorkspaceMapper::toEntity($model) : null;
    }

    public function findOrFail(int $id): WorkspaceEntity
    {
        $model = Workspace::findOrFail($id);

        return WorkspaceMapper::toEntity($model);
    }

    public function save(WorkspaceEntity $workspace): WorkspaceEntity
    {
        $model = $workspace->id > 0 ? Workspace::find($workspace->id) : null;
        $model = WorkspaceMapper::toModel($workspace, $model);
        $model->save();

        return WorkspaceMapper::toEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        return Workspace::findOrFail($id)->delete();
    }
}
