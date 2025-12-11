<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = '', int $status = Response::HTTP_OK): JsonResponse
    {
        $response = ['success' => true];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            if ($data instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
                $response['data'] = $data->items();
                $response['current_page'] = $data->currentPage();
                $response['per_page'] = $data->perPage();
                $response['total'] = $data->total();
                $response['last_page'] = $data->lastPage();
            } else {
                $response['data'] = $data;
            }
        }

        return response()->json($response, $status);
    }

    public static function error(string $message, int $status = Response::HTTP_BAD_REQUEST, array $errors = [], ?string $errorId = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $response['errors'] = $errors;
        }

        if ($errorId) {
            $response['error_id'] = $errorId;
        }

        return response()->json($response, $status);
    }

    public static function validationError(array $errors, string $message = 'Ошибка валидации', ?string $errorId = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];

        if ($errorId) {
            $response['error_id'] = $errorId;
        }

        return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function created(mixed $data = null, string $message = 'Ресурс успешно создан'): JsonResponse
    {
        return self::success($data, $message, Response::HTTP_CREATED);
    }

    public static function accepted(mixed $data = null, string $message = 'Запрос принят'): JsonResponse
    {
        return self::success($data, $message, Response::HTTP_ACCEPTED);
    }

    public static function noContent(): JsonResponse
    {
        return response()->json(['success' => true], Response::HTTP_NO_CONTENT);
    }
}
