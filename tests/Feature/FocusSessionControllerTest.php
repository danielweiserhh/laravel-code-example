<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FocusSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FocusSessionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_focus_sessions(): void
    {
        FocusSession::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/focus-sessions');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data'])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_focus_session(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/focus-sessions', [
            'duration_minutes' => 25,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'duration_minutes' => 25,
                    'user_id' => $this->user->id,
                    'status' => 'pending',
                ],
            ]);

        $this->assertDatabaseHas('focus_sessions', [
            'user_id' => $this->user->id,
            'duration_minutes' => 25,
        ]);
    }

    public function test_can_start_session(): void
    {
        $session = FocusSession::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/focus-sessions/{$session->id}/start");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'active',
                ],
            ]);

        $this->assertDatabaseHas('focus_sessions', [
            'id' => $session->id,
            'status' => 'active',
        ]);
    }

    public function test_can_pause_session(): void
    {
        $session = FocusSession::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/focus-sessions/{$session->id}/pause");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'paused',
                ],
            ]);

        $this->assertDatabaseHas('focus_sessions', [
            'id' => $session->id,
            'status' => 'paused',
        ]);
    }

    public function test_can_complete_session(): void
    {
        $session = FocusSession::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/focus-sessions/{$session->id}/complete");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'completed',
                ],
            ]);

        $this->assertDatabaseHas('focus_sessions', [
            'id' => $session->id,
            'status' => 'completed',
        ]);
    }

    public function test_cannot_access_other_user_session(): void
    {
        $otherUser = User::factory()->create();
        $session = FocusSession::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/focus-sessions/{$session->id}");

        $response->assertStatus(403);
    }
}
