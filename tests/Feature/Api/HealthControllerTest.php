<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HealthControllerTest extends TestCase
{
    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ok',
        ]);
    }

    public function test_health_full_endpoint_returns_structured_response(): void
    {
        $response = $this->getJson('/api/v1/health/full');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'checks' => [
                'database' => [
                    'status',
                    'details',
                ],
                'rabbitmq' => [
                    'status',
                    'details',
                ],
                'ai' => [
                    'status',
                    'details',
                ],
                'speech' => [
                    'status',
                    'details',
                ],
            ],
        ]);

        $data = $response->json();
        $this->assertContains($data['status'], ['ok', 'degraded', 'down']);
        $this->assertArrayHasKey('checks', $data);
    }

    public function test_health_full_never_returns_500(): void
    {
        $response = $this->getJson('/api/v1/health/full');

        $this->assertNotEquals(500, $response->status());
        $response->assertJsonStructure([
            'status',
            'checks',
        ]);
    }
}

