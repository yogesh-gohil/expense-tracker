<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->first();

        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $faker = Faker::create();

        for ($i = 1; $i <= 1000; $i += 1) {
            Category::query()->create([
                'user_id' => $user->id,
                'name' => $faker->unique()->words(2, true),
                'type' => $i % 2 === 0 ? Category::TYPE_EXPENSE : Category::TYPE_INCOME,
                'description' => $faker->sentence(),
            ]);
        }
    }
}
