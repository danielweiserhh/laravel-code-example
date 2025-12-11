<?php

declare(strict_types=1);

namespace App\Domain\FocusSessions\Services;

use App\Domain\Exceptions\InvalidDurationException;
use App\Domain\Exceptions\InvalidStatusTransitionException;
use App\Domain\FocusSessions\ValueObjects\FocusSession;

final class FocusSessionDomainService
{
    private const MIN_DURATION_MINUTES = 1;
    private const MAX_DURATION_MINUTES = 480;
    private const DEFAULT_DURATION_MINUTES = 25;
    private const VALID_STATUSES = ['pending', 'active', 'paused', 'completed'];

    public function validateDuration(int $durationMinutes): void
    {
        if ($durationMinutes < self::MIN_DURATION_MINUTES || $durationMinutes > self::MAX_DURATION_MINUTES) {
            throw new InvalidDurationException(
                $durationMinutes,
                self::MIN_DURATION_MINUTES,
                self::MAX_DURATION_MINUTES
            );
        }
    }

    public function validateVideoLink(?string $videoLink): void
    {
        if ($videoLink === null || $videoLink === '') {
            return;
        }

        if (! filter_var($videoLink, FILTER_VALIDATE_URL)) {
            throw new \DomainException('Некорректная ссылка на видеозвонок');
        }
    }

    public function validateStatusTransition(FocusSession $session, string $newStatus): void
    {
        $currentStatus = $session->status;

        $allowedTransitions = [
            'pending' => ['active'],
            'active' => ['paused', 'completed'],
            'paused' => ['active', 'completed'],
            'completed' => [],
        ];

        if (! isset($allowedTransitions[$currentStatus])) {
            throw new \DomainException("Неизвестный статус сессии: {$currentStatus}");
        }

        if (! in_array($newStatus, $allowedTransitions[$currentStatus], true)) {
            throw new InvalidStatusTransitionException($currentStatus, $newStatus);
        }
    }

    public function canStartNewSession(array $userActiveSessions): bool
    {
        foreach ($userActiveSessions as $session) {
            if ($session->isActive()) {
                return false;
            }
        }

        return true;
    }

    public function calculateActualDuration(string $startedAt, string $endedAt): int
    {
        $start = new \DateTimeImmutable($startedAt);
        $end = new \DateTimeImmutable($endedAt);

        $diffSeconds = $end->getTimestamp() - $start->getTimestamp();

        return max(0, (int) ceil($diffSeconds / 60));
    }

    public function getDefaultDuration(): int
    {
        return self::DEFAULT_DURATION_MINUTES;
    }

    public function getValidStatuses(): array
    {
        return self::VALID_STATUSES;
    }
}
