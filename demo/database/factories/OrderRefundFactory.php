<?php

namespace Database\Factories;

use App\Models\OrderRefund;
use App\Models\OrderReturn;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderRefundFactory extends Factory
{
    protected $model = OrderRefund::class;

    public function definition(): array
    {
        return [
            'amount'       => fake()->randomFloat(),
            'status'       => fake()->word(),
            'processed_at' => Carbon::now(),
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),

            'order_return_id' => OrderReturn::factory(),
        ];
    }
}
