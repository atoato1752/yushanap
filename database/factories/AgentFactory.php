<?php

namespace Database\Factories;

use App\Models\Agent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AgentFactory extends Factory
{
    protected $model = Agent::class;

    public function definition()
    {
        $cost_price = $this->faker->numberBetween(50, 80);
        return [
            'username' => $this->faker->unique()->userName,
            'password' => Hash::make('123456'),
            'cost_price' => $cost_price,
            'selling_price' => $cost_price + $this->faker->numberBetween(10, 30),
            'balance' => $this->faker->numberBetween(0, 1000),
            'status' => true
        ];
    }
} 