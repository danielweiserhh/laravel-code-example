<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Today\Services;

use App\Domain\Today\Services\TodayDomainService;
use PHPUnit\Framework\TestCase;

class TodayDomainServiceTest extends TestCase
{
    private TodayDomainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TodayDomainService;
    }

    public function test_get_today_date_returns_date_string(): void
    {
        $result = $this->service->getTodayDate();

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result);
    }

    public function test_get_today_date_respects_timezone(): void
    {
        $utcToday = $this->service->getTodayDate('UTC');
        $tokyoToday = $this->service->getTodayDate('Asia/Tokyo');

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $utcToday);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $tokyoToday);
    }

    public function test_get_today_start_utc_returns_datetime(): void
    {
        $result = $this->service->getTodayStartUtc();

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result);
    }

    public function test_get_today_end_utc_returns_datetime(): void
    {
        $result = $this->service->getTodayEndUtc();

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result);
    }

    public function test_is_today_returns_true_for_today(): void
    {
        $today = date('Y-m-d');

        $this->assertTrue($this->service->isToday($today));
    }

    public function test_is_today_returns_false_for_yesterday(): void
    {
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $this->assertFalse($this->service->isToday($yesterday));
    }

    public function test_is_today_returns_false_for_tomorrow(): void
    {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $this->assertFalse($this->service->isToday($tomorrow));
    }

    public function test_is_overdue_returns_true_for_past_date(): void
    {
        $pastDate = date('Y-m-d', strtotime('-5 days'));

        $this->assertTrue($this->service->isOverdue($pastDate));
    }

    public function test_is_overdue_returns_false_for_today(): void
    {
        $today = date('Y-m-d');

        $this->assertFalse($this->service->isOverdue($today));
    }

    public function test_is_overdue_returns_false_for_future_date(): void
    {
        $futureDate = date('Y-m-d', strtotime('+5 days'));

        $this->assertFalse($this->service->isOverdue($futureDate));
    }

    public function test_is_upcoming_returns_true_for_future_date(): void
    {
        $futureDate = date('Y-m-d', strtotime('+5 days'));

        $this->assertTrue($this->service->isUpcoming($futureDate));
    }

    public function test_is_upcoming_returns_false_for_today(): void
    {
        $today = date('Y-m-d');

        $this->assertFalse($this->service->isUpcoming($today));
    }

    public function test_is_upcoming_returns_false_for_past_date(): void
    {
        $pastDate = date('Y-m-d', strtotime('-5 days'));

        $this->assertFalse($this->service->isUpcoming($pastDate));
    }

    public function test_get_deadline_status_returns_overdue_for_past(): void
    {
        $pastDate = date('Y-m-d', strtotime('-1 day'));

        $this->assertEquals('overdue', $this->service->getDeadlineStatus($pastDate));
    }

    public function test_get_deadline_status_returns_today_for_today(): void
    {
        $today = date('Y-m-d');

        $this->assertEquals('today', $this->service->getDeadlineStatus($today));
    }

    public function test_get_deadline_status_returns_upcoming_for_future(): void
    {
        $futureDate = date('Y-m-d', strtotime('+1 day'));

        $this->assertEquals('upcoming', $this->service->getDeadlineStatus($futureDate));
    }

    public function test_get_deadline_status_returns_null_for_null_date(): void
    {
        $this->assertNull($this->service->getDeadlineStatus(null));
    }

    public function test_get_days_until_deadline_returns_zero_for_today(): void
    {
        $today = date('Y-m-d');

        $this->assertEquals(0, $this->service->getDaysUntilDeadline($today));
    }

    public function test_get_days_until_deadline_returns_negative_for_past(): void
    {
        $pastDate = date('Y-m-d', strtotime('-3 days'));

        $this->assertEquals(-3, $this->service->getDaysUntilDeadline($pastDate));
    }

    public function test_get_days_until_deadline_returns_positive_for_future(): void
    {
        $futureDate = date('Y-m-d', strtotime('+5 days'));

        $this->assertEquals(5, $this->service->getDaysUntilDeadline($futureDate));
    }

    public function test_handles_invalid_timezone_gracefully(): void
    {
        $result = $this->service->getTodayDate('Invalid/Timezone');

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result);
    }
}
