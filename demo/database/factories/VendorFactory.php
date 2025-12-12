<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'name'        => fake()->name(),
            'description' => fake()->text(),
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }


    public function configure(): static
    {
        $images = collect(Storage::files('demo-images'));

        return $this->afterCreating(function (Vendor $vendor) use ($images) {
            $vendor->addMediaFromDisk($images->random())
                ->preservingOriginal()
                ->toMediaCollection('logo');
        });
    }
}
