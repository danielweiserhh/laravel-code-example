<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\AIJob;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AIJobCompleted implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public AIJob $aiJob
    ) {}
    
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.{$this->aiJob->user_id}");
    }

    public function broadcastAs(): string
    {
        return 'ai.job.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'job_id' => $this->aiJob->id,
            'type' => $this->aiJob->type->value,
            'status' => $this->aiJob->status->value,
            'result' => $this->aiJob->result,
            'error_message' => $this->aiJob->error_message,
            'context_id' => $this->aiJob->payload['context_id'] ?? null,
        ];
    }

    public function broadcastWhen(): bool
    {
        return true;
    }
}
