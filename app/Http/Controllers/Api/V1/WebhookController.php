<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\AI\UseCases\ProcessAIJobWebhookCommand;
use App\Application\AI\UseCases\ProcessAIJobWebhookHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private readonly ProcessAIJobWebhookHandler $processWebhook
    ) {}

    public function aiJobCompleted(Request $request): JsonResponse
    {
        if ($request->header('X-Internal-Service') !== 'go-ai') {
            return $this->error('Неавторизованный запрос', 403);
        }

        $jobId = $request->input('job_id');
        $jobType = $request->input('type');
        $result = $request->input('result', []);
        $status = $request->input('status', 'succeeded');
        $errorMessage = $request->input('error_message');

        if (! $jobId || ! $jobType) {
            return $this->error('Отсутствуют обязательные параметры', 422);
        }

        try {
            $command = new ProcessAIJobWebhookCommand(
                jobId: (string) $jobId,
                jobType: (string) $jobType,
                result: is_array($result) ? $result : [],
                status: (string) $status,
                errorMessage: $errorMessage
            );

            $this->processWebhook->handle($command);

            return $this->success(null, 'Задача обработана');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Задача не найдена', 404);
        } catch (\Exception $e) {
            Log::error('Error processing AI job webhook', [
                'job_id' => $jobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('Ошибка обработки задачи', 500);
        }
    }
}
