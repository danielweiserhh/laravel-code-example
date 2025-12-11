<?php

namespace Database\Factories;

use App\Enums\AIJobType;
use App\Enums\JobStatus;
use App\Models\AIJob;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;


class AIJobFactory extends Factory
{
    protected $model = AIJob::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'workspace_id' => Workspace::factory(),
            'type' => AIJobType::GENERATE_TODAY_PLAN,
            'payload' => [],
            'status' => JobStatus::PENDING,
            'result' => null,
        ];
    }
}
