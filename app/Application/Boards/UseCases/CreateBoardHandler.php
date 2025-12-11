<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

use App\Domain\Boards\Repositories\BoardRepositoryInterface;
use App\Domain\Boards\Services\BoardDomainService;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Domain\Workspaces\Repositories\WorkspaceRepositoryInterface;
use App\DTOs\BoardCreateDTO;
use App\Enums\BoardRole;
use App\Models\Board;
use Illuminate\Support\Facades\DB;

final class CreateBoardHandler
{
    public function __construct(
        private readonly BoardRepositoryInterface $boardRepository,
        private readonly BoardDomainService $boardDomainService,
        private readonly WorkspaceRepositoryInterface $workspaceRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function handle(CreateBoardCommand $command): Board
    {
        return DB::transaction(function () use ($command) {
            $this->boardDomainService->validateName($command->name);

            $workspace = $this->workspaceRepository->findOrFail($command->workspaceId);
            $user = $this->userRepository->findOrFail($command->userId);

            $privacy = $command->privacy ?? $this->boardDomainService->getDefaultPrivacy()->value;

            $dto = new BoardCreateDTO(
                workspaceId: $workspace->id,
                name: $command->name,
                description: $command->description,
                color: $command->color,
                privacy: $privacy,
                settings: $command->settings ?? []
            );

            $domainBoard = $this->boardRepository->create($dto);

            $boardModel = Board::findOrFail($domainBoard->id);
            $boardModel->members()->attach($user->id, [
                'role' => BoardRole::OWNER->value,
            ]);

            return $boardModel->load('members');
        });
    }
}
