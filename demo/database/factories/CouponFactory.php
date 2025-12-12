<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code'        => fake()->word(),
            'discount'    => fake()->word(),
            'valid_from'  => Carbon::now(),
            'valid_to'    => Carbon::now(),
            'usage_limit' => fake()->randomNumber(),
            'type'        => fake()->randomElement(['fixed', 'percentage']),
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ];
    }
}
