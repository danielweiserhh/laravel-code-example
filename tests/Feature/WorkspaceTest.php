<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_workspace(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/workspaces', [
                'name' => 'Test Workspace',
                'description' => 'Test Description',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'members',
                ],
            ]);

        $this->assertDatabaseHas('workspaces', [
            'name' => 'Test Workspace',
        ]);
    }

    public function test_user_can_list_workspaces(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $workspace->members()->attach($user->id, ['role' => 'member']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/workspaces');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data'])
            ->assertJsonCount(1, 'data');
    }
}
