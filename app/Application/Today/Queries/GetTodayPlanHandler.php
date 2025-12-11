<?php

declare(strict_types=1);

namespace App\Application\Today\Queries;

use App\Domain\Boards\Repositories\BoardRepositoryInterface;
use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Domain\Boards\Repositories\ListRepositoryInterface;
use App\Domain\Today\Services\TodayDomainService;
use App\Domain\Users\Repositories\UserDailyPlanRepositoryInterface;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Domain\Users\Services\TodayPlanDomainService;
use App\Infrastructure\Persistence\Users\UserMapper;
use App\Models\Card;
use App\Models\User;

final class GetTodayPlanHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly BoardRepositoryInterface $boardRepository,
        private readonly ListRepositoryInterface $listRepository,
        private readonly CardRepositoryInterface $cardRepository,
        private readonly UserDailyPlanRepositoryInterface $dailyPlanRepository,
        private readonly TodayPlanDomainService $todayPlanService,
        private readonly TodayDomainService $todayDomainService
    ) {}

    
    public function handle(GetTodayPlanQuery $query): array
    {
        $userEntity = $this->userRepository->findOrFail($query->userId);
        $user = UserMapper::toModel($userEntity);

        $userTimezone = 'UTC';

        
        $todayList = $this->getTodayList($user);

        if ($todayList) {
            $cards = $this->getCardsFromList($todayList->id, $userTimezone);

            return [
                'plan' => [
                    'big_three' => $cards,
                    'note_for_user' => null,
                ],
                'source' => 'list',
            ];
        }

        
        $todayDate = $this->todayDomainService->getTodayDate($userTimezone);
        $savedPlan = $this->dailyPlanRepository->findForUserAndDate($userEntity, $todayDate);

        if ($savedPlan) {
            return [
                'plan' => [
                    'big_three' => $savedPlan->big_three ?? [],
                    'note_for_user' => $savedPlan->note_for_user,
                ],
                'source' => 'saved',
            ];
        }

        
        $plan = $this->todayPlanService->generateHeuristicPlan($user->id);

        return [
            'plan' => $plan,
            'source' => 'heuristic',
        ];
    }

    private function getTodayList(User $user): ?\App\Models\ListModel
    {
        
        $domainBoard = $this->boardRepository->findByNameInUserWorkspaces($user->id, 'Моя доска');

        if (! $domainBoard) {
            return null;
        }

        
        $domainLists = $this->listRepository->getByBoard($domainBoard->id);
        foreach ($domainLists as $domainList) {
            if ($domainList->name === 'Сегодня' && ! $domainList->isArchived) {
                return \App\Models\ListModel::findOrFail($domainList->id);
            }
        }

        return null;
    }

    
    private function getCardsFromList(int $listId, string $userTimezone): array
    {
        
        $domainCards = $this->cardRepository->getByList($listId);

        
        $result = [];
        foreach ($domainCards as $domainCard) {
            if (! $domainCard->isCompleted) {
                $card = Card::findOrFail($domainCard->id);

                
                $deadlineStatus = null;
                if ($domainCard->dueDate) {
                    $deadlineStatus = $this->todayDomainService->getDeadlineStatus(
                        $domainCard->dueDate,
                        $userTimezone
                    );
                }

                $result[] = [
                    'id' => $domainCard->id,
                    'title' => $domainCard->title,
                    'board' => $card->board?->name ?? 'Без доски',
                    'due_date' => $domainCard->dueDate ? Carbon::parse($domainCard->dueDate)->toDateString() : null,
                    'energy_level' => $domainCard->energyLevel,
                    'deadline_status' => $deadlineStatus,
                    'position' => $domainCard->position,
                ];
            }
        }

        
        usort($result, function ($a, $b) {
            if ($a['position'] !== $b['position']) {
                return $a['position'] <=> $b['position'];
            }

            return 0;
        });

        return $result;
    }
}
