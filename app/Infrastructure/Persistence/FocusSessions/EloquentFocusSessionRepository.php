<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\FocusSessions;

use App\Domain\FocusSessions\Repositories\FocusSessionRepositoryInterface;
use App\Domain\FocusSessions\ValueObjects\FocusSession as DomainFocusSession;
use App\Domain\Shared\ValueObjects\UserId;
use App\Enums\FocusSessionStatus;
use App\Models\FocusSession;
use Carbon\Carbon;

final class EloquentFocusSessionRepository implements FocusSessionRepositoryInterface
{
    private function toDomain(FocusSession $model): DomainFocusSession
    {
        return DomainFocusSession::fromArray([
            'id' => $model->id,
            'user_id' => $model->user_id,
            'card_id' => $model->card_id,
            'duration_minutes' => $model->duration_minutes,
            'started_at' => $model->started_at?->toIso8601String(),
            'ended_at' => $model->ended_at?->toIso8601String(),
            'status' => $model->status->value,
            'is_group' => $model->is_group,
            'video_link' => $model->video_link,
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
        ]);
    }

    public function find(int $id): ?DomainFocusSession
    {
        $model = FocusSession::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findOrFail(int $id): DomainFocusSession
    {
        $model = FocusSession::findOrFail($id);

        return $this->toDomain($model);
    }

    public function findByUserId(UserId $userId): array
    {
        $models = FocusSession::where('user_id', $userId->value)
            ->orderBy('created_at', 'desc')
            ->get();

        $result = [];

        foreach ($models as $model) {
            $result[] = $this->toDomain($model);
        }

        return $result;
    }
    
    public function findActiveByUserId(UserId $userId): array
    {
        $models = FocusSession::where('user_id', $userId->value)
            ->where('status', FocusSessionStatus::ACTIVE)
            ->get();

        $result = [];

        foreach ($models as $model) {
            $result[] = $this->toDomain($model);
        }

        return $result;
    }
    
    public function create(array $payload, UserId $userId): DomainFocusSession
    {
        $model = new FocusSession;
        $model->user_id = $userId->value;
        $model->card_id = $payload['card_id'] ?? null;
        $model->duration_minutes = $payload['duration_minutes'] ?? 25;
        $model->status = FocusSessionStatus::PENDING;
        $model->is_group = $payload['is_group'] ?? false;
        $model->video_link = $payload['video_link'] ?? null;
        $model->save();
        
        $model->participants()->attach($userId->value, [
            'joined_at' => now(),
        ]);

        return $this->toDomain($model->fresh());
    }
    
    public function update(int $id, array $payload): DomainFocusSession
    {
        $model = FocusSession::findOrFail($id);

        if (array_key_exists('status', $payload)) {
            $status = $payload['status'];
            $model->status = $status instanceof FocusSessionStatus
                ? $status
                : FocusSessionStatus::from((string) $status);
        }

        if (array_key_exists('started_at', $payload)) {
            $model->started_at = $payload['started_at'] !== null
                ? \Illuminate\Support\Carbon::parse($payload['started_at'])
                : null;
        }

        if (array_key_exists('ended_at', $payload)) {
            $model->ended_at = $payload['ended_at'] !== null
                ? \Illuminate\Support\Carbon::parse($payload['ended_at'])
                : null;
        }

        if (array_key_exists('duration_minutes', $payload)) {
            $model->duration_minutes = (int) $payload['duration_minutes'];
        }

        $model->save();

        return $this->toDomain($model->fresh());
    }

    public function addParticipant(int $sessionId, int $userId): void
    {
        $model = FocusSession::findOrFail($sessionId);
        
        if (! $model->participants()->where('user_id', $userId)->exists()) {
            $model->participants()->attach($userId, [
                'joined_at' => now(),
            ]);
        }
    }
    
    public function findByUserIdAndDate(UserId $userId, string $date): array
    {
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        $models = FocusSession::where('user_id', $userId->value)
            ->where(function ($query) use ($startOfDay, $endOfDay) {
                $query->whereBetween('started_at', [$startOfDay, $endOfDay])
                    ->orWhereBetween('created_at', [$startOfDay, $endOfDay]);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $result = [];

        foreach ($models as $model) {
            $result[] = $this->toDomain($model);
        }

        return $result;
    }

    public function getTotalFocusTimeForDate(UserId $userId, string $date): int
    {
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        $completedSessions = FocusSession::where('user_id', $userId->value)
            ->where('status', FocusSessionStatus::COMPLETED)
            ->whereBetween('ended_at', [$startOfDay, $endOfDay])
            ->get();

        $totalMinutes = 0;
        
        foreach ($completedSessions as $session) {
            if ($session->started_at && $session->ended_at) {
                $diffMinutes = $session->started_at->diffInMinutes($session->ended_at);
                $totalMinutes += (int) $diffMinutes;
            } else {
                
                $totalMinutes += $session->duration_minutes;
            }
        }

        return $totalMinutes;
    }
}
