<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\JobStatus;
use App\Models\AIJob;
use App\Models\Card;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TodayControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Queue::fake();
    }

    public function test_can_get_today_plan(): void
    {
        Card::factory()->count(5)->create()->each(function ($card) {
            $card->assignees()->attach($this->user->id);
        });

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/today');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'plan' => ['big_three', 'note_for_user'],
                    'source',
                ],
            ]);
    }

    public function test_today_plan_includes_big_three(): void
    {
        Card::factory()->count(3)->create()->each(function ($card) {
            $card->assignees()->attach($this->user->id);
        });

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/today');

        $response->assertStatus(200);
        $plan = $response->json('data.plan');
        $this->assertCount(3, $plan['big_three']);
    }

    public function test_user_can_accept_today_plan(): void
    {
        $card = Card::factory()->create();
        $card->assignees()->attach($this->user->id);

        $aiJob = AIJob::factory()
            ->for($this->user)
            ->state([
                'status' => JobStatus::SUCCEEDED,
                'result' => [
                    'big_three' => [['id' => $card->id, 'title' => $card->title]],
                    'note_for_user' => 'Initial note',
                ],
            ])
            ->create();

        $payload = [
            'job_id' => $aiJob->id,
            'big_three' => [
                [
                    'id' => $card->id,
                    'title' => $card->title,
                    'board' => $card->board?->name,
                ],
            ],
            'note_for_user' => 'Focus on shipping value today.',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/today/plan', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'План сохранен',
            ])
            ->assertJsonFragment([
                'note_for_user' => 'Focus on shipping value today.',
            ]);

        $this->assertDatabaseHas('user_daily_plans', [
            'user_id' => $this->user->id,
            'plan_date' => now()->toDateString(),
        ]);

        $todayResponse = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/today');

        $todayResponse->assertStatus(200);
        $plan = $todayResponse->json('data.plan');

        $this->assertCount(1, $plan['big_three']);
        $this->assertSame($card->id, $plan['big_three'][0]['id']);
        $this->assertSame('Focus on shipping value today.', $plan['note_for_user']);
        $this->assertSame('saved', $todayResponse->json('data.source'));
    }
}
