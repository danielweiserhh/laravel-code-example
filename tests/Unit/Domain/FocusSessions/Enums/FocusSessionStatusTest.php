<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\FocusSessions\Enums;

use App\Domain\FocusSessions\Enums\FocusSessionStatus;
use PHPUnit\Framework\TestCase;

final class FocusSessionStatusTest extends TestCase
{
    public function test_has_expected_cases(): void
    {
        $this->assertSame('pending', FocusSessionStatus::PENDING->value);
        $this->assertSame('active', FocusSessionStatus::ACTIVE->value);
        $this->assertSame('paused', FocusSessionStatus::PAUSED->value);
        $this->assertSame('completed', FocusSessionStatus::COMPLETED->value);
    }

    public function test_can_transition_from_pending_to_active(): void
    {
        $this->assertTrue(
            FocusSessionStatus::PENDING->canTransitionTo(FocusSessionStatus::ACTIVE)
        );
    }

    public function test_cannot_transition_from_pending_to_completed(): void
    {
        $this->assertFalse(
            FocusSessionStatus::PENDING->canTransitionTo(FocusSessionStatus::COMPLETED)
        );
    }

    public function test_can_transition_from_active_to_paused(): void
    {
        $this->assertTrue(
            FocusSessionStatus::ACTIVE->canTransitionTo(FocusSessionStatus::PAUSED)
        );
    }

    public function test_can_transition_from_active_to_completed(): void
    {
        $this->assertTrue(
            FocusSessionStatus::ACTIVE->canTransitionTo(FocusSessionStatus::COMPLETED)
        );
    }

    public function test_can_transition_from_paused_to_active(): void
    {
        $this->assertTrue(
            FocusSessionStatus::PAUSED->canTransitionTo(FocusSessionStatus::ACTIVE)
        );
    }

    public function test_can_transition_from_paused_to_completed(): void
    {
        $this->assertTrue(
            FocusSessionStatus::PAUSED->canTransitionTo(FocusSessionStatus::COMPLETED)
        );
    }

    public function test_cannot_transition_from_completed(): void
    {
        $this->assertFalse(
            FocusSessionStatus::COMPLETED->canTransitionTo(FocusSessionStatus::ACTIVE)
        );
        $this->assertFalse(
            FocusSessionStatus::COMPLETED->canTransitionTo(FocusSessionStatus::PAUSED)
        );
        $this->assertFalse(
            FocusSessionStatus::COMPLETED->canTransitionTo(FocusSessionStatus::PENDING)
        );
    }
}
