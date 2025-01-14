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
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,

            OrderStatusSeeder::class,
            OrderRefundStatusSeeder::class,
            OrderReturnStatusSeeder::class,
            OrderShipmentStatusSeeder::class,
            PaymentMethodSeeder::class,
            ProductStatusSeeder::class,
            EmailCampaignStatusSeeder::class,

            UserSeeder::class,
            UserAddressSeeder::class,

            VendorSeeder::class,

            ProductCategorySeeder::class,
            ProductAttributeSeeder::class,
            ProductSeeder::class,
            ProductReviewSeeder::class,

            OrderSeeder::class,

            PaymentVendorSeeder::class,
            VendorSettingsSeeder::class,
            VendorPaymentsSeeder::class,
            VendorReviewsSeeder::class,

            CouponSeeder::class,
            ReviewSeeder::class,
            WishlistSeeder::class,

            CartSeeder::class,
            CartItemSeeder::class,

            EmailCampaignSeeder::class,
            PromotionSeeder::class,
        ]);
    }
}
