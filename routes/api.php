<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AIController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BoardController;
use App\Http\Controllers\Api\V1\CardController;
use App\Http\Controllers\Api\V1\ChecklistController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\FocusSessionController;
use App\Http\Controllers\Api\V1\InboxController;
use App\Http\Controllers\Api\V1\ListController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RoutineController;
use App\Http\Controllers\Api\V1\SocialAuthController;
use App\Http\Controllers\Api\V1\TodayController;
use App\Http\Controllers\Api\V1\VoiceController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Controllers\Api\V1\WorkspaceController;
use App\Http\Controllers\MetricsController;
use App\Http\Middleware\RefreshTokenExpiry;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/metrics', [MetricsController::class, 'metrics'])->name('metrics');

Route::prefix('v1')->group(function () {
    Route::get('/health', [HealthController::class, 'index'])->name('health');
    Route::get('/health/full', [HealthController::class, 'full'])->name('health.full');

    Route::middleware('throttle:5,1')
        ->name('auth.')
        ->group(function () {
            Route::post('/register', [AuthController::class, 'register'])->name('register');
            Route::post('/login', [AuthController::class, 'login'])->name('login');
            Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])->name('password.forgot');
            Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
        });

    Route::prefix('auth')
        ->name('social.')
        ->group(function () {
            Route::get('/google', [SocialAuthController::class, 'redirectToGoogle'])->name('google');
            Route::get('/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('google.callback');
        });

    Route::prefix('webhooks')
        ->name('webhooks.')
        ->group(function () {
            Route::post('/ai-job-completed', [WebhookController::class, 'aiJobCompleted'])
                ->name('ai-job-completed');
        });

    Route::middleware(['auth:sanctum', RefreshTokenExpiry::class])->group(function () {
        Route::name('auth.')
            ->group(function () {
                Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
                Route::get('/user', [AuthController::class, 'me'])->name('me');
            });

        Route::prefix('profile')
            ->name('profile.')
            ->group(function () {
                Route::get('/', [ProfileController::class, 'show'])->name('show');
                Route::put('/', [ProfileController::class, 'update'])->name('update');
                Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
            });

        Route::apiResource('workspaces', WorkspaceController::class);

        Route::apiResource('boards', BoardController::class);

        Route::apiResource('lists', ListController::class);

        Route::apiResource('cards', CardController::class);
        Route::post('/cards/{card}/move', [CardController::class, 'move'])->name('cards.move');

        Route::prefix('cards/{card}/ai')
            ->name('cards.ai.')
            ->group(function () {
                Route::post('/decompose', [AIController::class, 'decomposeCard'])->name('decompose');
            });

        Route::prefix('today')
            ->name('today.')
            ->group(function () {
                Route::get('/', [TodayController::class, 'index'])->name('index');
                Route::post('/plan', [TodayController::class, 'store'])->name('plan');
                Route::post('/add-inbox-items', [TodayController::class, 'addInboxItems'])->name('add-inbox-items');
                Route::get('/other-suggestions', [TodayController::class, 'getOtherSuggestions'])->name('other-suggestions');
            });

        Route::prefix('inbox')
            ->name('inbox.')
            ->group(function () {
                Route::get('/', [InboxController::class, 'index'])->name('index');
                Route::post('/', [InboxController::class, 'store'])->name('store');
                Route::put('/{inboxItem}', [InboxController::class, 'update'])->name('update');
                Route::delete('/{inboxItem}', [InboxController::class, 'destroy'])->name('destroy');
                Route::post('/{inboxItem}/parse', [InboxController::class, 'parseWithAI'])->name('parse');
            });

        Route::prefix('voice')
            ->name('voice.')
            ->group(function () {
                Route::post('/transcribe', [VoiceController::class, 'transcribe'])->name('transcribe');
            });

        Route::prefix('ai')
            ->name('ai.')
            ->group(function () {
                Route::post('/assist', [AIController::class, 'assist'])->name('assist');
                Route::post('/suggest', [AIController::class, 'suggest'])->name('suggest');
                Route::get('/jobs/{aiJob}', [AIController::class, 'getJobStatus'])->name('jobs.show');
            });

        Route::apiResource('routines', RoutineController::class);
        Route::prefix('routines/{routine}')
            ->name('routines.')
            ->group(function () {
                Route::post('complete-step', [RoutineController::class, 'completeStep'])->name('complete-step');
                Route::post('reset-progress', [RoutineController::class, 'resetProgress'])->name('reset-progress');
            });

        Route::apiResource('focus-sessions', FocusSessionController::class);
        Route::prefix('focus-sessions/{focusSession}')
            ->name('focus-sessions.')
            ->group(function () {
                Route::post('/start', [FocusSessionController::class, 'start'])->name('start');
                Route::post('/pause', [FocusSessionController::class, 'pause'])->name('pause');
                Route::post('/complete', [FocusSessionController::class, 'complete'])->name('complete');
                Route::post('/join', [FocusSessionController::class, 'join'])->name('join');
            });

        Route::apiResource('checklists', ChecklistController::class)->except(['index', 'show']);

        Route::apiResource('comments', CommentController::class);
    });
});
