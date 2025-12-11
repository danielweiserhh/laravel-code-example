<?php

declare(strict_types=1);

namespace App\Application\Comments\UseCases;

use App\Domain\Comments\Repositories\CommentRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class DeleteCommentHandler
{
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository
    ) {}

    public function handle(DeleteCommentCommand $command): void
    {
        DB::transaction(function () use ($command) {
            $this->commentRepository->delete($command->commentId);
        });
    }
}
