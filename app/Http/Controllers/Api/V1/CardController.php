<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Boards\Queries\GetCardHandler;
use App\Application\Boards\Queries\GetCardQuery;
use App\Application\Boards\Queries\GetCardsHandler;
use App\Application\Boards\Queries\GetCardsQuery;
use App\Application\Boards\UseCases\CreateCardCommand;
use App\Application\Boards\UseCases\CreateCardHandler;
use App\Application\Boards\UseCases\DeleteCardCommand;
use App\Application\Boards\UseCases\DeleteCardHandler;
use App\Application\Boards\UseCases\MoveCardCommand;
use App\Application\Boards\UseCases\MoveCardHandler;
use App\Application\Boards\UseCases\UpdateCardCommand;
use App\Application\Boards\UseCases\UpdateCardHandler;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\NormalizesInput;
use App\Http\Requests\Api\V1\MoveCardRequest;
use App\Http\Requests\Api\V1\StoreCardRequest;
use App\Http\Requests\Api\V1\UpdateCardRequest;
use App\Models\Card;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CardController extends Controller
{
    use NormalizesInput;

    public function __construct(
        protected CreateCardHandler $createCard,
        protected MoveCardHandler $moveCard,
        protected UpdateCardHandler $updateCard,
        protected DeleteCardHandler $deleteCard,
        protected GetCardsHandler $getCards,
        protected GetCardHandler $getCard
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetCardsQuery(
            listId: $request->query('list_id') ? (int) $request->query('list_id') : null,
            boardId: $request->query('board_id') ? (int) $request->query('board_id') : null,
        );

        $cards = $this->getCards->handle($query);

        return $this->success($cards);
    }

    public function store(StoreCardRequest $request): JsonResponse
    {
        $this->authorize('create', Card::class);

        $validated = $request->validated();
        $listId = (int) $validated['list_id'];

        $normalized = $this->normalizeValues($validated, [
            'description', 'start_date', 'due_date', 'duration', 'energy_level', 'task_type',
        ]);

        $cmd = new CreateCardCommand(
            listId: $listId,
            title: $validated['title'],
            description: $normalized['description'],
            position: $this->normalizeInt($validated, 'position'),
            assigneeId: $this->normalizeInt($validated, 'assignee_id'),
            checklist: $validated['checklist'] ?? null,
            startDate: $normalized['start_date'],
            dueDate: $normalized['due_date'],
            energyLevel: $normalized['energy_level'],
            taskType: $normalized['task_type'],
            customFields: $normalized['duration'] ? ['duration' => $normalized['duration']] : null
        );

        $card = $this->createCard->handle($cmd);

        return $this->created($card, 'Задача создана');
    }

    public function show(Card $card): JsonResponse
    {
        $this->authorize('view', $card);

        $query = new GetCardQuery(cardId: $card->id);
        $cardWithRelations = $this->getCard->handle($query);

        return $this->success($cardWithRelations);
    }

    public function update(UpdateCardRequest $request, Card $card): JsonResponse
    {
        $this->authorize('update', $card);

        $validated = $request->validated();
        
        $normalized = $this->normalizeValues($validated, [
            'description', 'start_date', 'due_date', 'duration', 'energy_level', 'task_type',
        ]);

        $command = new UpdateCardCommand(
            cardId: $card->id,
            title: $validated['title'] ?? null,
            description: $normalized['description'],
            startDate: $normalized['start_date'],
            dueDate: $normalized['due_date'],
            energyLevel: $normalized['energy_level'],
            taskType: $normalized['task_type'],
            isCompleted: $this->normalizeBool($validated, 'is_completed'),
            assignees: $validated['assignees'] ?? null,
            customFields: $normalized['duration'] ? ['duration' => $normalized['duration']] : null,
        );

        $card = $this->updateCard->handle($command);

        return $this->success($card, 'Задача обновлена');
    }

    public function destroy(Card $card): JsonResponse
    {
        $this->authorize('delete', $card);

        $command = new DeleteCardCommand(
            cardId: $card->id
        );
        
        $this->deleteCard->handle($command);

        return $this->success(null, 'Задача удалена');
    }

    public function move(MoveCardRequest $request, Card $card): JsonResponse
    {
        $this->authorize('update', $card);

        $validated = $request->validated();
        $cmd = new MoveCardCommand(
            cardId: $card->id,
            toListId: (int) $validated['list_id'],
            toPosition: isset($validated['position']) ? (int) $validated['position'] : null
        );
        $this->moveCard->handle($cmd);
        $card->refresh();

        return $this->success($card, 'Задача перемещена');
    }
}
