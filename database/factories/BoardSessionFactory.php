<?php

namespace Database\Factories;

use App\Models\BoardSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoardSession>
 */
class BoardSessionFactory extends Factory
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
            'question' => fake()->sentence(10),
            'context' => fake()->optional()->paragraph(),
            'status' => 'pending',
            'final_decision' => null,
            'risks_benefits' => null,
            'decided_at' => null,
        ];
    }

    /**
     * Indicate that the session is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the session is debating.
     */
    public function debating(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'debating',
        ]);
    }

    /**
     * Indicate that the session is decided.
     */
    public function decided(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'decided',
            'final_decision' => fake()->paragraph(),
            'risks_benefits' => "Risks:\n- " . fake()->sentence() . "\n\nBenefits:\n- " . fake()->sentence(),
            'decided_at' => now(),
        ]);
    }

    /**
     * Indicate that the session has context.
     */
    public function withContext(): static
    {
        return $this->state(fn (array $attributes) => [
            'context' => fake()->paragraph(3),
        ]);
    }
}
