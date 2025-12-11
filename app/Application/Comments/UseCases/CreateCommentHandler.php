<?php

declare(strict_types=1);

namespace App\Application\Comments\UseCases;

use App\Domain\Comments\Entities\CommentEntity;
use App\Domain\Comments\Repositories\CommentRepositoryInterface;
use App\Infrastructure\Persistence\Comments\CommentMapper;
use App\Models\Comment;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class CreateCommentHandler
{
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository
    ) {}

    public function handle(CreateCommentCommand $command): Comment
    {
        return DB::transaction(function () use ($command) {
            $now = new DateTimeImmutable();
            $commentEntity = new CommentEntity(
                id: 0,
                cardId: $command->cardId,
                userId: $command->userId,
                text: $command->content,
                createdAt: $now,
                updatedAt: $now,
            );

            $savedEntity = $this->commentRepository->save($commentEntity);

            $model = CommentMapper::toModel($savedEntity);
            return $model->load('user');
        });
    }
}
