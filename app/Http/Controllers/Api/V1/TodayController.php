<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Today\Queries\GetTodayPlanHandler;
use App\Application\Today\Queries\GetTodayPlanQuery;
use App\Application\Today\Queries\GetTodaySuggestionsHandler;
use App\Application\Today\Queries\GetTodaySuggestionsQuery;
use App\Application\Today\UseCases\AcceptTodayPlanCommand;
use App\Application\Today\UseCases\AcceptTodayPlanHandler;
use App\Application\Today\UseCases\AddInboxItemsToTodayCommand;
use App\Application\Today\UseCases\AddInboxItemsToTodayHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AcceptTodayPlanRequest;
use App\Http\Requests\Api\V1\AddInboxItemsToTodayRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodayController extends Controller
{
    public function __construct(
        private readonly GetTodayPlanHandler $getTodayPlanHandler,
        private readonly AcceptTodayPlanHandler $acceptTodayPlanHandler,
        private readonly AddInboxItemsToTodayHandler $addInboxItemsHandler,
        private readonly GetTodaySuggestionsHandler $getTodaySuggestionsHandler
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetTodayPlanQuery($request->user()->id);
        $result = $this->getTodayPlanHandler->handle($query);

        return $this->success($result);
    }

    public function store(AcceptTodayPlanRequest $request): JsonResponse
    {
        $command = new AcceptTodayPlanCommand(
            userId: $request->user()->id,
            bigThree: $request->input('big_three', []),
            noteForUser: $request->input('note_for_user'),
            jobId: $request->input('job_id')
        );

        $plan = $this->acceptTodayPlanHandler->handle($command);

        return $this->success(['plan' => $plan], 'План сохранен');
    }

    public function addInboxItems(AddInboxItemsToTodayRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $command = new AddInboxItemsToTodayCommand(
            userId: $request->user()->id,
            inboxItemIds: $validated['inbox_item_ids']
        );

        $cards = $this->addInboxItemsHandler->handle($command);

        return $this->success(['cards' => $cards], 'Задачи добавлены на сегодня');
    }

    public function getOtherSuggestions(Request $request): JsonResponse
    {
        $excludedIds = $request->input('excluded_ids', []);
        $query = new GetTodaySuggestionsQuery(
            userId: $request->user()->id,
            excludedIds: $excludedIds
        );

        $suggestions = $this->getTodaySuggestionsHandler->handle($query);

        return $this->success(['suggestions' => $suggestions]);
    }
}
