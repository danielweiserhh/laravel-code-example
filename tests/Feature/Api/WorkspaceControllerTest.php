<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_their_workspaces(): void
    {
        $user = User::factory()->create();
        $workspace1 = Workspace::factory()->create();
        $workspace2 = Workspace::factory()->create();
        $workspace1->members()->attach($user->id, ['role' => 'owner']);
        $workspace2->members()->attach($user->id, ['role' => 'member']);

        $response = $this->actingAs($user)->getJson('/api/v1/workspaces');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data'])
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_create_workspace(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/workspaces', [
            'name' => 'New Workspace',
            'description' => 'Test workspace',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'New Workspace',
                    'description' => 'Test workspace',
                ],
            ]);

        $this->assertDatabaseHas('workspaces', [
            'name' => 'New Workspace',
        ]);

        $workspace = Workspace::where('name', 'New Workspace')->first();
        $this->assertTrue($workspace->members->contains($user));
        $this->assertEquals('owner', $workspace->members()->where('user_id', $user->id)->first()->pivot->role);
    }

    public function test_user_can_view_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $workspace->members()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAs($user)->getJson("/api/v1/workspaces/{$workspace->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $workspace->id,
                    'name' => $workspace->name,
                ],
            ]);
    }

    public function test_user_can_update_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $workspace->members()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAs($user)->putJson("/api/v1/workspaces/{$workspace->id}", [
            'name' => 'Updated Workspace',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Updated Workspace',
                    'description' => 'Updated description',
                ],
            ]);
    }

    public function test_user_can_delete_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $workspace->members()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAs($user)->deleteJson("/api/v1/workspaces/{$workspace->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Рабочее пространство удалено']);

        $this->assertDatabaseMissing('workspaces', ['id' => $workspace->id]);
    }

    public function test_guest_cannot_access_workspaces(): void
    {
        $response = $this->getJson('/api/v1/workspaces');

        $response->assertStatus(401);
    }

    public function test_user_cannot_view_unauthorized_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/v1/workspaces/{$workspace->id}");

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_unauthorized_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $workspace->members()->attach($user->id, ['role' => 'guest']);

        $response = $this->actingAs($user)->putJson("/api/v1/workspaces/{$workspace->id}", [
            'name' => 'Updated Workspace',
        ]);

        $response->assertStatus(403);
    }
}
