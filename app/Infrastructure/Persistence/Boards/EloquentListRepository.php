<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Boards;

use App\Domain\Boards\Repositories\ListRepositoryInterface;
use App\Domain\Boards\ValueObjects\BoardList;
use App\Models\ListModel;

final class EloquentListRepository implements ListRepositoryInterface
{
    private function toDomain(ListModel $model): BoardList
    {
        return BoardList::fromArray([
            'id' => $model->id,
            'board_id' => $model->board_id,
            'name' => $model->name,
            'position' => $model->position ?? 0,
            'is_archived' => $model->is_archived ?? false,
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
        ]);
    }

    public function find(int $id): ?BoardList
    {
        $model = ListModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findOrFail(int $id): BoardList
    {
        $model = ListModel::findOrFail($id);

        return $this->toDomain($model);
    }

    
    public function getByBoard(int $boardId): array
    {
        $models = ListModel::query()
            ->where('board_id', $boardId)
            ->orderBy('position')
            ->get();

        $result = [];
        
        foreach ($models as $model) {
            $result[] = $this->toDomain($model);
        }

        return $result;
    }

    public function getMaxPositionForBoard(int $boardId): int
    {
        $max = ListModel::where('board_id', $boardId)->max('position');

        return $max !== null ? (int) $max : 0;
    }

    public function create(int $boardId, string $name, int $position): BoardList
    {
        $model = new ListModel;
        $model->board_id = $boardId;
        $model->name = $name;
        $model->position = $position;
        $model->is_archived = false;
        $model->save();

        return $this->toDomain($model->fresh());
    }

    public function save(BoardList $list): BoardList
    {
        if ($list->id > 0) {
            $model = ListModel::findOrFail($list->id);
            $model->board_id = $list->boardId;
            $model->name = $list->name;
            $model->position = $list->position;
            $model->is_archived = $list->isArchived;
            $model->save();

            return $this->toDomain($model->fresh());
        }

        throw new \DomainException('Cannot save new list without DTO. Use create() method.');
    }

    public function delete(int $id): bool
    {
        return ListModel::findOrFail($id)->delete();
    }
}
