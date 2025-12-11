<?php

declare(strict_types=1);

namespace Tests\Unit\Application\AI\UseCases;

use App\Application\AI\UseCases\CreateAIJobHandler;
use App\Application\AI\UseCases\DecomposeCardCommand;
use App\Application\AI\UseCases\DecomposeCardHandler;
use App\Domain\AI\Repositories\AIJobRepositoryInterface;
use App\Domain\Boards\Repositories\BoardRepositoryInterface;
use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Domain\Boards\ValueObjects\Board as DomainBoard;
use App\Domain\Boards\ValueObjects\Card as DomainCard;
use Psr\Log\LoggerInterface;
use App\Enums\AIJobType;
use App\Enums\JobStatus;
use App\Models\AIJob;
use App\Models\Board;
use App\Models\Card;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

final class DecomposeCardHandlerTest extends TestCase
{
    public function test_handle_creates_ai_job_for_card_decomposition(): void
    {
        app()->instance('request_id', 'test-request-id');

        $board = Board::factory()->make(['id' => 1, 'workspace_id' => 10]);
        $cardModel = Card::factory()->make(['id' => 100, 'board_id' => 1]);
        $cardModel->setRelation('board', $board);
        $cardModel->created_at = now();
        $cardModel->updated_at = now();

        $card = DomainCard::fromArray([
            'id' => 100,
            'list_id' => $cardModel->list_id ?? 1,
            'board_id' => 1,
            'title' => $cardModel->title ?? 'Test Card',
            'description' => $cardModel->description,
            'position' => $cardModel->position ?? 1,
            'start_date' => $cardModel->start_date?->toIso8601String(),
            'due_date' => $cardModel->due_date?->toIso8601String(),
            'energy_level' => $cardModel->energy_level?->value,
            'task_type' => $cardModel->task_type?->value,
            'is_completed' => $cardModel->is_completed ?? false,
            'completed_at' => $cardModel->completed_at?->toIso8601String(),
            'cover_attachment_id' => $cardModel->cover_attachment_id,
            'custom_fields' => $cardModel->custom_fields,
            'created_at' => $cardModel->created_at->toIso8601String(),
            'updated_at' => $cardModel->updated_at->toIso8601String(),
        ]);

        $aiJob = AIJob::factory()->make([
            'id' => 'test-job-id',
            'type' => AIJobType::DECOMPOSE_CARD,
        ]);

        $domainBoard = DomainBoard::fromArray([
            'id' => 1,
            'workspace_id' => 10,
            'name' => 'Test Board',
            'description' => null,
            'color' => null,
            'privacy' => 'private',
            'is_favorite' => false,
            'settings' => null,
            'position' => 0,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);

        $cardRepository = Mockery::mock(CardRepositoryInterface::class);
        $cardRepository->shouldReceive('findOrFailWithBoard')
            ->with(100)
            ->once()
            ->andReturn($card);

        $boardRepository = Mockery::mock(BoardRepositoryInterface::class);
        $boardRepository->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($domainBoard);
        
        Queue::fake();

        $aiJobRepository = Mockery::mock(AIJobRepositoryInterface::class);
        $aiJobRepository->shouldReceive('create')
            ->with(Mockery::on(function (array $data) {
                return $data['user_id'] === 1
                    && $data['workspace_id'] === 10
                    && $data['type'] === AIJobType::DECOMPOSE_CARD
                    && $data['status'] === JobStatus::PENDING
                    && $data['payload']['card_id'] === 100
                    && $data['payload']['language'] === 'ru';
            }))
            ->once()
            ->andReturn($aiJob);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->once();

        $createAIJobHandler = new CreateAIJobHandler($aiJobRepository, $logger);

        $handler = new DecomposeCardHandler($cardRepository, $boardRepository, $createAIJobHandler);

        $command = new DecomposeCardCommand(
            userId: 1,
            cardId: 100,
            language: 'ru'
        );

        $result = $handler->handle($command);

        $this->assertInstanceOf(AIJob::class, $result);
        $this->assertEquals('test-job-id', $result->id);
    }
}
