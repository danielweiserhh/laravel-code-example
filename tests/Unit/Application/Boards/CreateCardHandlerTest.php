<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Boards;

use App\Application\Boards\UseCases\CreateCardCommand;
use App\Application\Boards\UseCases\CreateCardHandler;
use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Domain\Boards\Repositories\ListRepositoryInterface;
use App\Domain\Boards\Services\CardDomainService;
use App\Domain\Boards\ValueObjects\Card as DomainCard;
use App\Domain\Boards\ValueObjects\BoardList;
use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use Tests\TestCase;

class CreateCardHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_card_in_list_using_repositories(): void
    {
        
        $board = \App\Models\Board::factory()->create();
        $listModel = \App\Models\ListModel::factory()->create([
            'board_id' => $board->id,
        ]);

        $list = BoardList::fromArray([
            'id' => $listModel->id,
            'board_id' => $listModel->board_id,
            'name' => $listModel->name,
            'position' => $listModel->position,
            'is_archived' => $listModel->is_archived,
            'created_at' => $listModel->created_at->toIso8601String(),
            'updated_at' => $listModel->updated_at->toIso8601String(),
        ]);

        $domainCard = DomainCard::fromArray([
            'id' => 1,
            'list_id' => $list->id,
            'board_id' => $board->id,
            'title' => 'Task',
            'description' => 'Desc',
            'position' => 6,
            'start_date' => null,
            'due_date' => null,
            'energy_level' => null,
            'task_type' => null,
            'is_completed' => false,
            'completed_at' => null,
            'custom_fields' => [],
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);

        
        $realCard = Card::factory()->create([
            'id' => 1,
            'list_id' => $list->id,
            'board_id' => $board->id,
            'title' => 'Task',
            'description' => 'Desc',
            'position' => 6,
        ]);

        $lists = $this->mock(ListRepositoryInterface::class, function (MockInterface $mock) use ($list) {
            $mock->shouldReceive('find')->once()->with($list->id)->andReturn($list);
        });

        $cards = $this->mock(CardRepositoryInterface::class, function (MockInterface $mock) use ($list, $domainCard) {
            $mock->shouldReceive('getMaxPositionForList')->once()->with($list->id)->andReturn(5);
            $mock->shouldReceive('create')->once()->andReturn($domainCard);
        });

        $cardDomainService = new CardDomainService($cards);

        $handler = new CreateCardHandler($cards, $lists, $cardDomainService);
        $cmd = new CreateCardCommand(listId: $list->id, title: 'Task', description: 'Desc');
        $card = $handler->handle($cmd);

        $this->assertInstanceOf(Card::class, $card);
        $this->assertSame($list->id, $card->list_id);
        $this->assertSame('Task', $card->title);
    }

    public function test_throws_exception_when_list_not_found(): void
    {
        $lists = $this->mock(ListRepositoryInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('find')->once()->with(999)->andReturn(null);
        });

        $cards = $this->mock(CardRepositoryInterface::class);
        $cardDomainService = new CardDomainService($cards);

        $handler = new CreateCardHandler($cards, $lists, $cardDomainService);
        $cmd = new CreateCardCommand(listId: 999, title: 'Card');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('List not found');
        $handler->handle($cmd);
    }
}
