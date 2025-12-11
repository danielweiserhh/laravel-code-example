<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Boards\Queries\GetListsHandler;
use App\Application\Boards\Queries\GetListsQuery;
use App\Application\Boards\UseCases\CreateListCommand;
use App\Application\Boards\UseCases\CreateListHandler;
use App\Application\Boards\UseCases\DeleteListCommand;
use App\Application\Boards\UseCases\DeleteListHandler;
use App\Application\Boards\UseCases\UpdateListCommand;
use App\Application\Boards\UseCases\UpdateListHandler;
use App\Domain\Boards\Repositories\BoardRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreListRequest;
use App\Http\Requests\Api\V1\UpdateListRequest;
use App\Models\ListModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListController extends Controller
{
    public function __construct(
        protected GetListsHandler $getLists,
        protected BoardRepositoryInterface $boardRepository,
        protected CreateListHandler $createList,
        protected UpdateListHandler $updateList,
        protected DeleteListHandler $deleteList
    ) {}

    public function index(Request $request): JsonResponse
    {
        $boardId = $request->query('board_id') ? (int) $request->query('board_id') : null;
        $includeArchived = $request->boolean('include_archived', false);

        $query = new GetListsQuery(
            boardId: $boardId,
            includeArchived: $includeArchived
        );

        $lists = $this->getLists->handle($query);

        return $this->success($lists);
    }

    public function store(StoreListRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $boardId = (int) $validated['board_id'];

        $board = $this->boardRepository->find($boardId);
        if (! $board) {
            return $this->error('Доска не найдена', 404);
        }
        
        $boardModel = \App\Models\Board::findOrFail($boardId);
        $this->authorize('update', $boardModel);

        $command = new CreateListCommand(
            boardId: $boardId,
            name: $validated['name'],
            position: null,
        );

        $list = $this->createList->handle($command);

        return $this->created($list, 'Колонка создана');
    }

    public function update(UpdateListRequest $request, ListModel $list): JsonResponse
    {
        $this->authorize('update', $list->board);

        $validated = $request->validated();

        $command = new UpdateListCommand(
            listId: $list->id,
            name: $validated['name'] ?? null,
            position: isset($validated['position']) ? (int) $validated['position'] : null,
            isArchived: $validated['is_archived'] ?? null,
        );

        $list = $this->updateList->handle($command);

        return $this->success($list, 'Колонка обновлена');
    }

    public function destroy(ListModel $list): JsonResponse
    {
        $this->authorize('update', $list->board);

        $command = new DeleteListCommand(
            listId: $list->id,
        );

        try {
            $this->deleteList->handle($command);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(null, 'Колонка удалена');
    }
}
