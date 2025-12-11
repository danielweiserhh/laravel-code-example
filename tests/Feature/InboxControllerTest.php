<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\InboxItem;
use App\Models\User;
use App\Models\Workspace;
use App\Services\DefaultDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InboxControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Workspace $workspace;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->workspace = (new DefaultDataService)->createDefaultWorkspaceForUser($this->user);
        Queue::fake();
    }

    public function test_can_list_inbox_items(): void
    {
        InboxItem::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'workspace_id' => $this->workspace->id,
            'is_processed' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/inbox');

        $response->assertStatus(200);
        
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data' => [
                    '*' => ['id', 'content', 'source', 'created_at', 'updated_at'],
                ],
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
        ]);
        
        $data = $response->json('data.data');
        $this->assertGreaterThanOrEqual(3, count($data));
    }

    public function test_can_create_inbox_item(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/inbox', [
                'content' => 'Test inbox item',
                'source' => 'manual',
                'workspace_id' => $this->workspace->id,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => ['item' => ['content' => 'Test inbox item']],
            ]);

        $this->assertDatabaseHas('inbox_items', [
            'content' => 'Test inbox item',
            'user_id' => $this->user->id,
            'workspace_id' => $this->workspace->id,
        ]);
    }

    public function test_can_create_inbox_item_with_ai_parse(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/inbox', [
                'content' => 'Test inbox item',
                'ai_parse' => true,
                'workspace_id' => $this->workspace->id,
            ]);

        $response->assertStatus(201);
        Queue::assertPushed(\App\Jobs\SendAIJobToQueue::class);
    }

    public function test_can_delete_inbox_item(): void
    {
        $item = InboxItem::factory()->create([
            'user_id' => $this->user->id,
            'workspace_id' => $this->workspace->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/inbox/'.$item->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('inbox_items', ['id' => $item->id]);
    }

    public function test_cannot_delete_other_user_inbox_item(): void
    {
        $otherUser = User::factory()->create();
        $otherWorkspace = (new DefaultDataService)->createDefaultWorkspaceForUser($otherUser);
        $item = InboxItem::factory()->create([
            'user_id' => $otherUser->id,
            'workspace_id' => $otherWorkspace->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/inbox/'.$item->id);

        $response->assertStatus(403);
    }
}
