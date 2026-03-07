<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Agent;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent>
 */
class AgentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Agent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
            'type' => $this->faker->randomElement(['worker', 'coordinator', 'supervisor']),
            'role' => $this->faker->randomElement(['developer', 'qa', 'devops', 'security']),
            'model' => $this->faker->randomElement(['ollama/llama3', 'openrouter/gpt-4', 'openrouter Claude-3']),
            'provider' => $this->faker->randomElement(['ollama', 'openrouter']),
            'system_prompt' => $this->faker->paragraph(2),
            'model_settings' => null,
            'avatar' => $this->faker->randomElement(['🤖', '🦄', '🦅', '🦊', '🐼']),
            'status' => $this->faker->randomElement(['online', 'offline', 'busy']),
            'parent_id' => null,
            'emoji' => $this->faker->randomElement(['🤖', '🦄', '🦅', '🦊', '🐼']),
            'runtime_location' => $this->faker->randomElement(['php', 'openclaw']),
            'strategy_class' => null,
            'step_filter' => null,
            'workflow_config' => null,
            'skill_doc_path' => null,
            'skill_metadata' => null,
            'is_online' => $this->faker->boolean,
            'capabilities' => null,
            'settings' => null,
        ];
    }
}
