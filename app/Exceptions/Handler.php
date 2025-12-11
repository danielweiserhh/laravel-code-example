<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Domain\Exceptions\DomainException;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Exceptions\InvariantViolationException;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        ValidationException::class,
        AuthenticationException::class,
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
    }

    public function render($request, Throwable $e): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    private function handleApiException(Request $request, Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return $this->handleValidationException($e);
        }

        if ($e instanceof AuthenticationException) {
            return ApiResponse::error(
                'Требуется авторизация',
                Response::HTTP_UNAUTHORIZED
            );
        }

        if ($e instanceof ModelNotFoundException || $e instanceof EntityNotFoundException) {
            $message = $e instanceof EntityNotFoundException
                ? $e->getMessage()
                : 'Ресурс не найден';

            return ApiResponse::error(
                $message,
                Response::HTTP_NOT_FOUND
            );
        }

        if ($e instanceof InvariantViolationException) {
            return ApiResponse::error(
                $e->getUserMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $e->getContext()
            );
        }

        if ($e instanceof DomainException) {
            return ApiResponse::error(
                $e->getUserMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $e->getContext()
            );
        }

        if ($e instanceof \DomainException) {
            return ApiResponse::error(
                $e->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return $this->handleGenericException($e);
    }

    private function handleValidationException(ValidationException $e): JsonResponse
    {
        return ApiResponse::validationError(
            $e->errors(),
            $e->getMessage() ?: 'Ошибка валидации данных'
        );
    }

    private function handleGenericException(Throwable $e): JsonResponse
    {
        $this->logException($e);

        if (config('app.debug')) {
            return ApiResponse::error(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['trace' => $e->getTraceAsString()]
            );
        }

        return ApiResponse::error(
            'Произошла ошибка при обработке запроса',
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    private function logException(Throwable $e): void
    {
        if ($this->shouldReport($e)) {
            logger()->error('Unhandled exception in API', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}
