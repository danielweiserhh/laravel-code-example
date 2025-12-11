<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\FocusSessions\Queries\GetFocusSessionsHandler;
use App\Application\FocusSessions\Queries\GetFocusSessionsQuery;
use App\Application\FocusSessions\UseCases\CompleteFocusSessionCommand;
use App\Application\FocusSessions\UseCases\CompleteFocusSessionHandler;
use App\Application\FocusSessions\UseCases\CreateFocusSessionCommand;
use App\Application\FocusSessions\UseCases\CreateFocusSessionHandler;
use App\Application\FocusSessions\UseCases\JoinFocusSessionCommand;
use App\Application\FocusSessions\UseCases\JoinFocusSessionHandler;
use App\Application\FocusSessions\UseCases\PauseFocusSessionCommand;
use App\Application\FocusSessions\UseCases\PauseFocusSessionHandler;
use App\Application\FocusSessions\UseCases\StartFocusSessionCommand;
use App\Application\FocusSessions\UseCases\StartFocusSessionHandler;
use App\Domain\FocusSessions\Repositories\FocusSessionRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFocusSessionRequest;
use App\Models\FocusSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FocusSessionController extends Controller
{
    public function __construct(
        protected GetFocusSessionsHandler $getSessions,
        protected CreateFocusSessionHandler $createSession,
        protected StartFocusSessionHandler $startSession,
        protected PauseFocusSessionHandler $pauseSession,
        protected CompleteFocusSessionHandler $completeSession,
        protected JoinFocusSessionHandler $joinSession,
        protected FocusSessionRepositoryInterface $sessionRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetFocusSessionsQuery(
            userId: $request->user()->id,
        );

        $sessions = $this->getSessions->handle($query);
        
        $data = array_map(fn ($session) => $session->toArray(), $sessions);

        return $this->success($data);
    }

    public function store(StoreFocusSessionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $command = new CreateFocusSessionCommand(
            userId: $request->user()->id,
            cardId: isset($validated['card_id']) ? (int) $validated['card_id'] : null,
            durationMinutes: (int) ($validated['duration_minutes'] ?? 25),
            isGroup: (bool) ($validated['is_group'] ?? false),
            videoLink: $validated['video_link'] ?? null,
        );

        $session = $this->createSession->handle($command);

        return $this->created($session->toArray(), 'Сессия фокуса создана');
    }

    public function show(FocusSession $focusSession): JsonResponse
    {
        $this->authorize('view', $focusSession);
        
        $domainSession = $this->sessionRepository->findOrFail($focusSession->id);

        return $this->success($domainSession->toArray());
    }

    public function start(FocusSession $focusSession): JsonResponse
    {
        $this->authorize('update', $focusSession);

        $command = new StartFocusSessionCommand(
            sessionId: $focusSession->id,
        );

        $session = $this->startSession->handle($command);

        return $this->success($session->toArray(), 'Сессия запущена');
    }

    public function pause(FocusSession $focusSession): JsonResponse
    {
        $this->authorize('update', $focusSession);

        $command = new PauseFocusSessionCommand(
            sessionId: $focusSession->id,
        );

        $session = $this->pauseSession->handle($command);

        return $this->success($session->toArray(), 'Сессия приостановлена');
    }

    public function complete(FocusSession $focusSession): JsonResponse
    {
        $this->authorize('update', $focusSession);

        $command = new CompleteFocusSessionCommand(
            sessionId: $focusSession->id,
        );

        $session = $this->completeSession->handle($command);

        return $this->success($session->toArray(), 'Сессия завершена');
    }

    public function join(FocusSession $focusSession, Request $request): JsonResponse
    {
        $this->authorize('view', $focusSession);

        $command = new JoinFocusSessionCommand(
            sessionId: $focusSession->id,
            userId: $request->user()->id,
        );

        $session = $this->joinSession->handle($command);

        return $this->success($session->toArray(), 'Вы присоединились к сессии');
    }
}
