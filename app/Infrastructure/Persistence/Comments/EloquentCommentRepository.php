<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Comments;

use App\Domain\Comments\Collections\CommentCollection;
use App\Domain\Comments\Entities\CommentEntity;
use App\Domain\Comments\Repositories\CommentRepositoryInterface;
use App\Models\Comment;

final class EloquentCommentRepository implements CommentRepositoryInterface
{
    public function find(int $id): ?CommentEntity
    {
        $model = Comment::find($id);

        return $model ? CommentMapper::toEntity($model) : null;
    }

    public function findOrFail(int $id): CommentEntity
    {
        $model = Comment::findOrFail($id);

        return CommentMapper::toEntity($model);
    }

    public function getByCard(int $cardId): CommentCollection
    {
        $models = Comment::where('card_id', $cardId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        $entities = array_map(
            static fn (Comment $model) => CommentMapper::toEntity($model),
            $models->all()
        );

        return CommentMapper::toCollection($entities);
    }

    public function save(CommentEntity $comment): CommentEntity
    {
        $model = $comment->id > 0 ? Comment::find($comment->id) : null;
        $model = CommentMapper::toModel($comment, $model);
        $model->save();

        return CommentMapper::toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        Comment::findOrFail($id)->delete();
    }
}
