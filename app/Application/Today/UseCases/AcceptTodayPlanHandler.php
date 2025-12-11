<?php

declare(strict_types=1);

namespace App\Application\Today\UseCases;

use App\Domain\AI\Repositories\AIJobRepositoryInterface;
use App\Domain\Today\Services\TodayDomainService;
use App\Domain\Users\Repositories\UserDailyPlanRepositoryInterface;
use App\Domain\Users\Repositories\UserRepositoryInterface;

final class AcceptTodayPlanHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserDailyPlanRepositoryInterface $dailyPlanRepository,
        private readonly AIJobRepositoryInterface $aiJobRepository,
        private readonly TodayDomainService $todayDomainService
    ) {}

    
    public function handle(AcceptTodayPlanCommand $command): array
    {
        $user = $this->userRepository->findOrFail($command->userId);

        
        $userTimezone = $user->timezone ?? 'UTC';

        $normalized = $this->normalizePlan([
            'big_three' => $command->bigThree,
            'note_for_user' => $command->noteForUser,
        ]);

        
        $todayDate = $this->todayDomainService->getTodayDate($userTimezone);

        $dailyPlan = $this->dailyPlanRepository->updateOrCreate(
            [
                'user_id' => $user->id,
                'plan_date' => $todayDate,
            ],
            [
                'big_three' => $normalized['big_three'],
                'note_for_user' => $normalized['note_for_user'],
                'ai_job_id' => $command->jobId,
            ]
        );

        if ($command->jobId) {
            $aiJob = $this->aiJobRepository->find($command->jobId);
            if ($aiJob && $aiJob->user_id === $user->id) {
                $aiJob->result = $normalized;
                $this->aiJobRepository->save($aiJob);
            }
        }

        return [
            'big_three' => $dailyPlan->big_three ?? [],
            'note_for_user' => $dailyPlan->note_for_user,
        ];
    }

    
    private function normalizePlan(array $plan): array
    {
        $normalizeCards = static function (array $cards): array {
            $result = [];
            $seen = [];

            foreach ($cards as $card) {
                $id = (int) data_get($card, 'id');
                if ($id <= 0) {
                    continue;
                }

                if (isset($seen[$id])) {
                    continue;
                }

                $entry = ['id' => $id];

                $title = data_get($card, 'title');
                if (is_string($title) && $title !== '') {
                    $entry['title'] = $title;
                }

                $board = data_get($card, 'board');
                if (is_string($board) && $board !== '') {
                    $entry['board'] = $board;
                }

                $dueDate = data_get($card, 'due_date');
                if (is_string($dueDate) && $dueDate !== '') {
                    $entry['due_date'] = $dueDate;
                }

                $energyLevel = data_get($card, 'energy_level');
                if (is_string($energyLevel) && $energyLevel !== '') {
                    $entry['energy_level'] = $energyLevel;
                }

                $result[] = $entry;
                $seen[$id] = true;
            }

            return $result;
        };

        $note = $plan['note_for_user'] ?? null;
        if (! is_string($note) || $note === '') {
            $note = null;
        }

        $bigThree = array_slice($normalizeCards($plan['big_three'] ?? []), 0, 3);

        return [
            'big_three' => $bigThree,
            'note_for_user' => $note,
        ];
    }
}
