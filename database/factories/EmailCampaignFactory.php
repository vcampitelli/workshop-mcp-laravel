<?php

namespace Database\Factories;

use App\Models\EmailCampaign;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailCampaignFactory extends Factory
{
    protected $model = EmailCampaign::class;

    public function definition(): array
    {
        return [
            'title'      => fake()->word(),
            'content'    => fake()->word(),
            'status'     => fake()->word(),
            'sent_at'    => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
