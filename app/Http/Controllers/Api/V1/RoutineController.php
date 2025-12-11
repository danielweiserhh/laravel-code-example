<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Routines\Queries\GetRoutinesWithProgressHandler;
use App\Application\Routines\Queries\GetRoutinesWithProgressQuery;
use App\Application\Routines\UseCases\CreateRoutineCommand;
use App\Application\Routines\UseCases\CreateRoutineHandler;
use App\Application\Routines\UseCases\DeleteRoutineCommand;
use App\Application\Routines\UseCases\DeleteRoutineHandler;
use App\Application\Routines\UseCases\UpdateRoutineCommand;
use App\Application\Routines\UseCases\UpdateRoutineHandler;
use App\Domain\Routines\Repositories\RoutineRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRoutineRequest;
use App\Http\Requests\Api\V1\UpdateRoutineRequest;
use App\Models\Routine;
use App\Models\RoutineExecution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RoutineController extends Controller
{
    public function __construct(
        protected CreateRoutineHandler $createRoutine,
        protected UpdateRoutineHandler $updateRoutine,
        protected DeleteRoutineHandler $deleteRoutine,
        protected GetRoutinesWithProgressHandler $getRoutinesWithProgress,
        protected RoutineRepositoryInterface $routineRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetRoutinesWithProgressQuery(userId: $request->user()->id);
        $data = $this->getRoutinesWithProgress->handle($query);

        return $this->success($data);
    }

    public function store(StoreRoutineRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $command = new CreateRoutineCommand(
            userId: $request->user()->id,
            name: $validated['name'],
            workspaceId: isset($validated['workspace_id']) ? (int) $validated['workspace_id'] : null,
            description: $validated['description'] ?? null,
            type: $validated['type'] ?? null,
            settings: $validated['settings'] ?? null,
            steps: $validated['steps'] ?? null,
        );

        $routine = $this->createRoutine->handle($command);

        return $this->created($routine->toArray(), 'Рутина создана');
    }

    public function show(Request $request, Routine $routine): JsonResponse
    {
        $this->authorize('view', $routine);
        
        $domainRoutine = $this->routineRepository->findOrFail($routine->id);

        return $this->success($domainRoutine->toArray());
    }

    public function update(UpdateRoutineRequest $request, Routine $routine): JsonResponse
    {
        $this->authorize('update', $routine);

        $validated = $request->validated();

        $command = new UpdateRoutineCommand(
            routineId: $routine->id,
            name: $validated['name'] ?? null,
            description: $validated['description'] ?? null,
            type: $validated['type'] ?? null,
            settings: $validated['settings'] ?? null,
        );

        $updatedRoutine = $this->updateRoutine->handle($command);

        return $this->success($updatedRoutine->toArray(), 'Рутина обновлена');
    }

    public function destroy(Request $request, Routine $routine): JsonResponse
    {
        $this->authorize('delete', $routine);

        $command = new DeleteRoutineCommand(
            routineId: $routine->id,
        );

        $this->deleteRoutine->handle($command);

        return $this->success(null, 'Рутина удалена');
    }

    
    public function completeStep(Request $request, Routine $routine): JsonResponse
    {
        $this->authorize('view', $routine);

        $validated = $request->validate([
            'step_position' => 'required|integer|min:0',
        ]);

        $today = Carbon::today()->toDateString();
        $userId = $request->user()->id;

        $execution = RoutineExecution::firstOrCreate(
            [
                'routine_id' => $routine->id,
                'user_id' => $userId,
                'date' => $today,
            ],
            [
                'completed_steps' => [],
                'started_at' => now(),
            ]
        );

        $execution->completeStep((int) $validated['step_position']);
        
        $totalSteps = $routine->steps()->count();
        
        if ($execution->getCompletedCount() >= $totalSteps) {
            $execution->is_completed = true;
            $execution->completed_at = now();
        }

        $execution->save();

        return $this->success([
            'completed_steps' => $execution->completed_steps,
            'is_completed' => $execution->is_completed,
            'started_at' => $execution->started_at?->toIso8601String(),
            'completed_at' => $execution->completed_at?->toIso8601String(),
        ]);
    }

    
    public function resetProgress(Request $request, Routine $routine): JsonResponse
    {
        $this->authorize('view', $routine);

        $today = Carbon::today()->toDateString();
        $userId = $request->user()->id;

        RoutineExecution::where('routine_id', $routine->id)
            ->where('user_id', $userId)
            ->where('date', $today)
            ->delete();

        return $this->success(null, 'Прогресс сброшен');
    }
}
