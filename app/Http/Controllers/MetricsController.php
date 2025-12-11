<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Infrastructure\Monitoring\PrometheusMetricsExporter;
use Illuminate\Http\Response;

class MetricsController extends Controller
{
    public function __construct(
        private readonly PrometheusMetricsExporter $metricsExporter
    ) {}

    public function metrics(): Response
    {
        $metricsOutput = $this->metricsExporter->collectMetrics();

        return response($metricsOutput, 200)
            ->header('Content-Type', 'text/plain; version=0.0.4');
    }
}
