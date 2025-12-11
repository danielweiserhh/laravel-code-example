<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Psr\Log\LoggerInterface;
use App\Infrastructure\AI\RabbitMQClient;
use App\Infrastructure\Speech\GoSpeechClient;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function index(): JsonResponse
    {
        return $this->success(['status' => 'ok']);
    }

    public function full(): JsonResponse
    {
        try {
            /** @var array<string, array{status: string, details: string}> $checks */
            $checks = [
                'database' => $this->checkDatabase(),
                'rabbitmq' => $this->checkRabbitMQ(),
                'ai' => $this->checkAI(),
                'speech' => $this->checkSpeech(),
            ];

            $overallStatus = $this->determineOverallStatus($checks);

            return $this->success([
                'status' => $overallStatus,
                'checks' => $checks,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Health check endpoint failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->success([
                'status' => 'down',
                'checks' => [
                    'database' => ['status' => 'error', 'details' => 'Health check system failure'],
                    'rabbitmq' => ['status' => 'error', 'details' => 'Health check system failure'],
                    'ai' => ['status' => 'error', 'details' => 'Health check system failure'],
                    'speech' => ['status' => 'error', 'details' => 'Health check system failure'],
                ],
            ]);
        }
    }

    /**
     * @return array{status: string, details: string}
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => 'ok',
                'details' => 'Database connection successful',
            ];
        } catch (\Throwable $e) {
            $this->logger->error('Database health check failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'details' => 'Database connection failed',
            ];
        }
    }

    /**
     * @return array{status: string, details: string}
     */
    private function checkRabbitMQ(): array
    {
        try {
            $client = app(RabbitMQClient::class);
            $connection = $client->createConnection();

            if (! $connection->isConnected()) {
                return [
                    'status' => 'error',
                    'details' => 'RabbitMQ not connected',
                ];
            }

            $connection->close();
            return [
                'status' => 'ok',
                'details' => 'RabbitMQ connection successful',
            ];
        } catch (\Throwable $e) {
            $this->logger->error('RabbitMQ health check failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'details' => 'RabbitMQ connection failed',
            ];
        }
    }

    /**
     * @return array{status: string, details: string}
     */
    private function checkAI(): array
    {
        try {
            $client = app(RabbitMQClient::class);
            $connection = $client->createConnection();

            if (! $connection->isConnected()) {
                return [
                    'status' => 'degraded',
                    'details' => 'AI service unavailable (RabbitMQ not connected)',
                ];
            }

            $connection->close();
            return [
                'status' => 'ok',
                'details' => 'AI service available',
            ];
        } catch (\Throwable $e) {
            $this->logger->error('AI health check failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'degraded',
                'details' => 'AI service unavailable',
            ];
        }
    }

    /**
     * @return array{status: string, details: string}
     */
    private function checkSpeech(): array
    {
        try {
            $client = app(GoSpeechClient::class);
            $serviceUrl = config('services.speech.url', 'http://go-speech:8083');

            if (empty($serviceUrl)) {
                return [
                    'status' => 'degraded',
                    'details' => 'Speech service URL not configured',
                ];
            }

            $http = app(HttpClient::class);
            $response = $http->timeout(2)->get($serviceUrl.'/health');

            if ($response->successful()) {
                return [
                    'status' => 'ok',
                    'details' => 'Speech service available',
                ];
            }

            return [
                'status' => 'degraded',
                'details' => 'Speech service returned non-200 status',
            ];
        } catch (\Throwable $e) {
            $this->logger->error('Speech health check failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'degraded',
                'details' => 'Speech service unavailable',
            ];
        }
    }

    /**
     * @param array<string, array{status: string, details?: string}> $checks
     */
    private function determineOverallStatus(array $checks): string
    {
        $hasError = false;
        $hasDegraded = false;

        foreach ($checks as $check) {
            if ($check['status'] === 'error') {
                $hasError = true;
            } elseif ($check['status'] === 'degraded') {
                $hasDegraded = true;
            }
        }

        if ($hasError) {
            return 'down';
        }

        if ($hasDegraded) {
            return 'degraded';
        }

        return 'ok';
    }
}
