<?php

declare(strict_types=1);

namespace App\Application\Workspaces\UseCases;

use App\Domain\Workspaces\Entities\WorkspaceEntity;
use App\Domain\Workspaces\Repositories\WorkspaceRepositoryInterface;
use App\Infrastructure\Persistence\Workspaces\WorkspaceMapper;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class UpdateWorkspaceHandler
{
    public function __construct(
        private readonly WorkspaceRepositoryInterface $workspaceRepository
    ) {}

    public function handle(UpdateWorkspaceCommand $command): Workspace
    {
        return DB::transaction(function () use ($command) {
            $workspaceEntity = $this->workspaceRepository->findOrFail($command->workspaceId);

            $updatedEntity = $workspaceEntity;
            if ($command->name !== null) {
                $updatedEntity = $updatedEntity->withName($command->name);
            }
            if ($command->description !== null) {
                $updatedEntity = $updatedEntity->withDescription($command->description);
            }

            if ($updatedEntity !== $workspaceEntity) {
                $savedEntity = $this->workspaceRepository->save($updatedEntity);
                $model = WorkspaceMapper::toModel($savedEntity);
                if ($command->name !== null) {
                    $model->slug = Str::slug($command->name);
                }
                if ($command->settings !== null) {
                    $model->settings = $command->settings;
                }
                $model->save();
                return $model->fresh();
            }

            $model = WorkspaceMapper::toModel($workspaceEntity);
            if ($command->settings !== null) {
                $model->settings = $command->settings;
                $model->save();
            }

            return $model->fresh();
        });
    }
}
