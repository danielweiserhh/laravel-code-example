<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Domain\Boards\Services\CardDomainService;
use App\Models\Card;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class UpdateCardHandler
{
    public function __construct(
        private readonly CardDomainService $cardDomainService,
        private readonly CardRepositoryInterface $cardRepository
    ) {}

    public function handle(UpdateCardCommand $command): Card
    {
        return DB::transaction(function () use ($command) {
            $domainCard = $this->cardRepository->findOrFail($command->cardId);

            $customFieldsPayload = null;
            if ($command->customFields !== null) {
                $customFieldsPayload = array_merge($domainCard->customFields ?? [], $command->customFields);
                $customFieldsPayload = array_filter(
                    $customFieldsPayload,
                    static fn ($value) => $value !== null
                );
                $customFieldsPayload = empty($customFieldsPayload) ? null : $customFieldsPayload;
            }

            $startDate = $command->startDate !== null
                ? new DateTimeImmutable($command->startDate)
                : ($domainCard->startDate ? new DateTimeImmutable($domainCard->startDate) : null);
            $dueDate = $command->dueDate !== null
                ? new DateTimeImmutable($command->dueDate)
                : ($domainCard->dueDate ? new DateTimeImmutable($domainCard->dueDate) : null);
            $this->cardDomainService->validateDeadlines($startDate, $dueDate);

            $payload = array_filter([
                'title' => $command->title,
                'description' => $command->description,
                'start_date' => $command->startDate,
                'due_date' => $command->dueDate,
                'energy_level' => $command->energyLevel,
                'task_type' => $command->taskType,
                'is_completed' => $command->isCompleted,
            ], static fn ($value) => $value !== null);

            if ($command->assignees !== null) {
                $payload['assignees'] = $command->assignees;
            }
            if ($customFieldsPayload !== null || $command->customFields !== null) {
                $payload['custom_fields'] = $customFieldsPayload;
            }

            $updatedDomainCard = $this->cardRepository->update($command->cardId, $payload);

            return Card::with(['assignees', 'checklists'])->findOrFail($updatedDomainCard->id);
        });
    }
}
