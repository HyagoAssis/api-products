<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserLog>
 */
class UserLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'model_id' => Product::factory(),
            'model_type' => (new Product)->getMorphClass(),
            'operation' => $this->faker->randomElement(['CREATED', 'UPDATED', 'DELETED']),
            'old_values' => ['price' => $this->faker->randomFloat(2, 1, 1000)],
            'new_values' => ['price' => $this->faker->randomFloat(2, 1, 1000)],
        ];
    }
}
