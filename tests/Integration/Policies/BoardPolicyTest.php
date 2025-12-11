<?php

declare(strict_types=1);

namespace Tests\Integration\Policies;

use App\Enums\BoardRole;
use App\Models\Board;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_and_update_board(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $workspace->members()->attach($owner->id, ['role' => 'owner']);
        $board = Board::factory()->for($workspace)->create();
        $board->members()->attach($owner->id, ['role' => BoardRole::OWNER->value]);

        $this->assertTrue($owner->can('view', $board));
        $this->assertTrue($owner->can('update', $board));
    }

    public function test_guest_cannot_view_others_board(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $workspace->members()->attach($owner->id, ['role' => 'owner']);
        $board = Board::factory()->for($workspace)->create();
        $board->members()->attach($owner->id, ['role' => BoardRole::OWNER->value]);
        $guest = User::factory()->create();

        $this->assertFalse($guest->can('view', $board));
    }
}
