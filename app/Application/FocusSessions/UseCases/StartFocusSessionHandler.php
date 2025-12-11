<?php

declare(strict_types=1);

namespace App\Application\FocusSessions\UseCases;

use App\Domain\FocusSessions\Repositories\FocusSessionRepositoryInterface;
use App\Domain\FocusSessions\Services\FocusSessionDomainService;
use App\Domain\FocusSessions\ValueObjects\FocusSession as DomainFocusSession;
use Illuminate\Support\Facades\DB;

final class StartFocusSessionHandler
{
    public function __construct(
        private readonly FocusSessionRepositoryInterface $sessionRepository,
        private readonly FocusSessionDomainService $focusSessionDomainService
    ) {}

    public function handle(StartFocusSessionCommand $command): DomainFocusSession
    {
        return DB::transaction(function () use ($command) {
            $session = $this->sessionRepository->findOrFail($command->sessionId);

            
            $this->focusSessionDomainService->validateStatusTransition($session, 'active');

            return $this->sessionRepository->update($command->sessionId, [
                'status' => 'active',
                'started_at' => now()->toIso8601String(),
            ]);
        });
    }
}
