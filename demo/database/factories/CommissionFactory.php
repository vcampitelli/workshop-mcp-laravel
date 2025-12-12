<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Vendor;
use App\Models\Commission;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionFactory extends Factory
{
    protected $model = Commission::class;

    public function definition(): array
    {
        return [
            'amount'     => fake()->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'vendor_id' => Vendor::factory(),
            'order_id'  => Order::factory(),
        ];
    }
}
