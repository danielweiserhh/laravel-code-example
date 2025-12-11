<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

use App\Domain\Boards\Repositories\ChecklistRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class DeleteChecklistHandler
{
    public function __construct(
        private readonly ChecklistRepositoryInterface $checklistRepository
    ) {}

    public function handle(DeleteChecklistCommand $command): void
    {
        DB::transaction(function () use ($command) {
            $checklist = $this->checklistRepository->findOrFail($command->checklistId);
            $this->checklistRepository->delete($checklist);
        });
    }
}
