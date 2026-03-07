<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProjectAssignment;
use App\Models\Project;
use App\Models\Agent;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectAssignment>
 */
class ProjectAssignmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProjectAssignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'agent_id' => Agent::factory(),
            'role' => $this->faker->randomElement(['developer', 'reviewer', 'project_manager', 'qa']),
            'assigned_at' => now(),
        ];
    }
}
