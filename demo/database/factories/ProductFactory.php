<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name'        => fake()->name(),
            'description' => fake()->text(),
            'price'       => fake()->randomFloat(),
            'quantity' => fake()->randomNumber(3),
            'status'      => fake()->word(),
        ];
    }
}
