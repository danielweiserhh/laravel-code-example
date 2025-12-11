<?php

namespace Database\Factories;

use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WorkspaceFactory extends Factory
{
    protected $model = Workspace::class;

    public function definition(): array
    {
        $name = fake()->company().' Workspace';
        $baseSlug = Str::slug($name);

        return [
            'name' => $name,
            'description' => fake()->sentence(),
            'slug' => $baseSlug.'-'.fake()->unique()->numerify('####').'-'.time(),
            'settings' => [],
        ];
    }
}
