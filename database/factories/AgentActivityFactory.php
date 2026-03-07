<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AgentActivity;
use App\Models\Task;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AgentActivity>
 */
class AgentActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AgentActivity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'agent_name' => $this->faker->randomElement(['dave', 'sam', 'chen', 'security']),
            'action' => $this->faker->randomElement(['started', 'completed', 'failed', 'advanced', 'reassigned', 'updated']),
            'metadata_json' => null,
        ];
    }

    /**
     * Indicate the activity is for a specific task.
     */
    public function forTask(Task $task): static
    {
        return $this->state(fn (array $attributes) => [
            'task_id' => $task->id,
        ]);
    }

    /**
     * Indicate the activity was a started action.
     */
    public function started(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'started',
        ]);
    }

    /**
     * Indicate the activity was a completed action.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'completed',
        ]);
    }

    /**
     * Indicate the activity was a failed action.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'failed',
        ]);
    }
}