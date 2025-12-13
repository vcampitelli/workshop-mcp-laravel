<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::with('user')->get();
        $paymentMethods = PaymentMethod::pluck('id')->toArray();

        $payments = [];

        foreach ($orders as $order) {
            // Payment
            $createdAt = Carbon::parse($order->created_at);
            $payments[] = [
                'order_id'          => $order->id,
                'user_id'           => $order->user_id,
                'payment_method_id' => $paymentMethods[array_rand($paymentMethods)],
                'amount'            => $order->total_amount,
                'status'            => fake()->randomElement(['completed', 'pending', 'failed']),
                'processed_at'      => $createdAt->addMinutes(rand(1, 60)),
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
            ];
        }

        Payment::insert($payments);
    }
}
