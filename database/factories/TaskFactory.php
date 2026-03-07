<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Task;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(3),
            'assigned_to' => $this->faker->randomElement(['dave', 'sam', 'chen', 'security']),
            'project_id' => null,
            'requirement_id' => null,
            'repository_id' => null,
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'complete', 'failed', 'blocked']),
            'step' => $this->faker->randomElement(['develop', 'qa', 'security', 'staging', 'production']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'task_type' => $this->faker->randomElement(['feature', 'bugfix', 'refactor', 'test']),
            'context_json' => null,
            'branch_name' => $this->faker->randomElement(['feature/new-feature', 'bugfix/issue-123', 'hotfix/critical', null]),
            'pr_url' => $this->faker->randomElement(['https://github.com/example/repo/pull/123', null]),
            'artifacts_json' => null,
        ];
    }
}
