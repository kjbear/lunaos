<?php

namespace Database\Factories;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamMember>
 */
class TeamMemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TeamMember::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => $this->faker->unique()->word,
            'title' => $this->faker->jobTitle,
            'type' => $this->faker->randomElement(['workers', 'personas', 'board-members']),
            'role' => $this->faker->randomElement(['worker', 'persona', 'board_member']),
            'model' => $this->faker->randomElement(['ollama-local/qwen3.5:cloud', 'glm-5', 'haiku', 'dolphin']),
            'provider' => 'ollama',
            'avatar' => $this->faker->randomElement(['🤖', '🎯', '👔', '💻', '💰', '📢', '📦']),
            'emoji' => '🤖',
            'status' => $this->faker->randomElement(['active', 'inactive', 'online', 'offline', 'error', 'busy', 'archived']),
            'system_prompt' => $this->faker->paragraph(2),
            'settings' => null,
            'metadata_json' => null,
            'workspace_path' => null,
            'parent_id' => null,
            'deactivated_at' => null,
        ];
    }

    /**
     * Indicate that the team member is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the team member is online.
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'online',
        ]);
    }

    /**
     * Indicate that the team member is offline.
     */
    public function offline(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'offline',
        ]);
    }

    /**
     * Indicate that the team member is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
            'deactivated_at' => now(),
        ]);
    }

    /**
     * Indicate that the team member is a worker.
     */
    public function worker(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'workers',
            'role' => 'worker',
        ]);
    }

    /**
     * Indicate that the team member is a board member.
     */
    public function boardMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'board-members',
            'role' => 'board_member',
        ]);
    }

    /**
     * Indicate that the team member is a persona.
     */
    public function persona(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'personas',
            'role' => 'persona',
        ]);
    }

    /**
     * Create a CEO board member.
     */
    public function ceo(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'steven',
            'title' => 'CEO',
            'type' => 'board-members',
            'role' => 'board_member',
            'avatar' => '🎯',
            'emoji' => '🎯',
        ]);
    }

    /**
     * Create a COO board member.
     */
    public function coo(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'gwynne',
            'title' => 'COO',
            'type' => 'board-members',
            'role' => 'board_member',
            'avatar' => '👔',
            'emoji' => '👔',
        ]);
    }

    /**
     * Create a CTO board member.
     */
    public function cto(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'werner',
            'title' => 'CTO',
            'type' => 'board-members',
            'role' => 'board_member',
            'avatar' => '💻',
            'emoji' => '💻',
        ]);
    }

    /**
     * Create a CFO board member.
     */
    public function cfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'warren',
            'title' => 'CFO',
            'type' => 'board-members',
            'role' => 'board_member',
            'avatar' => '💰',
            'emoji' => '💰',
        ]);
    }

    /**
     * Create a developer worker.
     */
    public function developer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'dave',
            'title' => 'Developer',
            'type' => 'workers',
            'role' => 'worker',
            'avatar' => '🤖',
            'emoji' => '🤖',
        ]);
    }

    /**
     * Create a QA worker.
     */
    public function qa(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'sam',
            'title' => 'QA Specialist',
            'type' => 'workers',
            'role' => 'worker',
            'avatar' => '🔍',
            'emoji' => '🔍',
        ]);
    }

    /**
     * Indicate that the team member has a parent.
     */
    public function withParent($parentId): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }
}
