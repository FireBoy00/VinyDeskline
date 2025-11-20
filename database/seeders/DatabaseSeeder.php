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

        // Note: All users have password: "password"
        // Emails follow pattern: {firstLetter}{first4LettersLastName}@vinydeskline.com
    }
}
