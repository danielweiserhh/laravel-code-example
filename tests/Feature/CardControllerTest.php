<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Card;
use App\Models\ListModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_cards(): void
    {
        $board = Board::factory()->create();
        $list = ListModel::factory()->create(['board_id' => $board->id]);
        Card::factory()->count(3)->create(['list_id' => $list->id, 'board_id' => $board->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/cards?board_id='.$board->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data'])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_card(): void
    {
        $board = Board::factory()->create();
        $list = ListModel::factory()->create(['board_id' => $board->id]);
        $board->members()->attach($this->user->id);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/cards', [
                'list_id' => $list->id,
                'board_id' => $board->id,
                'title' => 'New Card',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => ['title' => 'New Card'],
            ]);

        $this->assertDatabaseHas('cards', [
            'title' => 'New Card',
            'list_id' => $list->id,
        ]);
    }

    public function test_can_show_card(): void
    {
        $board = Board::factory()->create();
        $board->members()->attach($this->user->id);
        $card = Card::factory()->create(['board_id' => $board->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/cards/'.$card->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['id' => $card->id],
            ]);
    }

    public function test_can_update_card(): void
    {
        $board = Board::factory()->create();
        $board->members()->attach($this->user->id);
        $card = Card::factory()->create(['board_id' => $board->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/v1/cards/'.$card->id, [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['title' => 'Updated Title'],
            ]);

        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_can_delete_card(): void
    {
        $board = Board::factory()->create();
        $board->members()->attach($this->user->id);
        $card = Card::factory()->create(['board_id' => $board->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/cards/'.$card->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Задача удалена',
            ]);

        $this->assertDatabaseMissing('cards', ['id' => $card->id]);
    }

    public function test_user_cannot_delete_someone_elses_card(): void
    {
        $otherUser = User::factory()->create();
        $board = Board::factory()->create();
        $board->members()->attach($otherUser->id);
        $card = Card::factory()->create(['board_id' => $board->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/cards/'.$card->id);

        $response->assertStatus(403);

        $this->assertDatabaseHas('cards', ['id' => $card->id]);
    }

    public function test_can_move_card(): void
    {
        $board = Board::factory()->create();
        $oldList = ListModel::factory()->create(['board_id' => $board->id]);
        $newList = ListModel::factory()->create(['board_id' => $board->id]);
        $card = Card::factory()->create(['list_id' => $oldList->id, 'board_id' => $board->id]);
        $board->members()->attach($this->user->id);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/cards/'.$card->id.'/move', [
                'list_id' => $newList->id,
                'position' => 1,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'list_id' => $newList->id,
        ]);
    }
}
