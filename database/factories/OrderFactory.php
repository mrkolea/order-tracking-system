<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => $this->faker->unique()->bothify('ORD-####'),
            'status' => Order::STATUS_PENDING,
            'total_amount' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}


