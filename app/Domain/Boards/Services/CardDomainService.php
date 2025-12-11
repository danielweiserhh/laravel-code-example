<?php

declare(strict_types=1);

namespace App\Domain\Boards\Services;

use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Domain\Boards\ValueObjects\CardData;
use App\Domain\Boards\ValueObjects\CardPositionData;
use App\Domain\Exceptions\InvalidDateRangeException;
use App\Domain\Exceptions\InvalidPositionException;
use App\Domain\Exceptions\InvariantViolationException;
use DateTimeImmutable;

final class CardDomainService
{
    public const ENERGY_LEVEL_MIN = 1;
    
    public const ENERGY_LEVEL_MAX = 5;

    public function __construct(
        private readonly CardRepositoryInterface $cardRepository
    ) {}
    
    public function calculateNextPosition(int $listId, ?int $maxPosition = null): int
    {
        if ($maxPosition !== null) {
            return $maxPosition + 1;
        }

        $maxPosition = $this->cardRepository->getMaxPositionForList($listId);

        return $maxPosition + 1;
    }

    public function validateEnergyLevel(?int $energyLevel): ?int
    {
        if ($energyLevel === null) {
            return null;
        }

        return max(self::ENERGY_LEVEL_MIN, min(self::ENERGY_LEVEL_MAX, $energyLevel));
    }

    public function validateDeadlines(?DateTimeImmutable $startDate, ?DateTimeImmutable $dueDate): void
    {
        if ($startDate !== null && $dueDate !== null && $dueDate < $startDate) {
            throw new InvalidDateRangeException($startDate, $dueDate);
        }
    }

    public function isOverdue(CardData $card): bool
    {
        if ($card->dueDate === null || $card->isCompleted) {
            return false;
        }

        return $card->dueDate < new DateTimeImmutable();
    }

    public function calculateCompletionPercentage(CardData $card): float
    {
        $totalItems = 0;
        $completedItems = 0;

        foreach ($card->checklists as $checklist) {
            foreach ($checklist->items as $item) {
                $totalItems++;
                if ($item->isCompleted) {
                    $completedItems++;
                }
            }
        }

        if ($totalItems === 0) {
            return 0.0;
        }

        return round(($completedItems / $totalItems) * 100, 2);
    }

    public function validatePosition(int $position, int $listId, ?int $excludeCardId = null): void
    {
        if ($position < 0) {
            throw new InvariantViolationException(
                'card.position.non_negative',
                'Card position must be non-negative',
                ['position' => $position, 'list_id' => $listId]
            );
        }

        $maxPosition = $this->cardRepository->getMaxPositionForListExcluding($listId, $excludeCardId);

        if ($position > $maxPosition + 1) {
            throw new InvalidPositionException($position, $maxPosition + 1, 'card');
        }
    }

    public function calculateNewPositions(array $cards, int $movedCardId, int $newPosition): array
    {
        $otherCards = array_filter($cards, fn (CardPositionData $card) => $card->id !== $movedCardId);
        $otherCardsCount = count($otherCards);

        $finalPosition = min($newPosition, $otherCardsCount);
        $positionMap = [];
        $currentPosition = 0;

        foreach ($cards as $card) {
            if ($card->id === $movedCardId) {
                continue;
            }
            if ($currentPosition === $finalPosition) {
                $currentPosition++; 
            }
            $positionMap[$card->id] = $currentPosition;
            $currentPosition++;
        }

        return $positionMap;
    }
}
