<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Workspaces;

use App\Domain\Workspaces\Entities\WorkspaceEntity;
use App\Models\Workspace;
use DateTimeImmutable;

final class WorkspaceMapper
{
    public static function toEntity(Workspace $model): WorkspaceEntity
    {
        return new WorkspaceEntity(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            slug: $model->slug,
            settings: $model->settings,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: DateTimeImmutable::createFromMutable($model->updated_at),
        );
    }

    public static function toModel(WorkspaceEntity $entity, ?Workspace $model = null): Workspace
    {
        $model ??= new Workspace();

        if ($entity->id > 0) {
            $model->id = $entity->id;
        }

        $model->name = $entity->name;
        $model->description = $entity->description;
        $model->slug = $entity->slug;
        $model->settings = $entity->settings;
        $model->created_at = $entity->createdAt;
        $model->updated_at = $entity->updatedAt;

        return $model;
    }
}
