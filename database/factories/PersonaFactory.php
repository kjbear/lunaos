<?php

namespace Database\Factories;

use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Persona>
 */
class PersonaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => fake()->name(),
            'title' => fake()->jobTitle(),
            'role' => 'board_member',
            'model' => fake()->randomElement(['glm-5', 'haiku', 'dolphin']),
            'avatar' => fake()->randomElement(['🎯', '👔', '💻', '💰', '📢', '📦']),
            'status' => 'active',
            'inspiration' => fake()->sentence(),
            'system_prompt' => null,
            'workspace_path' => null,
            'deactivated_at' => null,
        ];
    }

    /**
     * Indicate that the persona is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the persona is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the persona is a board member.
     */
    public function boardMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'board_member',
        ]);
    }

    /**
     * Indicate that the persona is a subagent.
     */
    public function subagent(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'subagent',
        ]);
    }

    /**
     * Create a CEO persona.
     */
    public function ceo(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Steven',
            'title' => 'CEO',
            'avatar' => '🎯',
            'inspiration' => 'Steve Jobs - visionary, product-obsessed',
        ]);
    }

    /**
     * Create a COO persona.
     */
    public function coo(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Gwynne',
            'title' => 'COO',
            'avatar' => '👔',
            'inspiration' => 'Gwynne Shotwell - operational excellence',
        ]);
    }

    /**
     * Create a CTO persona.
     */
    public function cto(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Werner',
            'title' => 'CTO',
            'avatar' => '💻',
            'inspiration' => 'Werner Vogels - scalability, architecture',
        ]);
    }

    /**
     * Create a CFO persona.
     */
    public function cfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Warren',
            'title' => 'CFO',
            'avatar' => '💰',
            'inspiration' => 'Warren Buffet - value investing, ROI discipline',
        ]);
    }

    /**
     * Create a CMO persona.
     */
    public function cmo(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Bozoma',
            'title' => 'CMO',
            'avatar' => '📢',
            'inspiration' => 'Bozoma Saint John - cultural marketing',
        ]);
    }

    /**
     * Create a CPO persona.
     */
    public function cpo(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Fidji',
            'title' => 'CPO',
            'avatar' => '📦',
            'inspiration' => 'Fidji Simo - user-centric product',
        ]);
    }
}
