<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Domain\Boards\Repositories\ListRepositoryInterface;
use App\Domain\Boards\Services\CardDomainService;
use App\Models\Card;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateCardHandler
{
    public function __construct(
        private readonly CardRepositoryInterface $cardRepository,
        private readonly ListRepositoryInterface $listRepository,
        private readonly CardDomainService $cardDomainService
    ) {}

    public function handle(CreateCardCommand $command): Card
    {
        return DB::transaction(function () use ($command) {
            $list = $this->listRepository->find($command->listId);

            if (! $list) {
                throw ValidationException::withMessages(['listId' => 'List not found']);
            }

            $position = $command->position;
            
            if ($position === null) {
                $maxPosition = $this->cardRepository->getMaxPositionForList($command->listId);
                $position = $this->cardDomainService->calculateNextPosition($command->listId, $maxPosition);
            } else {
                $this->cardDomainService->validatePosition($position, $command->listId);
            }

            $startDate = $command->startDate !== null ? new DateTimeImmutable($command->startDate) : null;
            $dueDate = $command->dueDate !== null ? new DateTimeImmutable($command->dueDate) : null;
            $this->cardDomainService->validateDeadlines($startDate, $dueDate);

            $payload = [
                'list_id' => $command->listId,
                'board_id' => $list->boardId,
                'title' => $command->title,
                'description' => $command->description,
                'position' => $position,
                'start_date' => $command->startDate,
                'due_date' => $command->dueDate,
                'energy_level' => $command->energyLevel,
                'task_type' => $command->taskType,
                'custom_fields' => $command->customFields,
                'assignee_id' => $command->assigneeId,
                'checklist' => $command->checklist,
            ];

            $domainCard = $this->cardRepository->create($payload);

            return Card::with(['assignees', 'checklists.items'])->findOrFail($domainCard->id);
        });
    }
}
