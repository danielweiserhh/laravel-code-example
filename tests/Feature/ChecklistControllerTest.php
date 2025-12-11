<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Card;
use App\Models\Checklist;
use App\Models\ListModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_create_checklist(): void
    {
        $board = Board::factory()->create();
        $board->members()->attach($this->user->id);
        $list = ListModel::factory()->create(['board_id' => $board->id]);
        $card = Card::factory()->create(['list_id' => $list->id, 'board_id' => $board->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/checklists', [
                'card_id' => $card->id,
                'title' => 'Test Checklist',
                'items' => [
                    ['title' => 'Item 1', 'position' => 1],
                    ['title' => 'Item 2', 'position' => 2],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => ['title' => 'Test Checklist'],
            ]);

        $this->assertDatabaseHas('checklists', [
            'card_id' => $card->id,
            'title' => 'Test Checklist',
        ]);
    }

    public function test_user_can_update_checklist(): void
    {
        $board = Board::factory()->create();
        $board->members()->attach($this->user->id);
        $list = ListModel::factory()->create(['board_id' => $board->id]);
        $card = Card::factory()->create(['list_id' => $list->id, 'board_id' => $board->id]);
        $checklist = Checklist::factory()->create(['card_id' => $card->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/checklists/{$checklist->id}", [
                'title' => 'Updated Checklist',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['title' => 'Updated Checklist'],
            ]);

        $this->assertDatabaseHas('checklists', [
            'id' => $checklist->id,
            'title' => 'Updated Checklist',
        ]);
    }

    public function test_user_can_delete_checklist(): void
    {
        $board = Board::factory()->create();
        $board->members()->attach($this->user->id);
        $list = ListModel::factory()->create(['board_id' => $board->id]);
        $card = Card::factory()->create(['list_id' => $list->id, 'board_id' => $board->id]);
        $checklist = Checklist::factory()->create(['card_id' => $card->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/checklists/{$checklist->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Чеклист удален',
            ]);

        $this->assertDatabaseMissing('checklists', ['id' => $checklist->id]);
    }

    public function test_user_cannot_delete_checklist_from_other_board(): void
    {
        $otherUser = User::factory()->create();
        $board = Board::factory()->create();
        $board->members()->attach($otherUser->id);
        $list = ListModel::factory()->create(['board_id' => $board->id]);
        $card = Card::factory()->create(['list_id' => $list->id, 'board_id' => $board->id]);
        $checklist = Checklist::factory()->create(['card_id' => $card->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/checklists/{$checklist->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('checklists', ['id' => $checklist->id]);
    }
}
