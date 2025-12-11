<?php

declare(strict_types=1);

namespace App\Application\AI\UseCases;

use App\Domain\Boards\Repositories\BoardRepositoryInterface;
use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Enums\AIJobType;
use App\Models\AIJob;

final class DecomposeCardHandler
{
    public function __construct(
        private readonly CardRepositoryInterface $cardRepository,
        private readonly BoardRepositoryInterface $boardRepository,
        private readonly CreateAIJobHandler $createAIJob
    ) {}

    public function handle(DecomposeCardCommand $command): AIJob
    {
        $domainCard = $this->cardRepository->findOrFailWithBoard($command->cardId);

        $workspaceId = null;
        $board = $this->boardRepository->find($domainCard->boardId);
        
        if ($board !== null) {
            $workspaceId = $board->workspaceId;
        }

        return $this->createAIJob->handle(
            new CreateAIJobCommand(
                userId: $command->userId,
                workspaceId: $workspaceId,
                type: AIJobType::DECOMPOSE_CARD->value,
                payload: [
                    'card_id' => $domainCard->id,
                    'language' => $command->language,
                ]
            )
        );
    }
}
