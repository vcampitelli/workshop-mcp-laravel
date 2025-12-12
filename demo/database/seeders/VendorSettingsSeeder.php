<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Database\Seeder;

class VendorSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'notification_email' => ['enabled', 'disabled'],
            'auto_stock_alert'   => ['5', '10', '15', '20'],
            'payment_schedule'   => ['weekly', 'bi-weekly', 'monthly'],
            'commission_rate'    => ['5', '10', '15', '20'],
            'featured_vendor'    => ['true', 'false'],
        ];

        $vendors = Vendor::all();
        $vendorSettings = [];

        foreach ($vendors as $vendor) {
            foreach ($settings as $key => $values) {
                $vendorSettings[] = [
                    'vendor_id'  => $vendor->id,
                    'key'        => $key,
                    'value'      => $values[array_rand($values)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        VendorSetting::insert($vendorSettings);
    }
}
