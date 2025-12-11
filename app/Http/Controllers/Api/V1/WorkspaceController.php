<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Workspaces\Queries\GetWorkspacesHandler;
use App\Application\Workspaces\Queries\GetWorkspacesQuery;
use App\Application\Workspaces\UseCases\CreateWorkspaceCommand;
use App\Application\Workspaces\UseCases\CreateWorkspaceHandler;
use App\Application\Workspaces\UseCases\DeleteWorkspaceCommand;
use App\Application\Workspaces\UseCases\DeleteWorkspaceHandler;
use App\Application\Workspaces\UseCases\UpdateWorkspaceCommand;
use App\Application\Workspaces\UseCases\UpdateWorkspaceHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWorkspaceRequest;
use App\Http\Requests\Api\V1\UpdateWorkspaceRequest;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function __construct(
        protected CreateWorkspaceHandler $createWorkspace,
        protected UpdateWorkspaceHandler $updateWorkspace,
        protected DeleteWorkspaceHandler $deleteWorkspace,
        protected GetWorkspacesHandler $getWorkspaces
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetWorkspacesQuery(userId: $request->user()->id);
        $workspaces = $this->getWorkspaces->handle($query);

        return $this->success($workspaces);
    }

    public function store(StoreWorkspaceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $command = new CreateWorkspaceCommand(
            userId: $request->user()->id,
            name: $validated['name'],
            description: $validated['description'] ?? null,
            settings: $validated['settings'] ?? null,
        );

        $workspace = $this->createWorkspace->handle($command);

        return $this->created($workspace, 'Рабочее пространство создано');
    }

    public function show(Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $workspace->load(['members', 'boards', 'routines']);

        return $this->success($workspace);
    }

    public function update(UpdateWorkspaceRequest $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validated();

        $command = new UpdateWorkspaceCommand(
            workspaceId: $workspace->id,
            name: $validated['name'] ?? null,
            description: $validated['description'] ?? null,
            settings: $validated['settings'] ?? null,
        );

        $workspace = $this->updateWorkspace->handle($command);

        return $this->success($workspace, 'Рабочее пространство обновлено');
    }

    public function destroy(Workspace $workspace): JsonResponse
    {
        $this->authorize('delete', $workspace);

        $command = new DeleteWorkspaceCommand(
            workspaceId: $workspace->id,
        );

        $this->deleteWorkspace->handle($command);

        return $this->success(null, 'Рабочее пространство удалено');
    }
}
