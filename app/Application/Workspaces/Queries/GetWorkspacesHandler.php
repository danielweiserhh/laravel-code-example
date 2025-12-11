<?php

declare(strict_types=1);

namespace App\Application\Workspaces\Queries;

use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Users\UserMapper;
use App\Models\User;

final class GetWorkspacesHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function handle(GetWorkspacesQuery $query): array
    {
        $userEntity = $this->userRepository->findOrFail($query->userId);
        $user = UserMapper::toModel($userEntity);

        return $user->workspaces()
            ->with(['members'])
            ->get()
            ->toArray();
    }
}
