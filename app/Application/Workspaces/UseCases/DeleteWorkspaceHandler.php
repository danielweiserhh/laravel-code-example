<?php

declare(strict_types=1);

namespace App\Application\Workspaces\UseCases;

use App\Domain\Workspaces\Repositories\WorkspaceRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class DeleteWorkspaceHandler
{
    public function __construct(
        private readonly WorkspaceRepositoryInterface $workspaceRepository
    ) {}

    public function handle(DeleteWorkspaceCommand $command): void
    {
        DB::transaction(function () use ($command) {
            $workspace = $this->workspaceRepository->findOrFail($command->workspaceId);
            $this->workspaceRepository->delete($workspace);
        });
    }
}
