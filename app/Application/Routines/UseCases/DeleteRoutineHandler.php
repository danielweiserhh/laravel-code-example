<?php

declare(strict_types=1);

namespace App\Application\Routines\UseCases;

use App\Domain\Routines\Repositories\RoutineRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class DeleteRoutineHandler
{
    public function __construct(
        private readonly RoutineRepositoryInterface $routineRepository
    ) {}

    public function handle(DeleteRoutineCommand $command): void
    {
        DB::transaction(function () use ($command) {
            
            $this->routineRepository->findOrFail($command->routineId);
            $this->routineRepository->delete($command->routineId);
        });
    }
}
