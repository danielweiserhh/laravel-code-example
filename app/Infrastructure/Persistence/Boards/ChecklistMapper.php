<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Boards;

use App\Domain\Boards\Entities\ChecklistEntity;
use App\Models\Checklist;
use DateTimeImmutable;

final class ChecklistMapper
{
    public static function toEntity(Checklist $model): ChecklistEntity
    {
        return new ChecklistEntity(
            id: $model->id,
            cardId: $model->card_id,
            title: $model->title,
            position: $model->position,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: DateTimeImmutable::createFromMutable($model->updated_at),
        );
    }

    public static function toModel(ChecklistEntity $entity, ?Checklist $model = null): Checklist
    {
        $model ??= new Checklist();

        if ($entity->id > 0) {
            $model->id = $entity->id;
        }

        $model->card_id = $entity->cardId;
        $model->title = $entity->title;
        $model->position = $entity->position;
        $model->created_at = $entity->createdAt;
        $model->updated_at = $entity->updatedAt;

        return $model;
    }
}
