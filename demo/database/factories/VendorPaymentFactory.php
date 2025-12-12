<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\VendorPayment;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorPaymentFactory extends Factory
{
    protected $model = VendorPayment::class;

    public function definition(): array
    {
        return [
            'amount'         => fake()->randomFloat(),
            'payment_date'   => Carbon::now(),
            'payment_method' => fake()->word(),
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),

            'vendor_id' => Vendor::factory(),
        ];
    }
}
