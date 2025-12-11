<?php

declare(strict_types=1);

namespace App\Application\Boards\UseCases;

use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Domain\Boards\Repositories\ListRepositoryInterface;
use App\Domain\Boards\Services\CardDomainService;
use App\Domain\Boards\ValueObjects\CardPositionData;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class MoveCardHandler
{
    public function __construct(
        private readonly CardRepositoryInterface $cardRepository,
        private readonly ListRepositoryInterface $listRepository,
        private readonly CardDomainService $cardDomainService
    ) {}

    public function handle(MoveCardCommand $command): void
    {
        DB::transaction(function () use ($command) {
            
            $domainCard = $this->cardRepository->findOrFail($command->cardId);
            $oldListId = $domainCard->listId;
            $newListId = $command->toListId;
            $newPosition = $command->toPosition;

            
            $toList = $this->listRepository->find($newListId);
            if (! $toList) {
                throw ValidationException::withMessages(['toListId' => 'List not found']);
            }

            
            if ($oldListId !== $newListId) {
                $this->cardRepository->update($command->cardId, [
                    'list_id' => $newListId,
                    'board_id' => $toList->boardId,
                ]);
            }

            
            if ($newPosition !== null) {
                $finalPosition = $this->reorderCards($newListId, $command->cardId, $newPosition);

                $this->cardRepository->update($command->cardId, [
                    'position' => $finalPosition,
                ]);
            }
        });
    }

    private function reorderCards(int $listId, int $cardId, int $newPosition): int
    {
        
        $cards = $this->cardRepository->getByList($listId);

        
        $cardPositions = array_map(fn ($card) => new CardPositionData(
            id: $card->id,
            position: $card->position
        ), $cards);

        
        if (empty($cardPositions)) {
            return 0;
        }

        
        $positionMap = $this->cardDomainService->calculateNewPositions($cardPositions, $cardId, $newPosition);

        
        $otherCardsCount = count($cardPositions) - 1;
        $finalPosition = min($newPosition, max(0, $otherCardsCount));

        
        if (! empty($positionMap)) {
            $this->cardRepository->updatePositions($positionMap);
        }

        return $finalPosition;
    }
}
