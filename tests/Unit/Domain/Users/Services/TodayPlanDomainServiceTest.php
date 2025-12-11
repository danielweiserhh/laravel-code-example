<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Users\Services;

use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Domain\Boards\ValueObjects\Card as DomainCard;
use App\Domain\Users\Services\TodayPlanDomainService;
use App\Models\Card;
use App\Models\User;
use Mockery;
use Tests\TestCase;

final class TodayPlanDomainServiceTest extends TestCase
{
    public function test_generate_heuristic_plan_selects_up_to_3_tasks(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $cardModels = [
            Card::factory()->make(['id' => 1, 'title' => 'Task 1', 'due_date' => now()->addDay()]),
            Card::factory()->make(['id' => 2, 'title' => 'Task 2', 'due_date' => now()->addDays(2)]),
            Card::factory()->make(['id' => 3, 'title' => 'Task 3', 'due_date' => now()->addDays(3)]),
            Card::factory()->make(['id' => 4, 'title' => 'Task 4', 'due_date' => now()->addDays(4)]),
        ];

        $cards = array_map(fn ($c) => DomainCard::fromArray([
            'id' => $c->id,
            'list_id' => $c->list_id ?? 1,
            'board_id' => $c->board_id ?? 1,
            'title' => $c->title,
            'description' => $c->description,
            'position' => $c->position ?? 1,
            'start_date' => $c->start_date?->toIso8601String(),
            'due_date' => $c->due_date?->toIso8601String(),
            'energy_level' => $c->energy_level?->value,
            'task_type' => $c->task_type?->value,
            'is_completed' => $c->is_completed ?? false,
            'completed_at' => $c->completed_at?->toIso8601String(),
            'cover_attachment_id' => $c->cover_attachment_id,
            'custom_fields' => $c->custom_fields,
            'created_at' => ($c->created_at ?? now())->toIso8601String(),
            'updated_at' => ($c->updated_at ?? now())->toIso8601String(),
        ]), $cardModels);

        $cardRepository = Mockery::mock(CardRepositoryInterface::class);
        $cardRepository->shouldReceive('getActiveCardsForUser')
            ->with(1)
            ->once()
            ->andReturn($cards);

        $service = new TodayPlanDomainService($cardRepository);

        $plan = $service->generateHeuristicPlan($user->id);

        $this->assertCount(3, $plan['big_three']);
        $this->assertArrayHasKey('note_for_user', $plan);
    }

    public function test_generate_heuristic_plan_prioritizes_urgent_tasks(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $cardModels = [
            Card::factory()->make(['id' => 1, 'title' => 'Task 1', 'due_date' => now()->addDays(3)]),
            Card::factory()->make(['id' => 2, 'title' => 'Task 2', 'due_date' => now()->addDay()]), 
            Card::factory()->make(['id' => 3, 'title' => 'Task 3', 'due_date' => now()->addDays(2)]),
        ];

        $cards = array_map(fn ($c) => DomainCard::fromArray([
            'id' => $c->id,
            'list_id' => $c->list_id ?? 1,
            'board_id' => $c->board_id ?? 1,
            'title' => $c->title,
            'description' => $c->description,
            'position' => $c->position ?? 1,
            'start_date' => $c->start_date?->toIso8601String(),
            'due_date' => $c->due_date?->toIso8601String(),
            'energy_level' => $c->energy_level?->value,
            'task_type' => $c->task_type?->value,
            'is_completed' => $c->is_completed ?? false,
            'completed_at' => $c->completed_at?->toIso8601String(),
            'cover_attachment_id' => $c->cover_attachment_id,
            'custom_fields' => $c->custom_fields,
            'created_at' => ($c->created_at ?? now())->toIso8601String(),
            'updated_at' => ($c->updated_at ?? now())->toIso8601String(),
        ]), $cardModels);

        $cardRepository = Mockery::mock(CardRepositoryInterface::class);
        $cardRepository->shouldReceive('getActiveCardsForUser')
            ->with(1)
            ->once()
            ->andReturn($cards);

        $service = new TodayPlanDomainService($cardRepository);

        $plan = $service->generateHeuristicPlan($user->id);

        
        $this->assertEquals(2, $plan['big_three'][0]['id']);
    }

    public function test_generate_heuristic_plan_handles_empty_cards(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $cards = [];

        $cardRepository = Mockery::mock(CardRepositoryInterface::class);
        $cardRepository->shouldReceive('getActiveCardsForUser')
            ->with(1)
            ->once()
            ->andReturn($cards);

        $service = new TodayPlanDomainService($cardRepository);

        $plan = $service->generateHeuristicPlan($user->id);

        $this->assertCount(0, $plan['big_three']);
        $this->assertNotEmpty($plan['note_for_user']);
    }
}
