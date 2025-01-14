<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $userRole = Role::where('name', 'User')->first();
        $users = User::where('role_id', $userRole->id)->get();
        $carts = [];

        foreach ($users as $user) {
            if (rand(0, 1)) {  // 50% chance of having a cart
                $carts[] = [
                    'user_id'    => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        Cart::insert($carts);
    }
}
