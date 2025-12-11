<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['prefix' => '', 'middleware' => ['auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
        
        $middleware->prepend(\App\Http\Middleware\RequestIdMiddleware::class);
        
        $middleware->append(\App\Http\Middleware\MetricsMiddleware::class);
        
        
        $middleware->append(\App\Http\Middleware\LogHttpRequest::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                $context = $request->method().' '.$request->path();
                $errorId = uniqid('err_', true);
                
                $logContext = [
                    'error_id' => $errorId,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'context' => $context,
                ];
                
                if ($e instanceof \App\Domain\Exceptions\DomainException) {
                    $logContext['domain_context'] = $e->getContext();
                }
                
                $isServerError = ! ($e instanceof \Illuminate\Validation\ValidationException
                    || $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
                    || $e instanceof \Illuminate\Auth\AuthenticationException
                    || $e instanceof \Illuminate\Auth\Access\AuthorizationException
                    || $e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException
                    || $e instanceof \App\Domain\Exceptions\DomainException
                    || $e instanceof \DomainException
                    || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException);

                if ($isServerError) {
                    $logContext['trace'] = $e->getTraceAsString();
                    \Illuminate\Support\Facades\Log::error('API Error', $logContext);
                } else {
                    \Illuminate\Support\Facades\Log::warning('API Request Failed', $logContext);
                }
                
                $userMessage = match (true) {
                    $e instanceof \Illuminate\Validation\ValidationException => 'Ошибка валидации данных.',
                    $e instanceof \App\Domain\Exceptions\EntityNotFoundException => 'Запрашиваемый ресурс не найден.',
                    $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 'Запрашиваемый ресурс не найден.',
                    $e instanceof \Illuminate\Auth\AuthenticationException => 'Необходима авторизация для выполнения этого действия.',
                    $e instanceof \Illuminate\Auth\Access\AuthorizationException => 'У вас нет прав для выполнения этого действия.',
                    $e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException => 'Слишком много запросов. Попробуйте позже.',
                    $e instanceof \App\Domain\Exceptions\DomainException => $e->getUserMessage(),
                    $e instanceof \DomainException => $e->getMessage(),
                    $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException => $e->getMessage() ?: 'Ошибка обработки запроса.',
                    default => "Произошла ошибка. Код ошибки: {$errorId}",
                };

                $status = match (true) {
                    $e instanceof \Illuminate\Validation\ValidationException => 422,
                    $e instanceof \App\Domain\Exceptions\EntityNotFoundException => 404,
                    $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 404,
                    $e instanceof \Illuminate\Auth\AuthenticationException => 401,
                    $e instanceof \Illuminate\Auth\Access\AuthorizationException => 403,
                    $e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException => 429,
                    $e instanceof \App\Domain\Exceptions\DomainException => 422, 
                    $e instanceof \DomainException => 422,
                    $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException => $e->getStatusCode(),
                    default => 500,
                };

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return \App\Http\Responses\ApiResponse::validationError($e->errors(), $userMessage, $errorId);
                }

                return \App\Http\Responses\ApiResponse::error($userMessage, $status, [], $errorId);
            }
        });
    })->create();
