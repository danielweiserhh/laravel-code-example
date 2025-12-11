<?php

declare(strict_types=1);

namespace App\Application\Routines\Queries;

use App\Domain\Routines\Repositories\RoutineExecutionRepositoryInterface;
use App\Domain\Routines\Repositories\RoutineRepositoryInterface;
use Illuminate\Support\Carbon;

final class GetRoutinesWithProgressHandler
{
    public function __construct(
        private readonly RoutineRepositoryInterface $routineRepository,
        private readonly RoutineExecutionRepositoryInterface $executionRepository
    ) {}

    public function handle(GetRoutinesWithProgressQuery $query): array
    {
        $date = $query->date ?? Carbon::today()->toDateString();

        $routines = $this->routineRepository->getAllForUser($query->userId);

        $executions = $this->executionRepository->findByUserAndDate($query->userId, $date);
        $executionsMap = [];
        foreach ($executions as $execution) {
            $executionsMap[$execution->routineId] = $execution;
        }

        return array_map(function ($routine) use ($executionsMap) {
            $routineData = $routine->toArray();
            $execution = $executionsMap[$routine->id] ?? null;

            $routineData['today_progress'] = $this->buildProgressData($execution);

            return $routineData;
        }, $routines);
    }

    private function buildProgressData(?\App\Domain\Routines\ValueObjects\RoutineExecution $execution): array
    {
        if ($execution === null) {
            return [
                'completed_steps' => [],
                'is_completed' => false,
                'started_at' => null,
                'completed_at' => null,
            ];
        }

        return [
            'completed_steps' => $execution->completedSteps,
            'is_completed' => $execution->isCompleted,
            'started_at' => $execution->startedAt,
            'completed_at' => $execution->completedAt,
        ];
    }
}
