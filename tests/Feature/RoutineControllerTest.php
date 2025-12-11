<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutineControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_routines(): void
    {
        Routine::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/routines');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data'])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_routine(): void
    {
        $workspace = \App\Models\Workspace::factory()->create();
        $workspace->members()->attach($this->user->id);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/routines', [
                'workspace_id' => $workspace->id,
                'name' => 'Morning Routine',
                'description' => 'Daily morning routine',
                'type' => 'custom',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'Morning Routine'],
            ]);

        $this->assertDatabaseHas('routines', [
            'name' => 'Morning Routine',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_create_routine_with_steps(): void
    {
        $workspace = \App\Models\Workspace::factory()->create();
        $workspace->members()->attach($this->user->id);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/routines', [
                'workspace_id' => $workspace->id,
                'name' => 'Morning Routine',
                'steps' => [
                    ['title' => 'Step 1', 'position' => 0],
                    ['title' => 'Step 2', 'position' => 1],
                ],
            ]);

        $response->assertStatus(201);
        $routine = Routine::where('name', 'Morning Routine')->first();
        $this->assertCount(2, $routine->steps);
    }

    public function test_can_show_routine(): void
    {
        $routine = Routine::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/routines/'.$routine->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['id' => $routine->id],
            ]);
    }

    public function test_can_update_routine(): void
    {
        $routine = Routine::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/routines/'.$routine->id, [
                'name' => 'Updated Routine',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'Updated Routine'],
            ]);

        $this->assertDatabaseHas('routines', [
            'id' => $routine->id,
            'name' => 'Updated Routine',
        ]);
    }

    public function test_can_delete_routine(): void
    {
        $routine = Routine::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/routines/'.$routine->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('routines', ['id' => $routine->id]);
    }
}
