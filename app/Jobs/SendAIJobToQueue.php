<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\AI\Repositories\AIJobRepositoryInterface;
use App\Enums\JobStatus;
use App\Infrastructure\AI\RabbitMQClient;
use App\Models\AIJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

class SendAIJobToQueue implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private ?AIJobRepositoryInterface $aiJobRepository = null;

    private ?RabbitMQClient $rabbitMQClient = null;

    public function __construct(
        public AIJob $aiJob,
        ?AIJobRepositoryInterface $aiJobRepository = null,
        ?RabbitMQClient $rabbitMQClient = null
    ) {
        if ($aiJobRepository !== null) {
            $this->aiJobRepository = $aiJobRepository;
        }

        if ($rabbitMQClient !== null) {
            $this->rabbitMQClient = $rabbitMQClient;
        }
    }

    private function getRepository(): AIJobRepositoryInterface
    {
        if ($this->aiJobRepository === null) {
            $this->aiJobRepository = app(AIJobRepositoryInterface::class);
        }

        return $this->aiJobRepository;
    }

    private function getRabbitMQClient(): RabbitMQClient
    {
        if ($this->rabbitMQClient === null) {
            $this->rabbitMQClient = app(RabbitMQClient::class);
        }

        return $this->rabbitMQClient;
    }

    public function handle(): void
    {
        try {
            $connection = $this->getRabbitMQClient()->createConnection();
            $channel = $connection->channel();
            $channel->queue_declare('ai.tasks', false, true, false, false);

            $body = json_encode([
                'job_id' => $this->aiJob->id,
                'type' => $this->aiJob->type->value,
                'timestamp' => now()->toIso8601String(),
                'payload' => $this->aiJob->payload,
            ], JSON_UNESCAPED_UNICODE);

            if ($body === false) {
                throw new \RuntimeException('Не удалось преобразовать AI job в JSON');
            }

            $message = new AMQPMessage(
                $body,
                ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
            );

            $channel->basic_publish($message, '', 'ai.tasks');

            $channel->close();
            $connection->close();
        } catch (\Exception $e) {
            Log::error('Error sending AI job to queue', [
                'job_id' => $this->aiJob->id,
                'job_type' => $this->aiJob->type->value ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            $this->aiJob->status = JobStatus::FAILED;
            $this->getRepository()->save($this->aiJob);

            throw $e;
        }
    }
}
