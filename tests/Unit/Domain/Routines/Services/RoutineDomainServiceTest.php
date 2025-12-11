<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Routines\Services;

use App\Domain\Routines\Services\RoutineDomainService;
use App\Domain\Routines\ValueObjects\Routine;
use PHPUnit\Framework\TestCase;

class RoutineDomainServiceTest extends TestCase
{
    private RoutineDomainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoutineDomainService;
    }

    public function test_validates_valid_name(): void
    {
        $this->service->validateName('Morning Routine');
    }

    public function test_rejects_empty_name(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Название рутины не может быть пустым');

        $this->service->validateName('');
    }

    public function test_rejects_whitespace_only_name(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Название рутины не может быть пустым');

        $this->service->validateName('   ');
    }

    public function test_rejects_too_long_name(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Название рутины слишком длинное');

        $this->service->validateName(str_repeat('a', 256));
    }

    public function test_validates_valid_description(): void
    {
        $this->service->validateDescription('This is a valid description');
    }

    public function test_validates_null_description(): void
    {
        $this->service->validateDescription(null);
    }

    public function test_rejects_too_long_description(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Описание рутины слишком длинное');

        $this->service->validateDescription(str_repeat('a', 5001));
    }

    public function test_validates_valid_types(): void
    {
        foreach (['morning', 'evening', 'work', 'custom'] as $type) {
            $this->service->validateType($type);
        }
    }

    public function test_rejects_invalid_type(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Недопустимый тип рутины');

        $this->service->validateType('invalid');
    }

    public function test_validates_valid_step_title(): void
    {
        $this->service->validateStepTitle('Step 1');
    }

    public function test_rejects_empty_step_title(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Название шага не может быть пустым');

        $this->service->validateStepTitle('');
    }

    public function test_validates_valid_step_duration(): void
    {
        $this->service->validateStepDuration(30);
    }

    public function test_validates_null_step_duration(): void
    {
        $this->service->validateStepDuration(null);
    }

    public function test_rejects_zero_step_duration(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Длительность шага должна быть положительной');

        $this->service->validateStepDuration(0);
    }

    public function test_rejects_too_long_step_duration(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Длительность шага не может превышать 8 часов');

        $this->service->validateStepDuration(481);
    }

    public function test_routine_can_be_activated(): void
    {
        $routine = $this->createRoutine(['name' => 'Test Routine', 'is_active' => false]);

        $this->assertTrue($this->service->canBeActivated($routine));
    }

    public function test_routine_with_empty_name_cannot_be_activated(): void
    {
        $routine = $this->createRoutine(['name' => '   ', 'is_active' => false]);

        $this->assertFalse($this->service->canBeActivated($routine));
    }

    public function test_active_routine_can_be_deactivated(): void
    {
        $routine = $this->createRoutine(['is_active' => true]);

        $this->assertTrue($this->service->canBeDeactivated($routine));
    }

    public function test_inactive_routine_cannot_be_deactivated(): void
    {
        $routine = $this->createRoutine(['is_active' => false]);

        $this->assertFalse($this->service->canBeDeactivated($routine));
    }

    public function test_returns_valid_types(): void
    {
        $types = $this->service->getValidTypes();

        $this->assertContains('morning', $types);
        $this->assertContains('evening', $types);
        $this->assertContains('work', $types);
        $this->assertContains('custom', $types);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createRoutine(array $overrides = []): Routine
    {
        $defaults = [
            'id' => 1,
            'user_id' => 1,
            'workspace_id' => null,
            'name' => 'Test Routine',
            'description' => null,
            'type' => 'custom',
            'is_active' => true,
            'settings' => null,
            'steps' => [],
            'created_at' => '2024-01-01T00:00:00+00:00',
            'updated_at' => '2024-01-01T00:00:00+00:00',
        ];

        return Routine::fromArray(array_merge($defaults, $overrides));
    }
}
