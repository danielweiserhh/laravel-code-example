<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Comments;

use App\Domain\Comments\Collections\CommentCollection;
use App\Domain\Comments\Entities\CommentEntity;
use App\Models\Comment;
use DateTimeImmutable;

final class CommentMapper
{
    public static function toEntity(Comment $model): CommentEntity
    {
        return new CommentEntity(
            id: $model->id,
            cardId: $model->card_id,
            userId: $model->user_id,
            text: $model->text,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: DateTimeImmutable::createFromMutable($model->updated_at),
        );
    }

    /**
     * @param CommentEntity[] $entities
     */
    public static function toCollection(array $entities): CommentCollection
    {
        return new CommentCollection($entities);
    }

    public static function toModel(CommentEntity $entity, ?Comment $model = null): Comment
    {
        $model ??= new Comment();

        if ($entity->id > 0) {
            $model->id = $entity->id;
        }

        $model->card_id = $entity->cardId;
        $model->user_id = $entity->userId;
        $model->text = $entity->text;
        $model->created_at = $entity->createdAt;
        $model->updated_at = $entity->updatedAt;

        return $model;
    }
}
