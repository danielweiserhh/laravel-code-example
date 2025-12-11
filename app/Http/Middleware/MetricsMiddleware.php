<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class MetricsMiddleware
{
    private const CACHE_PREFIX = 'metrics:';
    private const TTL = 3600; 

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $routeGroup = $this->getRouteGroup($request);

        try {
            $response = $next($request);
            $statusCode = $response->getStatusCode();
            $duration = microtime(true) - $startTime;
            
            $this->incrementCounter('requests_total', [
                'route_group' => $routeGroup,
                'status' => (string) $statusCode,
            ]);
            
            $this->recordHistogram('request_duration_seconds', $duration, [
                'route_group' => $routeGroup,
            ]);
            
            if ($statusCode >= 400) {
                $this->incrementCounter('requests_errors_total', [
                    'route_group' => $routeGroup,
                    'status' => (string) $statusCode,
                ]);
            }

            return $response;
        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            
            $this->incrementCounter('requests_errors_total', [
                'route_group' => $routeGroup,
                'status' => '500',
                'error_type' => class_basename($e),
            ]);

            $this->recordHistogram('request_duration_seconds', $duration, [
                'route_group' => $routeGroup,
            ]);

            throw $e;
        }
    }
    
    private function getRouteGroup(Request $request): string
    {
        $path = $request->path();

        if (str_starts_with($path, 'api/v1/ai')) {
            return 'ai';
        }

        if (str_starts_with($path, 'api/v1/voice')) {
            return 'voice';
        }

        if (str_starts_with($path, 'api/v1/today')) {
            return 'today';
        }

        if (str_starts_with($path, 'api/v1/inbox')) {
            return 'inbox';
        }

        if (str_starts_with($path, 'api/v1/boards') || str_starts_with($path, 'api/v1/cards') || str_starts_with($path, 'api/v1/lists')) {
            return 'boards';
        }

        if (str_starts_with($path, 'api/v1/routines')) {
            return 'routines';
        }

        if (str_starts_with($path, 'api/v1/focus-sessions')) {
            return 'focus_sessions';
        }

        if (str_starts_with($path, 'api/v1/auth') || str_starts_with($path, 'api/v1/user') || str_starts_with($path, 'api/v1/profile')) {
            return 'auth';
        }

        return 'other';
    }
    
    private function incrementCounter(string $metric, array $labels = []): void
    {
        $key = $this->buildKey($metric, $labels);
        Cache::increment($key, 1);
        Cache::put($key, Cache::get($key, 0), self::TTL);
    }
    
    private function recordHistogram(string $metric, float $value, array $labels = []): void
    {
        $sumKey = $this->buildKey($metric.'_sum', $labels);
        $countKey = $this->buildKey($metric.'_count', $labels);

        Cache::increment($sumKey, $value);
        Cache::increment($countKey, 1);
        
        $bucketKey = $this->buildKey($metric.'_bucket', array_merge($labels, ['le' => '+Inf']));
        Cache::increment($bucketKey, 1);
        
        Cache::put($sumKey, Cache::get($sumKey, 0), self::TTL);
        Cache::put($countKey, Cache::get($countKey, 0), self::TTL);
        Cache::put($bucketKey, Cache::get($bucketKey, 0), self::TTL);
    }
    
    private function buildKey(string $metric, array $labels): string
    {
        $labelStr = '';
        ksort($labels);
        foreach ($labels as $key => $value) {
            $labelStr .= sprintf('%s=%s,', $key, $value);
        }

        return self::CACHE_PREFIX.$metric.($labelStr ? ':'.rtrim($labelStr, ',') : '');
    }
}
