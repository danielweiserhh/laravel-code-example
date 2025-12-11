<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Routines\Enums;

use App\Domain\Routines\Enums\RoutineType;
use PHPUnit\Framework\TestCase;

final class RoutineTypeTest extends TestCase
{
    public function test_has_expected_cases(): void
    {
        $this->assertSame('morning', RoutineType::MORNING->value);
        $this->assertSame('evening', RoutineType::EVENING->value);
        $this->assertSame('work', RoutineType::WORK->value);
        $this->assertSame('custom', RoutineType::CUSTOM->value);
    }

    public function test_values_returns_all_values(): void
    {
        $values = RoutineType::values();

        $this->assertIsArray($values);
        $this->assertContains('morning', $values);
        $this->assertContains('evening', $values);
        $this->assertContains('work', $values);
        $this->assertContains('custom', $values);
        $this->assertCount(4, $values);
    }

    public function test_can_create_from_string(): void
    {
        $type = RoutineType::from('morning');

        $this->assertSame(RoutineType::MORNING, $type);
    }
}
