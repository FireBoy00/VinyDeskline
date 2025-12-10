<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin users (personalized)
        User::factory()
            ->count(3)
            ->admin()
            ->personalized()
            ->create();

        // Create regular users who have already personalized
        User::factory()
            ->count(5)
            ->personalized()
            ->create();

        // Create new users who need to personalize
        User::factory()
            ->count(4)
            ->needsPersonalization()
            ->create();

        //TODO: Remove test user
        User::factory()
            ->create([
                'first_name' => 'Ola',
                'last_name' => 'test',
                'email' => 'otest@vinydeskline.com',
                'desk_id' => '00:ec:eb:50:c2:c8',
                'optimal_sitting_height'=> 75,
                'optimal_standing_height'=> 115,
                'custom_height_1'=> null,
                'custom_name_1'=> 'bajojajo',
                'custom_name_2'=> null,
                'is_admin' => false,
                'needs_personalization' => false,
                'height' => 175,
                'age' => 20,
                'password' => 'password',

            ]);

        // Note: All users have password: "password"
        // Emails follow pattern: {firstLetter}{first4LettersLastName}@vinydeskline.com
    }
}
