<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        return [
            'quantity'   => fake()->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'cart_id'            => Cart::factory(),
            'product_id'         => Product::factory(),
            'product_variant_id' => ProductVariant::factory(),
        ];
    }
}
