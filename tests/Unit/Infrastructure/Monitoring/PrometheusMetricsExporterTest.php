<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Monitoring;

use App\Infrastructure\Monitoring\PrometheusMetricsExporter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class PrometheusMetricsExporterTest extends TestCase
{
    public function test_collects_metrics_returns_string(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->andReturn((object) ['count' => 5]);
        
        Redis::shouldReceive('info')
            ->once()
            ->andReturn([
                'connected_clients' => 10,
                'used_memory' => 1024000,
            ]);
        
        Cache::shouldReceive('get')
            ->with('metrics:cards_created_total', 0)
            ->andReturn(100);
        Cache::shouldReceive('get')
            ->with('metrics:inbox_items_created_total', 0)
            ->andReturn(50);
        Cache::shouldReceive('get')
            ->with('metrics:ai_jobs_total', 0)
            ->andReturn(25);
        Cache::shouldReceive('get')
            ->with('metrics:ai_jobs_failed', 0)
            ->andReturn(2);
        Cache::shouldReceive('getStore')
            ->andReturn(null);

        $exporter = new PrometheusMetricsExporter;
        $result = $exporter->collectMetrics();

        $this->assertIsString($result);
        $this->assertStringContainsString('db_connections_active 5', $result);
        $this->assertStringContainsString('redis_connected_clients 10', $result);
        $this->assertStringContainsString('redis_used_memory 1024000', $result);
        $this->assertStringContainsString('cards_created_total 100', $result);
        $this->assertStringContainsString('inbox_items_created_total 50', $result);
        $this->assertStringContainsString('ai_jobs_total 25', $result);
        $this->assertStringContainsString('ai_jobs_failed 2', $result);
    }

    public function test_handles_database_error_gracefully(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->andThrow(new \Exception('Database error'));
        
        Redis::shouldReceive('info')
            ->once()
            ->andReturn([
                'connected_clients' => 10,
                'used_memory' => 1024000,
            ]);
        
        Cache::shouldReceive('get')->andReturn(0);
        Cache::shouldReceive('getStore')->andReturn(null);

        $exporter = new PrometheusMetricsExporter;
        $result = $exporter->collectMetrics();

        $this->assertStringContainsString('db_connections_active -1', $result);
    }

    public function test_handles_redis_error_gracefully(): void
    {
        DB::shouldReceive('selectOne')
            ->once()
            ->andReturn((object) ['count' => 5]);
        
        Redis::shouldReceive('info')
            ->once()
            ->andThrow(new \Exception('Redis error'));
        
        Cache::shouldReceive('get')->andReturn(0);
        Cache::shouldReceive('getStore')->andReturn(null);

        $exporter = new PrometheusMetricsExporter;
        $result = $exporter->collectMetrics();

        $this->assertStringContainsString('redis_connected_clients -1', $result);
        $this->assertStringContainsString('redis_used_memory -1', $result);
    }
}
