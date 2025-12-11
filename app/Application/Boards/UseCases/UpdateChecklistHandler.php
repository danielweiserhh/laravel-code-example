<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

use App\Domain\Boards\Repositories\ChecklistRepositoryInterface;
use App\Models\Checklist;
use Illuminate\Support\Facades\DB;

final class UpdateChecklistHandler
{
    public function __construct(
        private readonly ChecklistRepositoryInterface $checklistRepository
    ) {}

    public function handle(UpdateChecklistCommand $command): Checklist
    {
        return DB::transaction(function () use ($command) {
            $checklist = $this->checklistRepository->findOrFail($command->checklistId);

            if ($command->title !== null) {
                $checklist->title = $command->title;
                $this->checklistRepository->save($checklist);
            }

            return $checklist->load('items');
        });
    }
}
