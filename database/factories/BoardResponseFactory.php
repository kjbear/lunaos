<?php

namespace Database\Factories;

use App\Models\BoardResponse;
use App\Models\BoardSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoardResponse>
 */
class BoardResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_id' => BoardSession::factory(),
            'member_id' => (string) Str::uuid(),
            'member_name' => fake()->name(),
            'member_role' => fake()->randomElement(['CEO', 'COO', 'CTO', 'CFO', 'CMO', 'CPO']),
            'response' => fake()->paragraph(2),
            'model_used' => fake()->randomElement(['glm-5', 'haiku', 'dolphin']),
            'response_order' => 0,
        ];
    }

    /**
     * Indicate the response belongs to an existing session.
     */
    public function forSession(BoardSession $session): static
    {
        return $this->state(fn (array $attributes) => [
            'session_id' => $session->id,
        ]);
    }

    /**
     * Create a response for a specific board member role.
     */
    public function asRole(string $role): static
    {
        $names = [
            'CEO' => 'Steven',
            'COO' => 'Gwynne',
            'CTO' => 'Werner',
            'CFO' => 'Warren',
            'CMO' => 'Bozoma',
            'CPO' => 'Fidji',
        ];

        return $this->state(fn (array $attributes) => [
            'member_role' => $role,
            'member_name' => $names[$role] ?? fake()->name(),
        ]);
    }

    /**
     * Create a response with a specific order.
     */
    public function withOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'response_order' => $order,
        ]);
    }
}
