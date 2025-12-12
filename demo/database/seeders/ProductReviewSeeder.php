<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Database\Seeder;

class ProductReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id')->toArray();
        $products = Product::all('id');

        $allReviews = [];

        foreach ($products as $product) {
            $reviewsCount = rand(5, 20);

            for ($i = 0; $i < $reviewsCount; $i++) {
                $allReviews[] = [
                    'product_id' => $product->id,
                    'user_id'    => $users[array_rand($users)],
                    'rating'     => rand(1, 5),
                    'comment'    => fake()->paragraph(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        ProductReview::insert($allReviews);
    }
}
