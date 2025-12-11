<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Boards;

use App\Domain\Boards\Entities\ChecklistEntity;
use App\Domain\Boards\Repositories\ChecklistRepositoryInterface;
use App\Models\Checklist;

final class EloquentChecklistRepository implements ChecklistRepositoryInterface
{
    public function find(int $id): ?ChecklistEntity
    {
        $model = Checklist::find($id);

        return $model ? ChecklistMapper::toEntity($model) : null;
    }

    public function findOrFail(int $id): ChecklistEntity
    {
        $model = Checklist::findOrFail($id);

        return ChecklistMapper::toEntity($model);
    }

    public function save(ChecklistEntity $checklist): ChecklistEntity
    {
        $model = $checklist->id > 0 ? Checklist::find($checklist->id) : null;
        $model = ChecklistMapper::toModel($checklist, $model);
        $model->save();

        return ChecklistMapper::toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        Checklist::findOrFail($id)->delete();
    }
}
