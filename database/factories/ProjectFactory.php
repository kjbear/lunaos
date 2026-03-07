<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Project;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'repo_url' => $this->faker->optional()->url(),
            'repository_id' => null,
            'health' => $this->faker->randomElement(['healthy', 'at_risk', 'blocked']),
            'progress' => $this->faker->numberBetween(0, 100),
            'percent_complete' => 0.00,
            'owner' => 'kyle',
            'status' => $this->faker->randomElement(['planning', 'active', 'completed', 'archived']),
            'architecture_type' => $this->faker->randomElement(['monolith', 'microservices', 'serverless']),
            'technologies' => ['Laravel', 'Vue', 'MySQL'],
            'project_manager_id' => null,
            'archived_at' => null,
            'deleted_at' => null,
        ];
    }

    /**
     * Indicate that the project is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'health' => 'healthy',
        ]);
    }

    /**
     * Indicate that the project is at risk.
     */
    public function atRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'health' => 'at_risk',
        ]);
    }

    /**
     * Indicate that the project is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }
}
