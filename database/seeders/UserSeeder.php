<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'yogesh@gmail.com'],
            [
                'name' => 'Yogesh',
                'password' => Hash::make('Admin@123'),
            ]
        );
    }
}
