<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Routines\Services;

use App\Domain\Exceptions\InvalidDurationException;
use App\Domain\Routines\Services\RoutineDomainService;
use PHPUnit\Framework\TestCase;

final class RoutineDomainServiceInvariantsTest extends TestCase
{
    private RoutineDomainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoutineDomainService();
    }

    public function test_cannot_validate_empty_step_title(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Название шага не может быть пустым');

        $this->service->validateStepTitle('');
    }

    public function test_cannot_validate_step_duration_below_minimum(): void
    {
        $this->expectException(InvalidDurationException::class);

        $this->service->validateStepDuration(0);
    }

    public function test_cannot_validate_step_duration_above_maximum(): void
    {
        $this->expectException(InvalidDurationException::class);

        $this->service->validateStepDuration(481);
    }

    public function test_can_validate_null_step_duration(): void
    {
        $this->service->validateStepDuration(null);
    }

    public function test_can_validate_valid_step_duration(): void
    {
        $this->service->validateStepDuration(1);
        $this->service->validateStepDuration(480);
    }
}
