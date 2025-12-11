<?php

declare(strict_types=1);

namespace Tests\Integration\Services;

use App\Application\AI\UseCases\AssistWithTaskCommand;
use App\Application\AI\UseCases\AssistWithTaskHandler;
use App\Application\AI\UseCases\DecomposeCardCommand;
use App\Application\AI\UseCases\DecomposeCardHandler;
use App\Application\AI\UseCases\FormatCardCommand;
use App\Application\AI\UseCases\FormatCardHandler;
use App\Application\AI\UseCases\GenerateTodayPlanCommand;
use App\Application\AI\UseCases\GenerateTodayPlanHandler;
use App\Application\AI\UseCases\ParseInboxItemCommand;
use App\Application\AI\UseCases\ParseInboxItemHandler;
use App\Application\AI\UseCases\SuggestCardRewriteCommand;
use App\Application\AI\UseCases\SuggestCardRewriteHandler;
use App\Enums\AIJobType;
use App\Enums\JobStatus;
use App\Jobs\SendAIJobToQueue;
use App\Models\Board;
use App\Models\Card;
use App\Models\InboxItem;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AIServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->instance('request_id', 'test-request-id');

        Queue::fake();
    }

    public function test_decompose_card_creates_ai_job(): void
    {
        $user = User::factory()->create();
        $card = Card::factory()->for(Board::factory()->for(Workspace::factory()))->create();

        $handler = app(DecomposeCardHandler::class);
        $job = $handler->handle(new DecomposeCardCommand(
            userId: $user->id,
            cardId: $card->id,
            language: 'en'
        ));

        $this->assertNotNull($job->id);
        $this->assertEquals(AIJobType::DECOMPOSE_CARD, $job->type);
        $this->assertEquals(JobStatus::PENDING, $job->status);
        $this->assertSame($card->board->workspace_id, $job->workspace_id);
        $this->assertEquals('en', $job->payload['language']);
        $this->assertEquals($card->id, $job->payload['card_id']);

        Queue::assertPushed(SendAIJobToQueue::class, function (SendAIJobToQueue $queuedJob) use ($job) {
            return $queuedJob->aiJob->id === $job->id;
        });
    }

    public function test_generate_today_plan_creates_ai_job(): void
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()->hasAttached($workspace)->create();

        $handler = app(GenerateTodayPlanHandler::class);
        $job = $handler->handle(new GenerateTodayPlanCommand(
            userId: $user->id,
            workspaceId: $workspace->id,
            language: 'ru'
        ));

        $this->assertEquals(AIJobType::GENERATE_TODAY_PLAN, $job->type);
        $this->assertEquals($workspace->id, $job->workspace_id);
        $this->assertEquals('ru', $job->payload['language']);

        Queue::assertPushed(SendAIJobToQueue::class, fn (SendAIJobToQueue $queuedJob) => $queuedJob->aiJob->id === $job->id);
    }

    public function test_assist_with_task_creates_ai_job(): void
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()->create();

        $handler = app(AssistWithTaskHandler::class);
        $job = $handler->handle(new AssistWithTaskCommand(
            userId: $user->id,
            workspaceId: $workspace->id,
            assistType: 'start_task',
            cardId: 42,
            contextText: 'Очень большая задача',
            language: 'ru'
        ));

        $this->assertEquals(AIJobType::ASSIST_WITH_TASK, $job->type);
        $this->assertEquals('start_task', $job->payload['assist_type']);
        $this->assertEquals(42, $job->payload['card_id']);
        $this->assertEquals('Очень большая задача', $job->payload['context']);
        $this->assertEquals('ru', $job->payload['language']);
        $this->assertEquals($workspace->id, $job->workspace_id);

        Queue::assertPushed(SendAIJobToQueue::class, fn (SendAIJobToQueue $queuedJob) => $queuedJob->aiJob->id === $job->id);
    }

    public function test_suggest_for_inbox_item_creates_ai_job(): void
    {
        $workspace = Workspace::factory()->create();
        $item = InboxItem::factory()->for($workspace)->create();

        $handler = app(ParseInboxItemHandler::class);
        $job = $handler->handle(new ParseInboxItemCommand(
            inboxItemId: $item->id,
            language: 'en'
        ));

        $this->assertEquals(AIJobType::PARSE_INBOX_ITEM, $job->type);
        $this->assertEquals($workspace->id, $job->workspace_id);
        $this->assertEquals($item->id, $job->payload['inbox_item_id']);
        $this->assertEquals($item->content, $job->payload['content']);
        $this->assertEquals('en', $job->payload['language']);

        Queue::assertPushed(SendAIJobToQueue::class, fn (SendAIJobToQueue $queuedJob) => $queuedJob->aiJob->id === $job->id);
    }

    public function test_suggest_card_rewrite_creates_ai_job(): void
    {
        $card = Card::factory()->for(Board::factory()->for(Workspace::factory()))->create([
            'title' => 'Большая задача',
            'description' => 'Нужно всё переписать',
        ]);
        $user = User::factory()->create();

        $handler = app(SuggestCardRewriteHandler::class);
        $job = $handler->handle(new SuggestCardRewriteCommand(
            userId: $user->id,
            cardId: $card->id,
            fields: ['title'],
            language: 'ru'
        ));

        $this->assertEquals(AIJobType::SUGGEST_CARD_REWRITE, $job->type);
        $this->assertEquals($card->board->workspace_id, $job->workspace_id);
        $this->assertEquals(['title'], $job->payload['fields']);
        $this->assertEquals('ru', $job->payload['language']);
        $this->assertEquals($card->title, $job->payload['current']['title']);

        Queue::assertPushed(SendAIJobToQueue::class, fn (SendAIJobToQueue $queuedJob) => $queuedJob->aiJob->id === $job->id);
    }

    public function test_format_card_creates_ai_job(): void
    {
        $workspace = Workspace::factory()->create();
        $user = User::factory()->create();

        $handler = app(FormatCardHandler::class);
        $job = $handler->handle(new FormatCardCommand(
            userId: $user->id,
            workspaceId: $workspace->id,
            title: 'Test title',
            description: 'Test description',
            language: 'ru',
            model: 'gpt-4'
        ));

        $this->assertEquals(AIJobType::CARD_FORMAT, $job->type);
        $this->assertEquals($workspace->id, $job->workspace_id);
        $this->assertEquals('Test title', $job->payload['title']);
        $this->assertEquals('Test description', $job->payload['description']);
        $this->assertEquals('ru', $job->payload['language']);
        $this->assertEquals('gpt-4', $job->payload['model']);

        Queue::assertPushed(SendAIJobToQueue::class, fn (SendAIJobToQueue $queuedJob) => $queuedJob->aiJob->id === $job->id);
    }
}
