<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\AI\UseCases\ParseInboxItemCommand;
use App\Application\AI\UseCases\ParseInboxItemHandler;
use App\Application\Inbox\Queries\GetInboxItemsHandler;
use App\Application\Inbox\Queries\GetInboxItemsQuery;
use App\Application\Inbox\UseCases\CreateInboxItemCommand;
use App\Application\Inbox\UseCases\CreateInboxItemHandler;
use App\Application\Inbox\UseCases\DeleteInboxItemCommand;
use App\Application\Inbox\UseCases\DeleteInboxItemHandler;
use App\Application\Inbox\UseCases\UpdateInboxItemCommand;
use App\Application\Inbox\UseCases\UpdateInboxItemHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateInboxItemRequest;
use App\Http\Requests\Api\V1\UpdateInboxItemRequest;
use App\Models\InboxItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function __construct(
        private readonly CreateInboxItemHandler $createInboxItem,
        private readonly UpdateInboxItemHandler $updateInboxItem,
        private readonly DeleteInboxItemHandler $deleteInboxItem,
        private readonly GetInboxItemsHandler $getInboxItems,
        private readonly ParseInboxItemHandler $parseInboxItemHandler
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min(50, $perPage)); 
        $unprocessedOnly = $request->query('unprocessed_only', false);

        $query = new GetInboxItemsQuery(
            userId: $request->user()->id,
            perPage: $perPage,
            unprocessedOnly: (bool) $unprocessedOnly,
        );

        $paginatedResult = $this->getInboxItems->handle($query);

        
        $items = array_map(fn ($item) => $item->toArray(), $paginatedResult->items);

        return $this->success([
            'data' => $items,
            'current_page' => $paginatedResult->currentPage,
            'per_page' => $paginatedResult->perPage,
            'total' => $paginatedResult->total,
            'last_page' => $paginatedResult->lastPage,
        ]);
    }

    public function store(CreateInboxItemRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $item = $this->createInboxItem->handle(
            new CreateInboxItemCommand(
                userId: $request->user()->id,
                workspaceId: $validated['workspace_id'] ?? null,
                content: $validated['content'],
                source: $validated['source'] ?? 'manual'
            )
        );

        $shouldParse = (bool) ($validated['ai_parse'] ?? false);
        
        if ($shouldParse) {
            $this->parseInboxItemHandler->handle(
                new ParseInboxItemCommand(
                    inboxItemId: $item->id,
                    language: 'ru'
                )
            );
        }

        $message = $shouldParse
            ? 'Заметка создана, обработка AI начата'
            : 'Заметка создана';

        return $this->created(['item' => $item->toArray()], $message);
    }

    public function destroy(InboxItem $inboxItem): JsonResponse
    {
        $this->authorize('delete', $inboxItem);

        $command = new DeleteInboxItemCommand(
            inboxItemId: $inboxItem->id,
        );

        $this->deleteInboxItem->handle($command);

        return $this->success(null, 'Заметка удалена');
    }

    public function update(UpdateInboxItemRequest $request, InboxItem $inboxItem): JsonResponse
    {
        $this->authorize('update', $inboxItem);

        $validated = $request->validated();

        $command = new UpdateInboxItemCommand(
            inboxItemId: $inboxItem->id,
            content: $validated['content'] ?? null,
            isProcessed: $validated['is_processed'] ?? null,
        );

        $item = $this->updateInboxItem->handle($command);

        return $this->success($item->toArray(), 'Заметка обновлена');
    }

    public function parseWithAI(InboxItem $inboxItem): JsonResponse
    {
        $this->authorize('update', $inboxItem);

        $aiJob = $this->parseInboxItemHandler->handle(
            new ParseInboxItemCommand(
                inboxItemId: $inboxItem->id,
                language: 'ru'
            )
        );

        return $this->accepted(
            ['job_id' => $aiJob->id],
            'Обработка AI начата'
        );
    }
}
