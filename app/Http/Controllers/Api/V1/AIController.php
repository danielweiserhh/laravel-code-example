<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\AI\UseCases\AssistWithTaskCommand;
use App\Application\AI\UseCases\AssistWithTaskHandler;
use App\Application\AI\UseCases\DecomposeCardCommand;
use App\Application\AI\UseCases\DecomposeCardFormCommand;
use App\Application\AI\UseCases\DecomposeCardFormHandler;
use App\Application\AI\UseCases\DecomposeCardHandler;
use App\Application\AI\UseCases\FormatCardCommand;
use App\Application\AI\UseCases\FormatCardHandler;
use App\Application\AI\UseCases\GenerateTodayPlanCommand;
use App\Application\AI\UseCases\GenerateTodayPlanHandler;
use App\Application\AI\UseCases\ImproveCardCommand;
use App\Application\AI\UseCases\ImproveCardHandler;
use App\Application\AI\UseCases\ParseInboxItemCommand;
use App\Application\AI\UseCases\ParseInboxItemHandler;
use App\Application\AI\UseCases\SuggestCardRewriteCommand;
use App\Application\AI\UseCases\SuggestCardRewriteHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AISuggestRequest;
use App\Http\Requests\Api\V1\AssistRequest;
use App\Http\Requests\Api\V1\DecomposeCardRequest;
use App\Models\AIJob;
use App\Models\Card;
use Illuminate\Http\JsonResponse;

class AIController extends Controller
{
    public function __construct(
        private readonly DecomposeCardHandler $decomposeCardHandler,
        private readonly ParseInboxItemHandler $parseInboxItemHandler,
        private readonly GenerateTodayPlanHandler $generateTodayPlanHandler,
        private readonly AssistWithTaskHandler $assistWithTaskHandler,
        private readonly SuggestCardRewriteHandler $suggestCardRewriteHandler,
        private readonly FormatCardHandler $formatCardHandler,
        private readonly DecomposeCardFormHandler $decomposeCardFormHandler,
        private readonly ImproveCardHandler $improveCardHandler
    ) {}

    public function decomposeCard(DecomposeCardRequest $request, Card $card): JsonResponse
    {
        $this->authorize('view', $card);

        $aiJob = $this->decomposeCardHandler->handle(
            new DecomposeCardCommand(
                userId: $request->user()->id,
                cardId: $card->id,
                language: $request->input('language', 'ru')
            )
        );

        return $this->accepted(
            ['job_id' => $aiJob->id],
            'Декомпозиция начата'
        );
    }

    public function assist(AssistRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $aiJob = $this->assistWithTaskHandler->handle(
            new AssistWithTaskCommand(
                userId: $request->user()->id,
                workspaceId: $validated['workspace_id'] ?? null,
                assistType: $validated['assist_type'],
                cardId: $validated['card_id'] ?? null,
                contextText: $validated['context'] ?? null,
                language: $validated['language'] ?? 'ru'
            )
        );

        return $this->accepted(
            ['job_id' => $aiJob->id],
            'Запрос на помощь обрабатывается'
        );
    }

    public function getJobStatus(AIJob $aiJob): JsonResponse
    {
        return $this->success($aiJob);
    }

    public function suggest(AISuggestRequest $request): JsonResponse
    {
        $user = $request->user();
        $contextType = $request->input('context_type');
        $contextId = $request->input('context_id');
        $options = $request->input('options', []);

        $aiJob = match ($contextType) {
            'inbox_item' => $this->handleInboxSuggestion((int) $contextId, $options),
            'today_plan' => $this->handleTodayPlanSuggestion($user->id, $options),
            'card_steps' => $this->handleCardStepsSuggestion($user->id, (int) $contextId, $options),
            'card_rewrite' => $this->handleCardRewriteSuggestion($user->id, (int) $contextId, $options),
            'card_assist' => $this->handleCardAssistSuggestion($user->id, (int) $contextId, $options),
            'card_format' => $this->handleCardFormat($user, $options),
            'card_decompose' => $this->handleCardDecompose($user, $options),
            'card_improve' => $this->handleCardImprove($user, $options),
            default => null,
        };

        if (! $aiJob) {
            return $this->error('Неподдерживаемый тип контекста', 422);
        }

        return $this->accepted(
            [
                'job_id' => $aiJob->id,
                'status' => $aiJob->status->value,
            ],
            'Задача создана'
        );
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function handleInboxSuggestion(int $inboxItemId, array $options): AIJob
    {
        return $this->parseInboxItemHandler->handle(
            new ParseInboxItemCommand(
                inboxItemId: $inboxItemId,
                language: $options['language'] ?? 'ru'
            )
        );
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function handleTodayPlanSuggestion(int $userId, array $options): AIJob
    {
        return $this->generateTodayPlanHandler->handle(
            new GenerateTodayPlanCommand(
                userId: $userId,
                workspaceId: $options['workspace_id'] ?? null,
                language: $options['language'] ?? 'ru'
            )
        );
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function handleCardStepsSuggestion(int $userId, int $cardId, array $options): AIJob
    {
        $card = Card::findOrFail($cardId);
        $this->authorize('view', $card);

        return $this->decomposeCardHandler->handle(
            new DecomposeCardCommand(
                userId: $userId,
                cardId: $cardId,
                language: $options['language'] ?? 'ru'
            )
        );
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function handleCardRewriteSuggestion(int $userId, int $cardId, array $options): AIJob
    {
        $card = Card::findOrFail($cardId);
        $this->authorize('update', $card);

        return $this->suggestCardRewriteHandler->handle(
            new SuggestCardRewriteCommand(
                userId: $userId,
                cardId: $cardId,
                fields: $options['fields'] ?? ['title', 'description'],
                language: $options['language'] ?? 'ru'
            )
        );
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function handleCardAssistSuggestion(int $userId, int $cardId, array $options): AIJob
    {
        $card = Card::findOrFail($cardId);
        $this->authorize('view', $card);

        $card->loadMissing('board');

        return $this->assistWithTaskHandler->handle(
            new AssistWithTaskCommand(
                userId: $userId,
                workspaceId: $card->board?->workspace_id,
                assistType: $options['assist_type'] ?? 'start_task',
                cardId: $cardId,
                contextText: $options['context'] ?? $card->description ?? $card->title ?? '',
                language: $options['language'] ?? 'ru'
            )
        );
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function handleCardFormat(\App\Models\User $user, array $options): AIJob
    {
        return $this->formatCardHandler->handle(
            new FormatCardCommand(
                userId: $user->id,
                workspaceId: $options['workspace_id'] ?? null,
                title: $options['title'] ?? '',
                description: $options['description'] ?? '',
                language: $options['language'] ?? 'ru',
                model: $options['model'] ?? $user->ai_model ?? null
            )
        );
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function handleCardDecompose(\App\Models\User $user, array $options): AIJob
    {
        return $this->decomposeCardFormHandler->handle(
            new DecomposeCardFormCommand(
                userId: $user->id,
                workspaceId: $options['workspace_id'] ?? null,
                title: $options['title'] ?? '',
                description: $options['description'] ?? '',
                language: $options['language'] ?? 'ru',
                model: $options['model'] ?? $user->ai_model ?? null
            )
        );
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function handleCardImprove(\App\Models\User $user, array $options): AIJob
    {
        return $this->improveCardHandler->handle(
            new ImproveCardCommand(
                userId: $user->id,
                workspaceId: $options['workspace_id'] ?? null,
                title: $options['title'] ?? '',
                description: $options['description'] ?? '',
                language: $options['language'] ?? 'ru',
                model: $options['model'] ?? $user->ai_model ?? null
            )
        );
    }
}
