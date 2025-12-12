<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'amount'       => fake()->randomFloat(),
            'method'       => fake()->word(),
            'status'       => fake()->word(),
            'processed_at' => Carbon::now(),
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),

            'order_id' => Order::factory(),
            'user_id'  => User::factory(),
        ];
    }
}
