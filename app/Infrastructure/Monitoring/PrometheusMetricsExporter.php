<?php

declare(strict_types=1);

namespace App\Infrastructure\Monitoring;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

final class PrometheusMetricsExporter
{
    private array $metrics = [];
    
    public function collectMetrics(): string
    {
        $this->metrics = [];

        $this->collectHttpMetrics();
        $this->collectDatabaseMetrics();
        $this->collectRedisMetrics();
        $this->collectBusinessMetrics();

        return implode("\n", $this->metrics);
    }

    
    private function collectHttpMetrics(): void
    {
        $this->collectCounterMetrics('requests_total');
        $this->collectCounterMetrics('requests_errors_total');
        $this->collectHistogramMetrics('request_duration_seconds');
    }
    
    private function collectDatabaseMetrics(): void
    {
        try {
            $result = DB::selectOne(
                'SELECT count(*) as count FROM pg_stat_activity WHERE datname = current_database()'
            );
            $dbConnections = $result->count ?? 0;
            $this->addMetric('db_connections_active', (int) $dbConnections);
        } catch (\Exception) {
            $this->addMetric('db_connections_active', -1);
        }
    }
    
    private function collectRedisMetrics(): void
    {
        try {
            $redisInfo = Redis::info();
            $this->addMetric('redis_connected_clients', (int) ($redisInfo['connected_clients'] ?? 0));
            $this->addMetric('redis_used_memory', (int) ($redisInfo['used_memory'] ?? 0));
        } catch (\Exception) {
            $this->addMetric('redis_connected_clients', -1);
            $this->addMetric('redis_used_memory', -1);
        }
    }
    
    private function collectBusinessMetrics(): void
    {
        $this->addMetric('cards_created_total', (int) Cache::get('metrics:cards_created_total', 0));
        $this->addMetric('inbox_items_created_total', (int) Cache::get('metrics:inbox_items_created_total', 0));
        $this->addMetric('ai_jobs_total', (int) Cache::get('metrics:ai_jobs_total', 0));
        $this->addMetric('ai_jobs_failed', (int) Cache::get('metrics:ai_jobs_failed', 0));
    }
    
    private function collectCounterMetrics(string $metric): void
    {
        try {
            $store = Cache::getStore();
            
            if (! $store || ! method_exists($store, 'getRedis')) {
                return;
            }

            $redis = $store->getRedis();

            if (! $redis instanceof \Redis) {
                return;
            }

            $pattern = 'metrics:'.$metric.':*';
            
            $keys = $redis->keys($pattern);

            if (! is_array($keys)) {
                return;
            }

            foreach ($keys as $key) {
                if (! is_string($key)) {
                    continue;
                }

                $value = Cache::get($key, 0);
                $labelPart = str_replace('metrics:'.$metric.':', '', $key);

                if ($labelPart !== '' && $labelPart !== $key) {
                    $this->metrics[] = sprintf('%s{%s} %s', $metric, $labelPart, (string) $value);
                } else {
                    $this->addMetric($metric, is_numeric($value) ? (float) $value : 0);
                }
            }
        } catch (\Exception) {
        }
    }
    
    private function collectHistogramMetrics(string $metric): void
    {
        $this->collectCounterMetrics($metric.'_sum');
        $this->collectCounterMetrics($metric.'_count');
        $this->collectCounterMetrics($metric.'_bucket');
    }
    
    private function addMetric(string $name, int|float $value): void
    {
        $this->metrics[] = sprintf('%s %s', $name, $value);
    }
}
