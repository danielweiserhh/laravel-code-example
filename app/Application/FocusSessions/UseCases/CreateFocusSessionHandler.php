<?php

declare(strict_types=1);

namespace App\Application\FocusSessions\UseCases;

use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Domain\FocusSessions\Repositories\FocusSessionRepositoryInterface;
use App\Domain\FocusSessions\Services\FocusSessionDomainService;
use App\Domain\FocusSessions\ValueObjects\FocusSession as DomainFocusSession;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class CreateFocusSessionHandler
{
    public function __construct(
        private readonly FocusSessionRepositoryInterface $sessionRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly CardRepositoryInterface $cardRepository,
        private readonly FocusSessionDomainService $focusSessionDomainService
    ) {}

    public function handle(CreateFocusSessionCommand $command): DomainFocusSession
    {
        return DB::transaction(function () use ($command) {
            $this->userRepository->findOrFail($command->userId);

            
            $this->focusSessionDomainService->validateDuration($command->durationMinutes);

            
            if ($command->videoLink !== null) {
                $this->focusSessionDomainService->validateVideoLink($command->videoLink);
            }

            
            $cardId = null;
            if ($command->cardId !== null) {
                $card = $this->cardRepository->findOrFail($command->cardId);
                $cardId = $card->id;
            }

            
            $userId = new UserId($command->userId);
            $activeSessions = $this->sessionRepository->findActiveByUserId($userId);
            if (! $this->focusSessionDomainService->canStartNewSession($activeSessions)) {
                throw new \DomainException('У вас уже есть активная сессия фокуса');
            }

            return $this->sessionRepository->create([
                'card_id' => $cardId,
                'duration_minutes' => $command->durationMinutes,
                'is_group' => $command->isGroup,
                'video_link' => $command->videoLink,
            ], $userId);
        });
    }
}
