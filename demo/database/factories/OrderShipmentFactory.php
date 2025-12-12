<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderShipment;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderShipmentFactory extends Factory
{
    protected $model = OrderShipment::class;

    public function definition(): array
    {
        return [
            'tracking_number' => fake()->word(),
            'carrier'         => fake()->word(),
            'status'          => fake()->word(),
            'shipped_at'      => Carbon::now(),
            'delivered_at'    => Carbon::now(),
            'created_at'      => Carbon::now(),
            'updated_at'      => Carbon::now(),

            'order_id' => Order::factory(),
        ];
    }
}
