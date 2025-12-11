<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogHttpRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        $requestId = app('request_id') ?? $request->header('X-Request-ID') ?? uniqid('req_', true);

        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            
            $duration = microtime(true) - $startTime;
            $this->logRequest($request, $requestId, 500, $duration);
            throw $e;
        }

        $duration = microtime(true) - $startTime;
        $statusCode = $response->getStatusCode();

        $this->logRequest($request, $requestId, $statusCode, $duration);

        
        $response->headers->set('X-Request-ID', $requestId);
        $response->headers->set('X-Response-Time', round($duration * 1000, 2).'ms');

        return $response;
    }

    private function logRequest(Request $request, string $requestId, int $statusCode, float $duration): void
    {
        Log::withContext([
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
        ])->info('HTTP Request', [
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => $request->path(),
            'status_code' => $statusCode,
            'duration_ms' => round($duration * 1000, 2),
            'user_id' => auth()->id(),
        ]);
    }
}
