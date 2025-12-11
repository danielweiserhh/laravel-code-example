<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Enums\AIJobType;
use App\Models\AIJob;

final class GenerateTodayPlanHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly CreateAIJobHandler $createAIJob
    ) {}

    public function handle(GenerateTodayPlanCommand $command): AIJob
    {
        $user = $this->userRepository->findOrFail($command->userId);

        $workspaceId = $command->workspaceId;
        
        if (! $workspaceId) {
            
            $workspace = $user->workspaces()->first();
            $workspaceId = $workspace?->id;
        }

        return $this->createAIJob->handle(
            new CreateAIJobCommand(
                userId: $command->userId,
                workspaceId: $workspaceId,
                type: AIJobType::GENERATE_TODAY_PLAN->value,
                payload: [
                    'user_id' => $command->userId,
                    'language' => $command->language,
                ]
            )
        );
    }
}
