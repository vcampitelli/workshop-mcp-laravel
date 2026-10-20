<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        for ($number = 1; $number <= 10; $number++) {
            User::updateOrCreate(
                ['email' => "user{$number}@example.com"],
                [
                    'name' => "User {$number}",
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
