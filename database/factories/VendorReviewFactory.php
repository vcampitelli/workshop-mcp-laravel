<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorReview;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorReviewFactory extends Factory
{
    protected $model = VendorReview::class;

    public function definition(): array
    {
        return [
            'rating'     => fake()->randomNumber(),
            'comment'    => fake()->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'vendor_id' => Vendor::factory(),
            'user_id'   => User::factory(),
        ];
    }
}
