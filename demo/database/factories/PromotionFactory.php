<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition(): array
    {
        return [
            'title'       => fake()->word(),
            'description' => fake()->text(),
            'start_at'    => Carbon::now(),
            'end_at'      => Carbon::now(),
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ];
    }
}
