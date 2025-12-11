<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Routines\ValueObjects;

use App\Domain\Routines\ValueObjects\RoutineStep;
use PHPUnit\Framework\TestCase;

final class RoutineStepTest extends TestCase
{
    public function test_creates_from_array(): void
    {
        $data = [
            'id' => 1,
            'routine_id' => 10,
            'title' => 'Step title',
            'description' => 'Step description',
            'position' => 0,
            'duration_minutes' => 15,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
        ];

        $step = RoutineStep::fromArray($data);

        $this->assertSame(1, $step->id);
        $this->assertSame(10, $step->routineId);
        $this->assertSame('Step title', $step->title);
        $this->assertSame('Step description', $step->description);
        $this->assertSame(0, $step->position);
        $this->assertSame(15, $step->durationMinutes);
    }

    public function test_creates_from_array_with_nullable_fields(): void
    {
        $data = [
            'id' => 1,
            'routine_id' => 10,
            'title' => 'Step title',
            'description' => null,
            'position' => 0,
            'duration_minutes' => null,
            'created_at' => '2024-01-01T00:00:00Z',
            'updated_at' => '2024-01-01T00:00:00Z',
        ];

        $step = RoutineStep::fromArray($data);

        $this->assertNull($step->description);
        $this->assertNull($step->durationMinutes);
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $step = new RoutineStep(
            id: 1,
            routineId: 10,
            title: 'Step title',
            description: 'Description',
            position: 0,
            durationMinutes: 15,
            createdAt: '2024-01-01T00:00:00Z',
            updatedAt: '2024-01-01T00:00:00Z',
        );

        $array = $step->toArray();

        $this->assertSame(1, $array['id']);
        $this->assertSame(10, $array['routine_id']);
        $this->assertSame('Step title', $array['title']);
        $this->assertSame('Description', $array['description']);
        $this->assertSame(0, $array['position']);
        $this->assertSame(15, $array['duration_minutes']);
    }
}
