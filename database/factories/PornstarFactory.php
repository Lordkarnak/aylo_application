<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pornstar>
 */
class PornstarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->randomNumber,
            'name' => fake()->name(),
            'license' => 'STANDARD',
            'age' => fake()->numberBetween(18, 65)
        ];
    }
}
