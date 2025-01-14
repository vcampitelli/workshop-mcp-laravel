<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class WishlistFactory extends Factory
{
    protected $model = Wishlist::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id'    => User::factory(),
            'product_id' => Product::factory(),
        ];
    }
}
