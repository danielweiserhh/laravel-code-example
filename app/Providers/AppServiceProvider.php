<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\AI\Repositories\AIJobRepositoryInterface;
use App\Domain\Boards\Repositories\BoardRepositoryInterface;
use App\Domain\Boards\Repositories\CardRepositoryInterface;
use App\Domain\Boards\Repositories\ChecklistRepositoryInterface;
use App\Domain\Boards\Repositories\ListRepositoryInterface;
use App\Domain\Comments\Repositories\CommentRepositoryInterface;
use App\Domain\FocusSessions\Repositories\FocusSessionRepositoryInterface;
use App\Domain\Inbox\Repositories\InboxItemRepositoryInterface;
use App\Domain\Routines\Repositories\RoutineExecutionRepositoryInterface;
use App\Domain\Routines\Repositories\RoutineRepositoryInterface;
use App\Domain\Speech\SpeechTranscriberInterface;
use App\Domain\Users\Repositories\UserDailyPlanRepositoryInterface;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Domain\Workspaces\Repositories\WorkspaceRepositoryInterface;
use App\Infrastructure\Persistence\AI\EloquentAIJobRepository;
use App\Infrastructure\Persistence\Boards\EloquentBoardRepository;
use App\Infrastructure\Persistence\Boards\EloquentCardRepository;
use App\Infrastructure\Persistence\Boards\EloquentChecklistRepository;
use App\Infrastructure\Persistence\Boards\EloquentListRepository;
use App\Infrastructure\Persistence\Comments\EloquentCommentRepository;
use App\Infrastructure\Persistence\FocusSessions\EloquentFocusSessionRepository;
use App\Infrastructure\Persistence\Inbox\EloquentInboxItemRepository;
use App\Infrastructure\Persistence\Routines\EloquentRoutineExecutionRepository;
use App\Infrastructure\Persistence\Routines\EloquentRoutineRepository;
use App\Infrastructure\Persistence\Users\EloquentUserDailyPlanRepository;
use App\Infrastructure\Persistence\Users\EloquentUserRepository;
use App\Infrastructure\Persistence\Workspaces\EloquentWorkspaceRepository;
use App\Infrastructure\Speech\GoSpeechClient;
use App\Models\Board;
use App\Models\Card;
use App\Models\Comment;
use App\Models\FocusSession;
use App\Models\InboxItem;
use App\Models\Routine;
use App\Models\Workspace;
use App\Policies\BoardPolicy;
use App\Policies\CardPolicy;
use App\Policies\CommentPolicy;
use App\Policies\FocusSessionPolicy;
use App\Policies\InboxItemPolicy;
use App\Policies\RoutinePolicy;
use App\Policies\WorkspacePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Client\Factory as HttpClient;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Workspace::class => WorkspacePolicy::class,
        Board::class => BoardPolicy::class,
        Card::class => CardPolicy::class,
        InboxItem::class => InboxItemPolicy::class,
        Comment::class => CommentPolicy::class,
        FocusSession::class => FocusSessionPolicy::class,
        Routine::class => RoutinePolicy::class,
    ];

    public function register(): void
    {
        $this->app->bind(AIJobRepositoryInterface::class, EloquentAIJobRepository::class);
        $this->app->bind(BoardRepositoryInterface::class, EloquentBoardRepository::class);
        $this->app->bind(ListRepositoryInterface::class, EloquentListRepository::class);
        $this->app->bind(CardRepositoryInterface::class, EloquentCardRepository::class);
        $this->app->bind(ChecklistRepositoryInterface::class, EloquentChecklistRepository::class);
        $this->app->bind(CommentRepositoryInterface::class, EloquentCommentRepository::class);
        $this->app->bind(InboxItemRepositoryInterface::class, EloquentInboxItemRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(UserDailyPlanRepositoryInterface::class, EloquentUserDailyPlanRepository::class);
        $this->app->bind(WorkspaceRepositoryInterface::class, EloquentWorkspaceRepository::class);
        $this->app->bind(RoutineRepositoryInterface::class, EloquentRoutineRepository::class);
        $this->app->bind(RoutineExecutionRepositoryInterface::class, EloquentRoutineExecutionRepository::class);
        $this->app->bind(FocusSessionRepositoryInterface::class, EloquentFocusSessionRepository::class);

        $this->app->bind(SpeechTranscriberInterface::class, function ($app) {
            return new GoSpeechClient(
                http: $app->make(HttpClient::class),
                logger: $app->make(\Psr\Log\LoggerInterface::class),
                serviceUrl: config('services.speech.url', 'http://go-speech:8083'),
                timeout: (int) config('services.speech.timeout', 60)
            );
        });
    }

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
