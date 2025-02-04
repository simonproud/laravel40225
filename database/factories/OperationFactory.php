<?php

namespace Database\Factories;

use App\Models\Operation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperationFactory extends Factory
{
    protected $model = Operation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'operation_type' => $this->faker->randomElement(['debit', 'credit']),
            'amount' => $this->faker->randomFloat(2, 0, 1000),
        ];
    }
}
