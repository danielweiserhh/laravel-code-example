<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\FocusSessions\Services;

use App\Domain\FocusSessions\Services\FocusSessionDomainService;
use App\Domain\FocusSessions\ValueObjects\FocusSession;
use PHPUnit\Framework\TestCase;

class FocusSessionDomainServiceTest extends TestCase
{
    private FocusSessionDomainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FocusSessionDomainService;
    }

    public function test_validates_valid_duration(): void
    {
        $this->service->validateDuration(25);
    }

    public function test_rejects_zero_duration(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Длительность сессии должна быть не менее 1 минуты');

        $this->service->validateDuration(0);
    }

    public function test_rejects_too_long_duration(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Длительность сессии не может превышать 8 часов');

        $this->service->validateDuration(481);
    }

    public function test_validates_valid_video_link(): void
    {
        $this->service->validateVideoLink('https://meet.google.com/abc-defg-hij');
    }

    public function test_validates_null_video_link(): void
    {
        $this->service->validateVideoLink(null);
    }

    public function test_validates_empty_video_link(): void
    {
        $this->service->validateVideoLink('');
    }

    public function test_rejects_invalid_video_link(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Некорректная ссылка на видеозвонок');

        $this->service->validateVideoLink('not-a-url');
    }

    public function test_allows_pending_to_active_transition(): void
    {
        $session = $this->createSession(['status' => 'pending']);

        $this->service->validateStatusTransition($session, 'active');
    }

    public function test_allows_active_to_paused_transition(): void
    {
        $session = $this->createSession(['status' => 'active']);

        $this->service->validateStatusTransition($session, 'paused');
    }

    public function test_allows_active_to_completed_transition(): void
    {
        $session = $this->createSession(['status' => 'active']);

        $this->service->validateStatusTransition($session, 'completed');
    }

    public function test_allows_paused_to_active_transition(): void
    {
        $session = $this->createSession(['status' => 'paused']);

        $this->service->validateStatusTransition($session, 'active');
    }

    public function test_allows_paused_to_completed_transition(): void
    {
        $session = $this->createSession(['status' => 'paused']);

        $this->service->validateStatusTransition($session, 'completed');
    }

    public function test_rejects_pending_to_completed_transition(): void
    {
        $session = $this->createSession(['status' => 'pending']);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("Невозможно перевести сессию из статуса 'pending' в 'completed'");

        $this->service->validateStatusTransition($session, 'completed');
    }

    public function test_rejects_completed_to_any_transition(): void
    {
        $session = $this->createSession(['status' => 'completed']);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("Невозможно перевести сессию из статуса 'completed' в 'active'");

        $this->service->validateStatusTransition($session, 'active');
    }

    public function test_can_start_new_session_when_no_active(): void
    {
        $sessions = [
            $this->createSession(['status' => 'pending']),
            $this->createSession(['status' => 'completed']),
        ];

        $this->assertTrue($this->service->canStartNewSession($sessions));
    }

    public function test_cannot_start_new_session_when_active_exists(): void
    {
        $sessions = [
            $this->createSession(['status' => 'pending']),
            $this->createSession(['status' => 'active']),
        ];

        $this->assertFalse($this->service->canStartNewSession($sessions));
    }

    public function test_calculates_actual_duration(): void
    {
        $startedAt = '2024-01-01T10:00:00+00:00';
        $endedAt = '2024-01-01T10:30:00+00:00';

        $duration = $this->service->calculateActualDuration($startedAt, $endedAt);

        $this->assertEquals(30, $duration);
    }

    public function test_returns_default_duration(): void
    {
        $this->assertEquals(25, $this->service->getDefaultDuration());
    }

    public function test_returns_valid_statuses(): void
    {
        $statuses = $this->service->getValidStatuses();

        $this->assertContains('pending', $statuses);
        $this->assertContains('active', $statuses);
        $this->assertContains('paused', $statuses);
        $this->assertContains('completed', $statuses);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createSession(array $overrides = []): FocusSession
    {
        $defaults = [
            'id' => 1,
            'user_id' => 1,
            'card_id' => null,
            'duration_minutes' => 25,
            'started_at' => null,
            'ended_at' => null,
            'status' => 'pending',
            'is_group' => false,
            'video_link' => null,
            'created_at' => '2024-01-01T00:00:00+00:00',
            'updated_at' => '2024-01-01T00:00:00+00:00',
        ];

        return FocusSession::fromArray(array_merge($defaults, $overrides));
    }
}
