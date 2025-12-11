<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    protected function success(mixed $data = null, ?string $message = null, int $status = Response::HTTP_OK): JsonResponse
    {
        return ApiResponse::success($data, $message ?? '', $status);
    }

    protected function created(mixed $data = null, ?string $message = null): JsonResponse
    {
        return ApiResponse::created($data, $message ?? 'Resource created successfully');
    }

    protected function accepted(mixed $data = null, ?string $message = null): JsonResponse
    {
        return ApiResponse::accepted($data, $message ?? 'Request accepted');
    }

    protected function noContent(): JsonResponse
    {
        return ApiResponse::noContent();
    }

    protected function error(string $message, int $status = Response::HTTP_BAD_REQUEST, array $errors = []): JsonResponse
    {
        return ApiResponse::error($message, $status, $errors);
    }
}
