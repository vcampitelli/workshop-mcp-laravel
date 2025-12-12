<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Carbon;
use App\Models\ProductAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'value'            => fake()->word(),
            'additional_price' => fake()->randomFloat(),
            'quantity'         => fake()->randomNumber(),
            'created_at'       => Carbon::now(),
            'updated_at'       => Carbon::now(),

            'product_id'           => Product::factory(),
            'product_attribute_id' => ProductAttribute::factory(),
        ];
    }
}
