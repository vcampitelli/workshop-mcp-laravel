<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            OrderStatusSeeder::class,
            OrderRefundStatusSeeder::class,
            OrderReturnStatusSeeder::class,
            OrderShipmentStatusSeeder::class,
            PaymentMethodSeeder::class,
            ProductStatusSeeder::class,

            UserSeeder::class,

            ProductCategorySeeder::class,
            ProductAttributeSeeder::class,
            ProductSeeder::class,
            ProductReviewSeeder::class,

            OrderSeeder::class,

            PaymentSeeder::class,

            ReviewSeeder::class,
        ]);
    }
}
