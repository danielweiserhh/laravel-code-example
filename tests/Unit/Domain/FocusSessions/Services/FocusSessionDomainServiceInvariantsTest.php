<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\FocusSessions\Services;

use App\Domain\Exceptions\InvalidDurationException;
use App\Domain\Exceptions\InvalidStatusTransitionException;
use App\Domain\FocusSessions\Services\FocusSessionDomainService;
use App\Domain\FocusSessions\ValueObjects\FocusSession;
use PHPUnit\Framework\TestCase;

final class FocusSessionDomainServiceInvariantsTest extends TestCase
{
    private FocusSessionDomainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FocusSessionDomainService();
    }

    public function test_cannot_validate_duration_below_minimum(): void
    {
        $this->expectException(InvalidDurationException::class);
        $this->expectExceptionMessage('Duration 0 is invalid');

        $this->service->validateDuration(0);
    }

    public function test_cannot_validate_duration_above_maximum(): void
    {
        $this->expectException(InvalidDurationException::class);
        $this->expectExceptionMessage('Duration 481 is invalid');

        $this->service->validateDuration(481);
    }

    public function test_can_validate_valid_duration(): void
    {
        $this->service->validateDuration(25);
        $this->service->validateDuration(1);
        $this->service->validateDuration(480);
    }

    public function test_cannot_transition_from_completed_to_active(): void
    {
        $session = $this->createSession('completed');

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage('Cannot transition from status "completed" to "active"');

        $this->service->validateStatusTransition($session, 'active');
    }

    public function test_cannot_transition_from_pending_to_completed(): void
    {
        $session = $this->createSession('pending');

        $this->expectException(InvalidStatusTransitionException::class);

        $this->service->validateStatusTransition($session, 'completed');
    }

    public function test_can_transition_from_pending_to_active(): void
    {
        $session = $this->createSession('pending');
        $this->service->validateStatusTransition($session, 'active');
    }

    public function test_can_transition_from_active_to_paused(): void
    {
        $session = $this->createSession('active');
        $this->service->validateStatusTransition($session, 'paused');
    }

    private function createSession(string $status): FocusSession
    {
        return new FocusSession(
            id: 1,
            userId: 1,
            cardId: null,
            durationMinutes: 25,
            status: $status,
            startedAt: null,
            endedAt: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }
}
