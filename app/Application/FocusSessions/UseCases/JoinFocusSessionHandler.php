<?php

declare(strict_types=1);

namespace App\Application\FocusSessions\UseCases;

use App\Domain\FocusSessions\Repositories\FocusSessionRepositoryInterface;
use App\Domain\FocusSessions\ValueObjects\FocusSession as DomainFocusSession;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class JoinFocusSessionHandler
{
    public function __construct(
        private readonly FocusSessionRepositoryInterface $sessionRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function handle(JoinFocusSessionCommand $command): DomainFocusSession
    {
        return DB::transaction(function () use ($command) {
            $session = $this->sessionRepository->findOrFail($command->sessionId);
            $this->userRepository->findOrFail($command->userId);

            if (! $session->isGroup) {
                throw new \DomainException('Это не групповая сессия');
            }

            $this->sessionRepository->addParticipant($command->sessionId, $command->userId);

            return $this->sessionRepository->findOrFail($command->sessionId);
        });
    }
}
