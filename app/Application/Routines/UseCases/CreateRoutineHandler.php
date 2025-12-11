<?php

declare(strict_types=1);

namespace App\Application\Routines\UseCases;

use App\Domain\Routines\Repositories\RoutineRepositoryInterface;
use App\Domain\Routines\Services\RoutineDomainService;
use App\Domain\Routines\ValueObjects\Routine as DomainRoutine;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class CreateRoutineHandler
{
    public function __construct(
        private readonly RoutineRepositoryInterface $routineRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly RoutineDomainService $routineDomainService
    ) {}

    public function handle(CreateRoutineCommand $command): DomainRoutine
    {
        return DB::transaction(function () use ($command) {
            $user = $this->userRepository->findOrFail($command->userId);

            
            $this->routineDomainService->validateName($command->name);

            if ($command->description !== null) {
                $this->routineDomainService->validateDescription($command->description);
            }

            if ($command->type !== null) {
                $this->routineDomainService->validateType($command->type);
            }

            
            if ($command->steps !== null) {
                foreach ($command->steps as $step) {
                    $this->routineDomainService->validateStepTitle($step['title']);
                    if (isset($step['duration_minutes'])) {
                        $this->routineDomainService->validateStepDuration($step['duration_minutes']);
                    }
                }
            }

            
            $workspaceId = $command->workspaceId;

            $userId = new UserId($command->userId);

            return $this->routineRepository->create([
                'workspace_id' => $workspaceId,
                'name' => $command->name,
                'description' => $command->description,
                'type' => $command->type ?? 'custom',
                'settings' => $command->settings ?? [],
                'steps' => $command->steps,
            ], $userId);
        });
    }
}
