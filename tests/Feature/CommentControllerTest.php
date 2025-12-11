<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BoardRole;
use App\Models\Board;
use App\Models\Card;
use App\Models\Comment;
use App\Models\ListModel;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Workspace $workspace;
    private Board $board;
    private Card $card;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create();
        $this->workspace->members()->attach($this->user->id);

        $this->board = Board::factory()->create([
            'workspace_id' => $this->workspace->id,
        ]);
        $this->board->members()->attach($this->user->id, ['role' => BoardRole::OWNER]);

        $list = ListModel::factory()->create(['board_id' => $this->board->id]);
        $this->card = Card::factory()->create([
            'list_id' => $list->id,
            'board_id' => $this->board->id,
        ]);
    }

    public function test_can_list_comments(): void
    {
        Comment::factory()->count(3)->create([
            'card_id' => $this->card->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/comments?card_id={$this->card->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data'])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_comment(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/comments', [
            'card_id' => $this->card->id,
            'content' => 'Test comment',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'card_id' => $this->card->id,
                    'content' => 'Test comment',
                    'user_id' => $this->user->id,
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'card_id' => $this->card->id,
            'content' => 'Test comment',
        ]);
    }

    public function test_can_update_own_comment(): void
    {
        $comment = Comment::factory()->create([
            'card_id' => $this->card->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/comments/{$comment->id}", [
            'content' => 'Updated comment',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'content' => 'Updated comment',
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated comment',
        ]);
    }

    public function test_can_delete_own_comment(): void
    {
        $comment = Comment::factory()->create([
            'card_id' => $this->card->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_cannot_update_other_user_comment(): void
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'card_id' => $this->card->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/comments/{$comment->id}", [
            'content' => 'Updated comment',
        ]);

        $response->assertStatus(403);
    }
}
