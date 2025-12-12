<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\OrderReturn;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderReturnFactory extends Factory
{
    protected $model = OrderReturn::class;

    public function definition(): array
    {
        return [
            'reason'     => fake()->word(),
            'status'     => fake()->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'order_item_id' => OrderItem::factory(),
        ];
    }
}
