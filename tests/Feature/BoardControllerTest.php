<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Board;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_boards(): void
    {
        $workspace = Workspace::factory()->create();
        $workspace->members()->attach($this->user->id);
        Board::factory()->count(3)->create(['workspace_id' => $workspace->id])
            ->each(fn ($board) => $board->members()->attach($this->user->id));

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/boards');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data'])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_board(): void
    {
        $workspace = Workspace::factory()->create();
        $workspace->members()->attach($this->user->id);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/boards', [
                'workspace_id' => $workspace->id,
                'name' => 'New Board',
                'description' => 'Board Description',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'New Board'],
            ]);

        $this->assertDatabaseHas('boards', [
            'name' => 'New Board',
            'workspace_id' => $workspace->id,
        ]);
    }

    public function test_can_show_board(): void
    {
        $board = Board::factory()->create();
        $board->members()->attach($this->user->id);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/boards/'.$board->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['id' => $board->id],
            ]);
    }

    public function test_can_update_board(): void
    {
        $board = Board::factory()->create();
        $board->members()->attach($this->user->id, ['role' => \App\Enums\BoardRole::OWNER->value]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/boards/'.$board->id, [
                'name' => 'Updated Board',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'Updated Board'],
            ]);

        $this->assertDatabaseHas('boards', [
            'id' => $board->id,
            'name' => 'Updated Board',
        ]);
    }

    public function test_can_delete_board(): void
    {
        $board = Board::factory()->create();
        $board->members()->attach($this->user->id, ['role' => \App\Enums\BoardRole::OWNER->value]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/boards/'.$board->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('boards', ['id' => $board->id]);
    }
}
