<?php

declare(strict_types=1);

namespace Tests\Integration\Jobs;

use App\Enums\AIJobType;
use App\Enums\JobStatus;
use App\Infrastructure\AI\RabbitMQClient;
use App\Jobs\SendAIJobToQueue;
use App\Models\AIJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Tests\TestCase;

class SendAIJobToQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_job_publishes_payload_to_queue(): void
    {
        $aiJob = AIJob::factory()->create([
            'type' => AIJobType::GENERATE_TODAY_PLAN,
            'status' => JobStatus::PENDING,
            'payload' => ['foo' => 'bar'],
        ]);

        $channelMock = Mockery::mock();
        $channelMock->shouldReceive('queue_declare')
            ->once()
            ->with('ai.tasks', false, true, false, false);
        $channelMock->shouldReceive('basic_publish')
            ->once()
            ->withArgs(function (AMQPMessage $message, $exchange, $routingKey) use ($aiJob) {
                $this->assertSame('', $exchange);
                $this->assertSame('ai.tasks', $routingKey);

                $payload = json_decode($message->getBody(), true);
                $this->assertSame($aiJob->id, $payload['job_id']);
                $this->assertSame($aiJob->type->value, $payload['type']);
                $this->assertSame(['foo' => 'bar'], $payload['payload']);

                return true;
            });
        $channelMock->shouldReceive('close')->once();

        $connectionMock = Mockery::mock(AMQPStreamConnection::class);
        $connectionMock->shouldReceive('channel')->once()->andReturn($channelMock);
        $connectionMock->shouldReceive('close')->once();
        
        $rabbitMQClientMock = new class($connectionMock) extends RabbitMQClient
        {
            public function __construct(private AMQPStreamConnection $connection) {}

            public function createConnection(): AMQPStreamConnection
            {
                return $this->connection;
            }
        };

        $job = new SendAIJobToQueue($aiJob, null, $rabbitMQClientMock);
        $job->handle();

        $this->assertEquals(JobStatus::PENDING, $aiJob->fresh()->status);
    }
}
