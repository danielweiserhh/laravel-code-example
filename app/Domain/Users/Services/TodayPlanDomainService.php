<?php

declare(strict_types=1);

namespace App\Domain\Users\Services;

use App\Domain\Boards\Repositories\CardRepositoryInterface;

final class TodayPlanDomainService
{
    public function __construct(
        private readonly CardRepositoryInterface $cardRepository
    ) {}

    public function generateHeuristicPlan(int $userId): array
    {
        $cards = $this->cardRepository->getActiveCardsForUser($userId);
        $bigThree = $this->selectBigThree($cards);

        return [
            'big_three' => $bigThree,
            'note_for_user' => $this->generateNoteForUser($bigThree),
        ];
    }

    private function selectBigThree(array $cards): array
    {
        usort($cards, function ($a, $b) {
            if ($a->dueDate !== null && $b->dueDate !== null) {
                $cmp = strcmp($a->dueDate, $b->dueDate);
                if ($cmp !== 0) {
                    return $cmp;
                }
            } elseif ($a->dueDate !== null) {
                return -1; 
            } elseif ($b->dueDate !== null) {
                return 1;
            }

            $energyOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
            $aEnergy = $energyOrder[$a->energyLevel ?? ''] ?? 0;
            $bEnergy = $energyOrder[$b->energyLevel ?? ''] ?? 0;
            if ($aEnergy !== $bEnergy) {
                return $bEnergy <=> $aEnergy;
            }

            return strcmp($a->createdAt, $b->createdAt);
        });

        $selected = array_slice($cards, 0, 3);

        return array_map(function ($card): array {
            return [
                'id' => $card->id,
                'title' => $card->title,
                'board' => 'Без доски',
                'due_date' => $card->dueDate ? (new \DateTime($card->dueDate))->format('Y-m-d') : null,
                'energy_level' => $card->energyLevel,
            ];
        }, $selected);
    }

    private function generateNoteForUser(array $bigThree): string
    {
        if (empty($bigThree)) {
            return 'Выбери главное и начни с малого.';
        }

        if (count($bigThree) === 1) {
            return 'Одна задача — один фокус. Ты справишься!';
        }

        return 'Выбери главное и начни с малого.';
    }
}
