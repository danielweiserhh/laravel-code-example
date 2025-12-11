<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function actingAs($user, $guard = null): static
    {
        return parent::actingAs($user, $guard ?? 'sanctum');
    }
}
