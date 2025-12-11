<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Boards;

use App\Application\Boards\UseCases\MoveCardCommand;
use App\Application\Boards\UseCases\MoveCardHandler;
use App\Domain\Boards\Repositories\ListRepositoryInterface;
use App\Domain\Boards\Services\CardDomainService;
use App\Infrastructure\Persistence\Boards\EloquentCardRepository;
use App\Infrastructure\Persistence\Boards\EloquentListRepository;
use App\Models\Board;
use App\Models\Card;
use App\Models\ListModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use Tests\TestCase;

class MoveCardHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_moves_card_to_new_list_and_position(): void
    {
        $board = Board::factory()->create();
        $oldList = ListModel::factory()->for($board)->create();
        $newList = ListModel::factory()->for($board)->create();
        $card = Card::factory()->create([
            'list_id' => $oldList->id,
            'board_id' => $board->id,
            'position' => 10,
        ]);

        
        $cardRepository = new EloquentCardRepository;
        $listRepository = new EloquentListRepository;
        $cardDomainService = new CardDomainService($cardRepository);

        $handler = new MoveCardHandler($cardRepository, $listRepository, $cardDomainService);
        $handler->handle(new MoveCardCommand(cardId: $card->id, toListId: $newList->id, toPosition: 3));

        
        $card->refresh();
        $this->assertSame($newList->id, $card->list_id);
        $this->assertSame($newList->board_id, $card->board_id);
    }

    public function test_throws_if_list_not_found(): void
    {
        $board = Board::factory()->create();
        $list = ListModel::factory()->for($board)->create();
        $card = Card::factory()->create([
            'list_id' => $list->id,
            'board_id' => $board->id,
        ]);

        
        $cardRepository = new EloquentCardRepository;
        $listRepository = $this->mock(ListRepositoryInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('find')->once()->with(999)->andReturn(null);
        });
        $cardDomainService = new CardDomainService($cardRepository);

        $handler = new MoveCardHandler($cardRepository, $listRepository, $cardDomainService);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('List not found');
        $handler->handle(new MoveCardCommand(cardId: $card->id, toListId: 999, toPosition: 1));
    }

    public function test_moves_card_to_beginning_of_list(): void
    {
        $board = Board::factory()->create();
        $list = ListModel::factory()->for($board)->create();
        $card1 = Card::factory()->create(['list_id' => $list->id, 'board_id' => $board->id, 'position' => 1]);
        $card2 = Card::factory()->create(['list_id' => $list->id, 'board_id' => $board->id, 'position' => 2]);
        $card3 = Card::factory()->create(['list_id' => $list->id, 'board_id' => $board->id, 'position' => 3]);

        
        $cardRepository = new EloquentCardRepository;
        $listRepository = new EloquentListRepository;
        $cardDomainService = new CardDomainService($cardRepository);

        $handler = new MoveCardHandler($cardRepository, $listRepository, $cardDomainService);
        $handler->handle(new MoveCardCommand(cardId: $card3->id, toListId: $list->id, toPosition: 0));

        $card3->refresh();
        $this->assertSame(0, $card3->position);
    }

    public function test_moves_card_to_end_of_list(): void
    {
        $board = Board::factory()->create();
        $list = ListModel::factory()->for($board)->create();
        $card1 = Card::factory()->create(['list_id' => $list->id, 'board_id' => $board->id, 'position' => 0]);
        $card2 = Card::factory()->create(['list_id' => $list->id, 'board_id' => $board->id, 'position' => 1]);
        $card3 = Card::factory()->create(['list_id' => $list->id, 'board_id' => $board->id, 'position' => 2]);

        $cardRepository = new EloquentCardRepository;
        $listRepository = new EloquentListRepository;
        $cardDomainService = new CardDomainService($cardRepository);

        $handler = new MoveCardHandler($cardRepository, $listRepository, $cardDomainService);
        $handler->handle(new MoveCardCommand(cardId: $card1->id, toListId: $list->id, toPosition: 10)); 

        $card1->refresh();
        $this->assertSame(2, $card1->position);
    }

    public function test_moves_card_to_empty_list(): void
    {
        $board = Board::factory()->create();
        $oldList = ListModel::factory()->for($board)->create();
        $newList = ListModel::factory()->for($board)->create();
        $card = Card::factory()->create([
            'list_id' => $oldList->id,
            'board_id' => $board->id,
            'position' => 1,
        ]);

        $cardRepository = new EloquentCardRepository;
        $listRepository = new EloquentListRepository;
        $cardDomainService = new CardDomainService($cardRepository);

        $handler = new MoveCardHandler($cardRepository, $listRepository, $cardDomainService);
        $handler->handle(new MoveCardCommand(cardId: $card->id, toListId: $newList->id, toPosition: 0));

        $card->refresh();
        $this->assertSame($newList->id, $card->list_id);
        $this->assertSame(0, $card->position);
    }

    public function test_moves_card_to_middle_of_list(): void
    {
        $board = Board::factory()->create();
        $list = ListModel::factory()->for($board)->create();
        $card1 = Card::factory()->create(['list_id' => $list->id, 'board_id' => $board->id, 'position' => 0]);
        $card2 = Card::factory()->create(['list_id' => $list->id, 'board_id' => $board->id, 'position' => 1]);
        $card3 = Card::factory()->create(['list_id' => $list->id, 'board_id' => $board->id, 'position' => 2]);
        $card4 = Card::factory()->create(['list_id' => $list->id, 'board_id' => $board->id, 'position' => 3]);

        $cardRepository = new EloquentCardRepository;
        $listRepository = new EloquentListRepository;
        $cardDomainService = new CardDomainService($cardRepository);

        $handler = new MoveCardHandler($cardRepository, $listRepository, $cardDomainService);
        $handler->handle(new MoveCardCommand(cardId: $card4->id, toListId: $list->id, toPosition: 1));

        $card4->refresh();
        $this->assertSame(1, $card4->position);
    }
}
