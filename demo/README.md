# Laravel E-Commerce Database Structure

This repository is an example of a complex Laravel database with migrations/models/factories/seeders.

The purpose is for developers to take this example and simulate various e-commerce scenarios, evaluate decisions about DB table relationships, and experiment with various Eloquent/SQL queries.

**Notice**: it's not an entire Laravel E-Commerce project. It's JUST the database layer.

This is the DB schema:

![](https://laraveldaily.com/uploads/2025/01/database-structure-min.png)

---

## There's Data Inside

With factories and seeders, you can play around with various scenarios, here are examples of a few DB tables after `php artisan migrate --seed`:

![](https://laraveldaily.com/uploads/2025/01/order-refunds-table-example.png)

![](https://laraveldaily.com/uploads/2025/01/orders-table-example.png)

![](https://laraveldaily.com/uploads/2025/01/product-table-example.png)

---

## Installation

Follow these steps to set up the project locally:

1. Clone the repository:
   ```bash
   git clone https://github.com/LaravelDaily/Laravel-Multi-Vendor-E-Commerce-Structure.git project
   cd project
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy the `.env` file and configure your environment variables:
   ```bash
   cp .env.example .env
   ```

4. Generate the application key:
   ```bash
   php artisan key:generate
   ```

5. Set up the database:
    - Update `.env` with your database credentials.
    - Run migrations and seed the database, repo includes fake tasks:
      ```bash
      php artisan migrate --seed
      ```

---

## How it Works?

With this example, we wanted to show you how to structure a bigger application. In this case - an E-Commerce project.

You can find Database Seeders inside the repository, which will generate fake data for you:

**database/seeders/DatabaseSeeder.php**
```php
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
```

From here, you can play around with the data and see how to write specific queries to get the data you need or generate reports.

---

## DB Structure Decisions to Pay Attention To

We think these points below are interesting to analyze and learn from or experiment with alternatives.

1. Each of the User can have **multiple addresses**, and they are stored in separate table.
2. Products can have **multiple variations**, and they are stored in separate table.
3. We are using `decimal` for our prices, as it's more precise than `float`.
4. Order actions (like shipments, returns and refunds) are stored in separate tables.
5. Tracking Vendor commissions and payments is a separate table.
6. Usage of `restrict` on delete for some tables, like `products` and `order_items`. This means that if you have orders, you can't delete products.
7. Eloquent Casting usage for `datetime` fields