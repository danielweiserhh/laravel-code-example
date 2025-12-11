<?php

declare(strict_types=1);

namespace App\Application\Comments\Queries;

use App\Domain\Comments\Collections\CommentCollection;
use App\Domain\Comments\Repositories\CommentRepositoryInterface;
use App\Infrastructure\Persistence\Comments\CommentMapper;
use App\Models\Comment;

final class GetCommentsHandler
{
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository
    ) {}

    public function handle(GetCommentsQuery $query): array
    {
        $collection = $this->commentRepository->getByCard($query->cardId);

        return array_map(
            static function ($entity) {
                $model = CommentMapper::toModel($entity);
                return $model->load('user')->toArray();
            },
            $collection->toArray()
        );
    }
}
