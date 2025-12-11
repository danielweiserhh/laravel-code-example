<?php

declare(strict_types=1);

namespace App\Domain\Today\Services;

use DateTimeImmutable;
use DateTimeZone;

final class TodayDomainService
{
    private const DEFAULT_TIMEZONE = 'UTC';
    
    public function getTodayDate(?string $timezone = null): string
    {
        $tz = $this->resolveTimezone($timezone);
        $now = new DateTimeImmutable('now', $tz);

        return $now->format('Y-m-d');
    }
    
    public function getTodayStartUtc(?string $timezone = null): string
    {
        $tz = $this->resolveTimezone($timezone);
        $todayStart = new DateTimeImmutable('today', $tz);
        $todayStartUtc = $todayStart->setTimezone(new DateTimeZone('UTC'));

        return $todayStartUtc->format('Y-m-d H:i:s');
    }
    
    public function getTodayEndUtc(?string $timezone = null): string
    {
        $tz = $this->resolveTimezone($timezone);
        $todayEnd = new DateTimeImmutable('tomorrow', $tz);
        $todayEnd = $todayEnd->modify('-1 second');
        $todayEndUtc = $todayEnd->setTimezone(new DateTimeZone('UTC'));

        return $todayEndUtc->format('Y-m-d H:i:s');
    }
    
    public function isToday(string $date, ?string $timezone = null): bool
    {
        $todayDate = $this->getTodayDate($timezone);
        $checkDate = (new DateTimeImmutable($date))->format('Y-m-d');

        return $todayDate === $checkDate;
    }
    
    public function isOverdue(string $date, ?string $timezone = null): bool
    {
        $todayDate = $this->getTodayDate($timezone);
        $checkDate = (new DateTimeImmutable($date))->format('Y-m-d');

        return $checkDate < $todayDate;
    }
    
    public function isUpcoming(string $date, ?string $timezone = null): bool
    {
        $todayDate = $this->getTodayDate($timezone);
        $checkDate = (new DateTimeImmutable($date))->format('Y-m-d');

        return $checkDate > $todayDate;
    }
    
    public function getDeadlineStatus(?string $date, ?string $timezone = null): ?string
    {
        if ($date === null) {
            return null;
        }

        if ($this->isOverdue($date, $timezone)) {
            return 'overdue';
        }

        if ($this->isToday($date, $timezone)) {
            return 'today';
        }

        return 'upcoming';
    }
    
    public function getDaysUntilDeadline(string $date, ?string $timezone = null): int
    {
        $tz = $this->resolveTimezone($timezone);
        $today = new DateTimeImmutable('today', $tz);
        $deadline = new DateTimeImmutable($date, $tz);
        $deadline = $deadline->setTime(0, 0, 0);

        $diff = $today->diff($deadline);
        $days = (int) $diff->days;

        return $diff->invert ? -$days : $days;
    }
    
    private function resolveTimezone(?string $timezone): DateTimeZone
    {
        if ($timezone === null || $timezone === '') {
            return new DateTimeZone(self::DEFAULT_TIMEZONE);
        }

        try {
            return new DateTimeZone($timezone);
        } catch (\Exception) {
            return new DateTimeZone(self::DEFAULT_TIMEZONE);
        }
    }
}
