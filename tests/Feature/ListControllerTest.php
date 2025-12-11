<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Board;
use App\Models\ListModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_lists(): void
    {
        $board = Board::factory()->create();
        $lists = ListModel::factory()->count(3)->create(['board_id' => $board->id]);
        $listWithCard = $lists->first();
        \App\Models\Card::factory()->create([
            'board_id' => $board->id,
            'list_id' => $listWithCard->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/lists?board_id='.$board->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data'])
            ->assertJsonCount(3, 'data')
            ->assertJsonFragment(['active_cards_count' => 1]);
    }

    public function test_can_create_list(): void
    {
        $board = Board::factory()->create();
        $board->members()->attach($this->user->id, ['role' => \App\Enums\BoardRole::OWNER->value]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/lists', [
                'board_id' => $board->id,
                'name' => 'New List',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'New List'],
            ]);

        $this->assertDatabaseHas('lists', [
            'name' => 'New List',
            'board_id' => $board->id,
        ]);
    }

    public function test_can_update_list(): void
    {
        $board = Board::factory()->create();
        $board->members()->attach($this->user->id, ['role' => \App\Enums\BoardRole::OWNER->value]);
        $list = ListModel::factory()->create(['board_id' => $board->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/lists/'.$list->id, [
                'name' => 'Updated List',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'Updated List'],
            ]);

        $this->assertDatabaseHas('lists', [
            'id' => $list->id,
            'name' => 'Updated List',
        ]);
    }

    public function test_can_delete_list(): void
    {
        $board = Board::factory()->create();
        $board->members()->attach($this->user->id, ['role' => \App\Enums\BoardRole::OWNER->value]);
        $list = ListModel::factory()->create(['board_id' => $board->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/lists/'.$list->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('lists', ['id' => $list->id]);
    }

    public function test_cannot_delete_list_with_active_cards(): void
    {
        $board = Board::factory()->create();
        $board->members()->attach($this->user->id, ['role' => \App\Enums\BoardRole::OWNER->value]);
        $list = ListModel::factory()->create(['board_id' => $board->id]);
        \App\Models\Card::factory()->create(['board_id' => $board->id, 'list_id' => $list->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/lists/'.$list->id);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Невозможно удалить колонку с активными задачами', 'success' => false]);

        $this->assertDatabaseHas('lists', ['id' => $list->id]);
    }
}
