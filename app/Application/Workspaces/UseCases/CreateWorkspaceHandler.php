<?php

declare(strict_types=1);

namespace App\Application\Workspaces\UseCases;

use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Enums\WorkspaceRole;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateWorkspaceHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function handle(CreateWorkspaceCommand $command): Workspace
    {
        return DB::transaction(function () use ($command) {
            $user = $this->userRepository->findOrFail($command->userId);

            $workspace = Workspace::create([
                'name' => $command->name,
                'description' => $command->description,
                'slug' => Str::slug($command->name),
                'settings' => $command->settings ?? [],
            ]);

            $workspace->members()->attach($user->id, [
                'role' => WorkspaceRole::OWNER->value,
            ]);

            return $workspace->load('members');
        });
    }
}
