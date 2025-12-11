<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AISuggestNegativeTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_context_type_returns_422(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $resp = $this->postJson('/api/v1/ai/suggest', [
            'context_type' => 'unknown_type',
            'context_id' => 1,
            'options' => [],
        ]);

        $resp->assertStatus(422);
    }
}
