<?php

namespace Database\Factories;

use App\Models\Vendor;
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

            'vendor_id' => Vendor::factory(),
        ];
    }

    public function configure(): static
    {
        $images = collect(Storage::files('demo-images'));

        return $this->afterCreating(function (Product $product) use ($images) {
            $product->addMediaFromDisk($images->random())
                ->preservingOriginal()
                ->toMediaCollection('featured_image');
        });
    }
}
