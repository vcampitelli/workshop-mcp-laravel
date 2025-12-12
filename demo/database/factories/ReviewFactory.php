<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Review;
use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'rating'     => fake()->randomNumber(),
            'comment'    => fake()->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id'    => User::factory(),
            'product_id' => Product::factory(),
            'vendor_id'  => Vendor::factory(),
        ];
    }
}
