<?php

declare(strict_types=1);

namespace Tests\Feature\Concurrency;

use App\Models\Board;
use App\Models\Card;
use App\Models\ListModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CardMoveConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_concurrent_card_moves_maintain_consistency(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user->id]);
        $list1 = ListModel::factory()->create(['board_id' => $board->id]);
        $list2 = ListModel::factory()->create(['board_id' => $board->id]);

        $card1 = Card::factory()->create(['list_id' => $list1->id, 'position' => 0]);
        $card2 = Card::factory()->create(['list_id' => $list1->id, 'position' => 1]);
        $card3 = Card::factory()->create(['list_id' => $list1->id, 'position' => 2]);

        $this->actingAs($user);

        $response1 = $this->putJson("/api/v1/cards/{$card1->id}/move", [
            'list_id' => $list2->id,
            'position' => 0,
        ]);

        $response2 = $this->putJson("/api/v1/cards/{$card2->id}/move", [
            'list_id' => $list2->id,
            'position' => 0,
        ]);

        $this->assertTrue($response1->isSuccessful() || $response2->isSuccessful());

        $list1Cards = Card::where('list_id', $list1->id)->orderBy('position')->get();
        $list2Cards = Card::where('list_id', $list2->id)->orderBy('position')->get();

        $this->assertPositionsAreUnique($list1Cards);
        $this->assertPositionsAreUnique($list2Cards);
        $this->assertNoGapsInPositions($list1Cards);
        $this->assertNoGapsInPositions($list2Cards);
    }

    public function test_concurrent_position_updates_maintain_order(): void
    {
        $user = User::factory()->create();
        $board = Board::factory()->create(['user_id' => $user->id]);
        $list = ListModel::factory()->create(['board_id' => $board->id]);

        $cards = Card::factory()->count(5)->create([
            'list_id' => $list->id,
        ]);

        foreach ($cards as $index => $card) {
            $card->update(['position' => $index]);
        }

        $this->actingAs($user);

        $responses = [];
        $responses[] = $this->putJson("/api/v1/cards/{$cards[0]->id}/move", [
            'list_id' => $list->id,
            'position' => 4,
        ]);

        $responses[] = $this->putJson("/api/v1/cards/{$cards[4]->id}/move", [
            'list_id' => $list->id,
            'position' => 0,
        ]);

        $finalCards = Card::where('list_id', $list->id)->orderBy('position')->get();

        $this->assertPositionsAreUnique($finalCards);
        $this->assertNoGapsInPositions($finalCards);
        $this->assertCount(5, $finalCards);
    }

    private function assertPositionsAreUnique($cards): void
    {
        $positions = $cards->pluck('position')->toArray();
        $uniquePositions = array_unique($positions);

        $this->assertCount(
            count($uniquePositions),
            $positions,
            'Card positions must be unique'
        );
    }

    private function assertNoGapsInPositions($cards): void
    {
        $positions = $cards->pluck('position')->sort()->values()->toArray();
        $expectedPositions = range(0, count($positions) - 1);

        $this->assertEquals(
            $expectedPositions,
            $positions,
            'Card positions must have no gaps'
        );
    }
}

