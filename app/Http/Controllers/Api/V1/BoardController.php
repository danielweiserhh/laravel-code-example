<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Boards\Queries\GetBoardsHandler;
use App\Application\Boards\Queries\GetBoardsQuery;
use App\Application\Boards\Queries\GetBoardWithCardsHandler;
use App\Application\Boards\Queries\GetBoardWithCardsQuery;
use App\Application\Boards\UseCases\CreateBoardCommand;
use App\Application\Boards\UseCases\CreateBoardHandler;
use App\Application\Boards\UseCases\DeleteBoardCommand;
use App\Application\Boards\UseCases\DeleteBoardHandler;
use App\Application\Boards\UseCases\UpdateBoardCommand;
use App\Application\Boards\UseCases\UpdateBoardHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBoardRequest;
use App\Http\Requests\Api\V1\UpdateBoardRequest;
use App\Models\Board;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function __construct(
        protected CreateBoardHandler $createBoard,
        protected UpdateBoardHandler $updateBoard,
        protected DeleteBoardHandler $deleteBoard,
        protected GetBoardsHandler $getBoards,
        protected GetBoardWithCardsHandler $getBoardWithCards
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetBoardsQuery(
            userId: $request->user()->id,
            workspaceId: $request->query('workspace_id') ? (int) $request->query('workspace_id') : null,
        );

        $boards = $this->getBoards->handle($query);

        return $this->success($boards);
    }

    public function store(StoreBoardRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $command = new CreateBoardCommand(
            workspaceId: (int) $validated['workspace_id'],
            userId: $request->user()->id,
            name: $validated['name'],
            description: $validated['description'] ?? null,
            color: $validated['color'] ?? null,
            privacy: $validated['privacy'] ?? null,
            settings: $validated['settings'] ?? null,
        );

        $board = $this->createBoard->handle($command);

        return $this->created($board, 'Доска создана');
    }

    public function show(Board $board): JsonResponse
    {
        $this->authorize('view', $board);

        $query = new GetBoardWithCardsQuery(boardId: $board->id);
        $board = $this->getBoardWithCards->handle($query);

        return $this->success($board);
    }

    public function update(UpdateBoardRequest $request, Board $board): JsonResponse
    {
        $this->authorize('update', $board);

        $validated = $request->validated();

        $command = new UpdateBoardCommand(
            boardId: $board->id,
            name: $validated['name'] ?? null,
            description: $validated['description'] ?? null,
            color: $validated['color'] ?? null,
            privacy: $validated['privacy'] ?? null,
            isFavorite: $validated['is_favorite'] ?? null,
            settings: $validated['settings'] ?? null,
        );

        $board = $this->updateBoard->handle($command);

        return $this->success($board, 'Доска обновлена');
    }

    public function destroy(Board $board): JsonResponse
    {
        $this->authorize('delete', $board);

        $command = new DeleteBoardCommand(
            boardId: $board->id,
        );

        $this->deleteBoard->handle($command);

        return $this->success(null, 'Доска удалена');
    }
}
