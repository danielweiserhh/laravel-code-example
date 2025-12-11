<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Comments\Queries\GetCommentsHandler;
use App\Application\Comments\Queries\GetCommentsQuery;
use App\Application\Comments\UseCases\CreateCommentCommand;
use App\Application\Comments\UseCases\CreateCommentHandler;
use App\Application\Comments\UseCases\DeleteCommentCommand;
use App\Application\Comments\UseCases\DeleteCommentHandler;
use App\Application\Comments\UseCases\UpdateCommentCommand;
use App\Application\Comments\UseCases\UpdateCommentHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCommentRequest;
use App\Http\Requests\Api\V1\UpdateCommentRequest;
use App\Models\Card;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        private readonly GetCommentsHandler $getComments,
        private readonly CreateCommentHandler $createComment,
        private readonly UpdateCommentHandler $updateComment,
        private readonly DeleteCommentHandler $deleteComment
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cardId = $request->query('card_id');

        if (! $cardId) {
            return $this->error('Параметр card_id обязателен', 400);
        }

        $query = new GetCommentsQuery(cardId: (int) $cardId);
        $comments = $this->getComments->handle($query);

        return $this->success($comments);
    }

    public function store(StoreCommentRequest $request): JsonResponse
    {
        $card = Card::findOrFail($request->input('card_id'));
        $this->authorize('view', $card);

        $validated = $request->validated();

        $command = new CreateCommentCommand(
            cardId: (int) $validated['card_id'],
            userId: $request->user()->id,
            content: $validated['content']
        );

        $comment = $this->createComment->handle($command);

        return $this->created($comment, 'Комментарий создан');
    }

    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $validated = $request->validated();

        $command = new UpdateCommentCommand(
            commentId: $comment->id,
            content: $validated['content']
        );

        $updatedComment = $this->updateComment->handle($command);

        return $this->success($updatedComment, 'Комментарий обновлен');
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $command = new DeleteCommentCommand(commentId: $comment->id);
        $this->deleteComment->handle($command);

        return $this->success(null, 'Комментарий удален');
    }
}
