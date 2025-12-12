<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductTag;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductTagFactory extends Factory
{
    protected $model = ProductTag::class;

    public function definition(): array
    {
        return [
            'name'       => fake()->name(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'product_id' => Product::factory(),
        ];
    }
}
