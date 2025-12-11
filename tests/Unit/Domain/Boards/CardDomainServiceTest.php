<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Boards;

use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Domain\Boards\Services\CardDomainService;
use App\Domain\Boards\ValueObjects\CardData;
use App\Domain\Boards\ValueObjects\CardPositionData;
use App\Domain\Boards\ValueObjects\ChecklistData;
use App\Domain\Boards\ValueObjects\ChecklistDataCollection;
use App\Domain\Boards\ValueObjects\ChecklistItemData;
use App\Domain\Boards\ValueObjects\ChecklistItemDataCollection;
use App\Domain\Exceptions\InvalidDateRangeException;
use App\Domain\Exceptions\InvalidPositionException;
use App\Domain\Exceptions\InvariantViolationException;
use Carbon\Carbon;
use Tests\TestCase;

class CardDomainServiceTest extends TestCase
{
    private CardRepositoryInterface $cardRepository;
    private CardDomainService $cardDomainService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cardRepository = $this->mock(CardRepositoryInterface::class);
        $this->cardDomainService = new CardDomainService($this->cardRepository);
    }

    public function test_calculate_next_position_with_provided_max_position(): void
    {
        $listId = 1;
        $maxPosition = 5;

        $result = $this->cardDomainService->calculateNextPosition($listId, $maxPosition);

        $this->assertSame(6, $result);
    }

    public function test_calculate_next_position_with_null_max_position_empty_list(): void
    {
        $listId = 1;

        $this->cardRepository
            ->shouldReceive('getMaxPositionForList')
            ->once()
            ->with($listId)
            ->andReturn(0);

        $result = $this->cardDomainService->calculateNextPosition($listId, null);

        $this->assertSame(1, $result);
    }

    public function test_calculate_next_position_with_null_max_position_list_with_cards(): void
    {
        $listId = 1;

        $this->cardRepository
            ->shouldReceive('getMaxPositionForList')
            ->once()
            ->with($listId)
            ->andReturn(10);

        $result = $this->cardDomainService->calculateNextPosition($listId, null);

        $this->assertSame(11, $result);
    }

    public function test_validate_energy_level_returns_null_for_null(): void
    {
        $result = $this->cardDomainService->validateEnergyLevel(null);

        $this->assertNull($result);
    }

    public function test_validate_energy_level_normalizes_to_minimum(): void
    {
        $result = $this->cardDomainService->validateEnergyLevel(0);

        $this->assertSame(1, $result);
    }

    public function test_validate_energy_level_normalizes_to_maximum(): void
    {
        $result = $this->cardDomainService->validateEnergyLevel(10);

        $this->assertSame(5, $result);
    }

    public function test_validate_energy_level_keeps_valid_values(): void
    {
        $this->assertSame(3, $this->cardDomainService->validateEnergyLevel(3));
        $this->assertSame(1, $this->cardDomainService->validateEnergyLevel(1));
        $this->assertSame(5, $this->cardDomainService->validateEnergyLevel(5));
    }

    public function test_validate_deadlines_throws_when_due_before_start(): void
    {
        $startDate = Carbon::parse('2024-01-15');
        $dueDate = Carbon::parse('2024-01-10');

        $this->expectException(InvalidDateRangeException::class);

        $this->cardDomainService->validateDeadlines($startDate, $dueDate);
    }

    public function test_validate_deadlines_allows_when_due_after_start(): void
    {
        $startDate = Carbon::parse('2024-01-10');
        $dueDate = Carbon::parse('2024-01-15');

        $this->cardDomainService->validateDeadlines($startDate, $dueDate);
    }

    public function test_validate_deadlines_allows_null_dates(): void
    {
        $this->cardDomainService->validateDeadlines(null, null);
        $this->cardDomainService->validateDeadlines(Carbon::parse('2024-01-10'), null);
        $this->cardDomainService->validateDeadlines(null, Carbon::parse('2024-01-15'));
    }

    public function test_is_overdue_returns_false_when_no_due_date(): void
    {
        $card = new CardData(
            id: 1,
            dueDate: null,
            isCompleted: false,
            checklists: new ChecklistDataCollection([])
        );

        $result = $this->cardDomainService->isOverdue($card);

        $this->assertFalse($result);
    }

    public function test_is_overdue_returns_false_when_completed(): void
    {
        $card = new CardData(
            id: 1,
            dueDate: Carbon::yesterday(),
            isCompleted: true,
            checklists: new ChecklistDataCollection([])
        );

        $result = $this->cardDomainService->isOverdue($card);

        $this->assertFalse($result);
    }

    public function test_is_overdue_returns_true_when_past_due(): void
    {
        $card = new CardData(
            id: 1,
            dueDate: Carbon::yesterday(),
            isCompleted: false,
            checklists: new ChecklistDataCollection([])
        );

        $result = $this->cardDomainService->isOverdue($card);

        $this->assertTrue($result);
    }

    public function test_is_overdue_returns_false_when_future_due(): void
    {
        $card = new CardData(
            id: 1,
            dueDate: Carbon::tomorrow(),
            isCompleted: false,
            checklists: new ChecklistDataCollection([])
        );

        $result = $this->cardDomainService->isOverdue($card);

        $this->assertFalse($result);
    }

    public function test_calculate_completion_percentage_returns_zero_for_no_checklists(): void
    {
        $card = new CardData(
            id: 1,
            dueDate: null,
            isCompleted: false,
            checklists: new ChecklistDataCollection([])
        );

        $result = $this->cardDomainService->calculateCompletionPercentage($card);

        $this->assertSame(0.0, $result);
    }

    public function test_calculate_completion_percentage_calculates_correctly(): void
    {
        $checklist1 = new ChecklistData(
            id: 1,
            items: new ChecklistItemDataCollection([
                new ChecklistItemData(id: 1, isCompleted: true),
                new ChecklistItemData(id: 2, isCompleted: false),
            ])
        );

        $checklist2 = new ChecklistData(
            id: 2,
            items: new ChecklistItemDataCollection([
                new ChecklistItemData(id: 3, isCompleted: true),
            ])
        );

        $card = new CardData(
            id: 1,
            dueDate: null,
            isCompleted: false,
            checklists: new ChecklistDataCollection([$checklist1, $checklist2])
        );

        $result = $this->cardDomainService->calculateCompletionPercentage($card);

        $this->assertSame(66.67, $result);
    }

    public function test_validate_position_throws_when_negative(): void
    {
        $this->expectException(InvariantViolationException::class);

        $this->cardDomainService->validatePosition(-1, 1);
    }

    public function test_validate_position_throws_when_too_large(): void
    {
        $listId = 1;
        $position = 100;

        $this->cardRepository
            ->shouldReceive('getMaxPositionForListExcluding')
            ->once()
            ->with($listId, null)
            ->andReturn(5);

        $this->expectException(InvalidPositionException::class);

        $this->cardDomainService->validatePosition($position, $listId);
    }

    public function test_validate_position_allows_valid_position(): void
    {
        $listId = 1;
        $position = 3;

        $this->cardRepository
            ->shouldReceive('getMaxPositionForListExcluding')
            ->once()
            ->with($listId, null)
            ->andReturn(5);

        $this->cardDomainService->validatePosition($position, $listId);
    }

    public function test_validate_position_allows_appending_to_end(): void
    {
        $listId = 1;
        $position = 6; 

        $this->cardRepository
            ->shouldReceive('getMaxPositionForListExcluding')
            ->once()
            ->with($listId, null)
            ->andReturn(5);

        $this->cardDomainService->validatePosition($position, $listId);
    }

    public function test_validate_position_with_exclude_card_id(): void
    {
        $listId = 1;
        $position = 3;
        $excludeCardId = 10;

        $this->cardRepository
            ->shouldReceive('getMaxPositionForListExcluding')
            ->once()
            ->with($listId, $excludeCardId)
            ->andReturn(5);

        $this->cardDomainService->validatePosition($position, $listId, $excludeCardId);
    }

    public function test_calculate_new_positions_reorders_correctly(): void
    {
        $card1 = new CardPositionData(id: 1, position: 0);
        $card2 = new CardPositionData(id: 2, position: 1);
        $card3 = new CardPositionData(id: 3, position: 2);

        $cards = [$card1, $card2, $card3];
        $movedCardId = 2;
        $newPosition = 0; 

        $result = $this->cardDomainService->calculateNewPositions($cards, $movedCardId, $newPosition);
        
        $this->assertSame([1 => 1, 3 => 2], $result);
    }
}
