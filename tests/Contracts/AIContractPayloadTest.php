<?php

declare(strict_types=1);

namespace Tests\Contracts;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AIContractPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_card_format_payload_matches_fixture(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $fixture = json_decode(file_get_contents(base_path('tests/Contracts/fixtures/ai_tasks/card_format.request.json')), true);
        $resp = $this->postJson('/api/v1/ai/suggest', [
            'context_type' => 'card_format',
            'options' => $fixture['payload'],
        ]);

        $resp->assertStatus(202);
        $jobId = $resp->json('data.job_id');
        $this->assertTrue(Str::isUuid($jobId));
    }
}
