<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserAddressFactory extends Factory
{
    protected $model = UserAddress::class;

    public function definition(): array
    {
        return [
            'address_line1' => fake()->address(),
            'address_line2' => fake()->address(),
            'city'          => fake()->city(),
            'state'         => fake()->word(),
            'country'       => fake()->country(),
            'postal_code'   => fake()->postcode(),
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }
}
