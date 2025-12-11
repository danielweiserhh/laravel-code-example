<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Card;
use App\Models\ListModel;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardChecklistTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Workspace $workspace;
    protected ListModel $list;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create();
        $this->workspace->members()->attach($this->user->id, ['role' => 'owner']);

        $board = $this->workspace->boards()->create([
            'name' => 'Test Board',
            'privacy' => 'private',
        ]);

        $this->list = $board->lists()->create([
            'name' => 'Test List',
            'position' => 1,
        ]);
    }

    public function test_can_create_card_with_checklist(): void
    {
        $checklistItems = [
            'Пойти в строительный магазин',
            'Купить LED лампочку для коридора',
            'Проверить остальные лампочки в квартире',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/cards', [
                'list_id' => $this->list->id,
                'title' => 'Купить лампочку',
                'description' => 'Перегорела лампочка в коридоре',
                'checklist' => $checklistItems,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'title',
                'checklists' => [
                    '*' => [
                        'id',
                        'title',
                        'items' => [
                            '*' => [
                                'id',
                                'title',
                                'position',
                                'is_completed',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $card = Card::find($response->json('data.id'));
        $this->assertNotNull($card);

        $checklists = $card->checklists;
        $this->assertCount(1, $checklists);

        $checklist = $checklists->first();
        $this->assertEquals('Чеклист', $checklist->title);

        $items = $checklist->items;
        $this->assertCount(3, $items);

        foreach ($checklistItems as $index => $expectedTitle) {
            $item = $items->where('position', $index + 1)->first();
            $this->assertNotNull($item, "Item at position {$index} not found");
            $this->assertEquals($expectedTitle, $item->title);
            $this->assertFalse($item->is_completed);
        }
    }

    public function test_checklist_items_are_trimmed(): void
    {
        $checklistItems = [
            '  Пойти в магазин  ',
            'Купить лампочку',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/cards', [
                'list_id' => $this->list->id,
                'title' => 'Тест',
                'checklist' => $checklistItems,
            ]);

        $response->assertStatus(201);

        $card = Card::find($response->json('data.id'));
        $this->assertNotNull($card);
        $checklist = $card->checklists->first();
        $items = $checklist->items;

        $this->assertEquals('Пойти в магазин', $items->first()->title);
    }

    public function test_empty_checklist_items_are_ignored(): void
    {
        $checklistItems = [
            'Пойти в магазин',
            'Купить лампочку',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/cards', [
                'list_id' => $this->list->id,
                'title' => 'Тест',
                'checklist' => $checklistItems,
            ]);

        $response->assertStatus(201);

        $card = Card::find($response->json('data.id'));
        $this->assertNotNull($card);
        $checklist = $card->checklists->first();
        $items = $checklist->items;

        
        $this->assertCount(2, $items);
        $this->assertEquals('Пойти в магазин', $items->first()->title);
        $this->assertEquals('Купить лампочку', $items->last()->title);
    }

    public function test_card_can_be_created_without_checklist(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/cards', [
                'list_id' => $this->list->id,
                'title' => 'Тест без чеклиста',
                'description' => 'Описание',
            ]);

        $response->assertStatus(201);

        $card = Card::find($response->json('data.id'));
        $this->assertNotNull($card);
        $this->assertCount(0, $card->checklists);
    }
}
