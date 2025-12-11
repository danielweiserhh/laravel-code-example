<?php

declare(strict_types=1);

namespace Tests\Feature\Concurrency;

use App\Models\Routine;
use App\Models\RoutineExecution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class RoutineExecutionConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_concurrent_routine_step_completions_maintain_consistency(): void
    {
        $user = User::factory()->create();
        $routine = Routine::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $date = Carbon::today()->toDateString();

        $execution1 = RoutineExecution::factory()->create([
            'routine_id' => $routine->id,
            'user_id' => $user->id,
            'date' => $date,
            'completed_steps' => [],
        ]);

        $response1 = $this->postJson("/api/v1/routines/{$routine->id}/complete-step", [
            'step_position' => 0,
            'date' => $date,
        ]);

        $response2 = $this->postJson("/api/v1/routines/{$routine->id}/complete-step", [
            'step_position' => 1,
            'date' => $date,
        ]);

        $this->assertTrue($response1->isSuccessful() || $response2->isSuccessful());

        $execution = RoutineExecution::where('routine_id', $routine->id)
            ->where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        $this->assertNotNull($execution);
        $completedSteps = $execution->completed_steps;
        $this->assertIsArray($completedSteps);
        $this->assertContains(0, $completedSteps);
        $this->assertContains(1, $completedSteps);
        $this->assertCount(2, $completedSteps);
    }

    public function test_concurrent_routine_resets_do_not_conflict(): void
    {
        $user = User::factory()->create();
        $routine = Routine::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $date = Carbon::today()->toDateString();

        RoutineExecution::factory()->create([
            'routine_id' => $routine->id,
            'user_id' => $user->id,
            'date' => $date,
            'completed_steps' => [0, 1, 2],
        ]);

        $response1 = $this->postJson("/api/v1/routines/{$routine->id}/reset-progress", [
            'date' => $date,
        ]);

        $response2 = $this->postJson("/api/v1/routines/{$routine->id}/reset-progress", [
            'date' => $date,
        ]);

        $this->assertTrue($response1->isSuccessful() || $response2->isSuccessful());

        $execution = RoutineExecution::where('routine_id', $routine->id)
            ->where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        $this->assertNotNull($execution);
        $this->assertEmpty($execution->completed_steps);
    }
}

