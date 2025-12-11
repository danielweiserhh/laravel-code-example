<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

use App\Domain\Boards\Entities\ChecklistEntity;
use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Domain\Boards\Repositories\ChecklistRepositoryInterface;
use App\Infrastructure\Persistence\Boards\ChecklistMapper;
use App\Models\Checklist;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class CreateChecklistHandler
{
    public function __construct(
        private readonly ChecklistRepositoryInterface $checklistRepository,
        private readonly CardRepositoryInterface $cardRepository
    ) {}

    public function handle(CreateChecklistCommand $command): Checklist
    {
        return DB::transaction(function () use ($command) {
            $domainCard = $this->cardRepository->findOrFail($command->cardId);

            $now = new DateTimeImmutable();
            $checklistEntity = new ChecklistEntity(
                id: 0,
                cardId: $domainCard->id,
                title: $command->title,
                position: 1,
                createdAt: $now,
                updatedAt: $now,
            );

            $savedEntity = $this->checklistRepository->save($checklistEntity);
            $checklist = ChecklistMapper::toModel($savedEntity);

            if ($command->items !== null && ! empty($command->items)) {
                foreach ($command->items as $index => $item) {
                    $checklist->items()->create([
                        'title' => $item['title'],
                        'position' => $item['position'] ?? ($index + 1),
                        'is_completed' => $item['is_completed'] ?? false,
                    ]);
                }
            }

            return $checklist->load('items');
        });
    }
}
