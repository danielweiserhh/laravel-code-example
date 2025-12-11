<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Boards\UseCases\CreateChecklistCommand;
use App\Application\Boards\UseCases\CreateChecklistHandler;
use App\Application\Boards\UseCases\DeleteChecklistCommand;
use App\Application\Boards\UseCases\DeleteChecklistHandler;
use App\Application\Boards\UseCases\UpdateChecklistCommand;
use App\Application\Boards\UseCases\UpdateChecklistHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreChecklistRequest;
use App\Http\Requests\Api\V1\UpdateChecklistRequest;
use App\Models\Card;
use App\Models\Checklist;
use Illuminate\Http\JsonResponse;

class ChecklistController extends Controller
{
    public function __construct(
        private readonly CreateChecklistHandler $createChecklist,
        private readonly UpdateChecklistHandler $updateChecklist,
        private readonly DeleteChecklistHandler $deleteChecklist
    ) {}

    public function store(StoreChecklistRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $cardId = (int) $validated['card_id'];
        
        $card = Card::findOrFail($cardId);
        $this->authorize('update', $card);

        $command = new CreateChecklistCommand(
            cardId: $card->id,
            title: $validated['title'],
            items: $validated['items'] ?? null
        );

        $checklist = $this->createChecklist->handle($command);

        return $this->created($checklist, 'Чеклист создан');
    }

    public function update(UpdateChecklistRequest $request, Checklist $checklist): JsonResponse
    {
        $this->authorize('update', $checklist->card);

        $validated = $request->validated();

        $command = new UpdateChecklistCommand(
            checklistId: $checklist->id,
            title: $validated['title'] ?? null
        );

        $updatedChecklist = $this->updateChecklist->handle($command);

        return $this->success($updatedChecklist, 'Чеклист обновлен');
    }

    public function destroy(Checklist $checklist): JsonResponse
    {
        $this->authorize('update', $checklist->card);

        $command = new DeleteChecklistCommand(checklistId: $checklist->id);
        $this->deleteChecklist->handle($command);

        return $this->success(null, 'Чеклист удален');
    }
}
