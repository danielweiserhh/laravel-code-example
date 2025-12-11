<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Boards;

use App\Domain\Boards\Repositories\BoardRepositoryInterface;
use App\Domain\Boards\ValueObjects\Board as DomainBoard;
use App\DTOs\BoardCreateDTO;
use App\DTOs\BoardUpdateDTO;
use App\Models\Board;

final class EloquentBoardRepository implements BoardRepositoryInterface
{
    private function toDomain(Board $model): DomainBoard
    {
        return DomainBoard::fromArray([
            'id' => $model->id,
            'workspace_id' => $model->workspace_id,
            'name' => $model->name,
            'description' => $model->description,
            'color' => $model->color,
            'privacy' => ($model->privacy instanceof \App\Enums\BoardPrivacy 
                ? $model->privacy->value 
                : ($model->privacy ?? \App\Domain\Boards\Enums\BoardPrivacy::PRIVATE->value)),
            'is_favorite' => $model->is_favorite ?? false,
            'settings' => $model->settings,
            'position' => $model->position ?? 0,
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
        ]);
    }

    public function find(int $id): ?DomainBoard
    {
        $model = Board::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findOrFail(int $id): DomainBoard
    {
        $model = Board::findOrFail($id);

        return $this->toDomain($model);
    }

    public function findWithRelations(int $id, array $relations): ?DomainBoard
    {
        $relationStrings = [];

        foreach ($relations as $relation) {
            if (is_string($relation)) {
                $relationStrings[] = $relation;
            }
        }

        $model = Board::query()
            ->whereKey($id)
            ->with($relationStrings)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function create(BoardCreateDTO $dto): DomainBoard
    {
        $board = new Board;
        $board->workspace_id = $dto->workspaceId;
        $board->name = $dto->name;
        $board->description = $dto->description;
        $board->color = $dto->color;
        $board->privacy = $dto->privacy 
            ? \App\Enums\BoardPrivacy::from($dto->privacy) 
            : \App\Enums\BoardPrivacy::PRIVATE;
        $board->settings = $dto->settings ?? [];
        $board->save();

        return $this->toDomain($board->fresh());
    }

    public function update(int $id, BoardUpdateDTO $dto): DomainBoard
    {
        $board = Board::findOrFail($id);

        if ($dto->name !== null) {
            $board->name = $dto->name;
        }

        if ($dto->description !== null) {
            $board->description = $dto->description;
        }

        if ($dto->color !== null) {
            $board->color = $dto->color;
        }

        if ($dto->privacy !== null) {
            $board->privacy = \App\Enums\BoardPrivacy::from($dto->privacy);
        }

        if ($dto->isFavorite !== null) {
            $board->is_favorite = $dto->isFavorite;
        }
        
        if ($dto->settings !== null) {
            $board->settings = $dto->settings;
        }

        $board->save();

        return $this->toDomain($board->fresh());
    }

    public function save(DomainBoard $board): DomainBoard
    {
        if ($board->id > 0) {
            $model = Board::findOrFail($board->id);
            $model->workspace_id = $board->workspaceId;
            $model->name = $board->name;
            $model->description = $board->description;
            $model->color = $board->color;
            $model->privacy = \App\Enums\BoardPrivacy::from($board->privacy);
            $model->is_favorite = $board->isFavorite;
            $model->settings = $board->settings;
            $model->position = $board->position;
            $model->save();

            return $this->toDomain($model->fresh());
        }

        throw new \DomainException('Cannot save new board without DTO. Use create() method.');
    }

    public function delete(int $id): bool
    {
        return Board::findOrFail($id)->delete();
    }

    public function findByNameInUserWorkspaces(int $userId, string $boardName): ?DomainBoard
    {
        $model = Board::whereHas('workspace.members', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where('name', $boardName)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function isUserMember(int $boardId, int $userId): bool
    {
        return Board::find($boardId)
            ?->members()
            ->where('users.id', $userId)
            ->exists() ?? false;
    }

    public function isUserAdminOrOwner(int $boardId, int $userId): bool
    {
        return Board::find($boardId)
            ?->members()
            ->where('users.id', $userId)
            ->whereIn('board_members.role', ['owner', 'admin'])
            ->exists() ?? false;
    }
}
