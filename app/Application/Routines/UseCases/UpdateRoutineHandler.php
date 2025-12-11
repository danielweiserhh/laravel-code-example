<?php

declare(strict_types=1);

namespace App\Application\Routines\UseCases;

use App\Domain\Routines\Repositories\RoutineRepositoryInterface;
use App\Domain\Routines\Services\RoutineDomainService;
use App\Domain\Routines\ValueObjects\Routine as DomainRoutine;
use Illuminate\Support\Facades\DB;

final class UpdateRoutineHandler
{
    public function __construct(
        private readonly RoutineRepositoryInterface $routineRepository,
        private readonly RoutineDomainService $routineDomainService
    ) {}

    public function handle(UpdateRoutineCommand $command): DomainRoutine
    {
        return DB::transaction(function () use ($command) {
            
            $this->routineRepository->findOrFail($command->routineId);

            
            if ($command->name !== null) {
                $this->routineDomainService->validateName($command->name);
            }

            if ($command->description !== null) {
                $this->routineDomainService->validateDescription($command->description);
            }

            if ($command->type !== null) {
                $this->routineDomainService->validateType($command->type);
            }

            $payload = [];

            if ($command->name !== null) {
                $payload['name'] = $command->name;
            }
            if ($command->description !== null) {
                $payload['description'] = $command->description;
            }
            if ($command->type !== null) {
                $payload['type'] = $command->type;
            }
            if ($command->settings !== null) {
                $payload['settings'] = $command->settings;
            }

            if (empty($payload)) {
                return $this->routineRepository->findOrFail($command->routineId);
            }

            return $this->routineRepository->update($command->routineId, $payload);
        });
    }
}
