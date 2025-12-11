<?php

declare(strict_types=1);

namespace App\Application\Comments\UseCases;

use App\Domain\Comments\Repositories\CommentRepositoryInterface;
use App\Infrastructure\Persistence\Comments\CommentMapper;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;

final class UpdateCommentHandler
{
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository
    ) {}

    public function handle(UpdateCommentCommand $command): Comment
    {
        return DB::transaction(function () use ($command) {
            $commentEntity = $this->commentRepository->findOrFail($command->commentId);
            $updatedEntity = $commentEntity->withText($command->content);
            $savedEntity = $this->commentRepository->save($updatedEntity);

            $model = CommentMapper::toModel($savedEntity);
            return $model->load('user');
        });
    }
}
