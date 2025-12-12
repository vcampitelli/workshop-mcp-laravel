<?php

namespace Database\Factories;

use Illuminate\Support\Carbon;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        return [
            'name'       => fake()->sentence(3),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
