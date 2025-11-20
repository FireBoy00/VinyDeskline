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
        // Create admin user with personalization already completed
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'admin@vinydeskline.com',
            'password' => bcrypt('password'),
            'height' => 175.5,
            'age' => 35,
            'needs_personalization' => false,
        ]);

        // Create new user who needs to personalize
        User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@vinydeskline.com',
            'password' => bcrypt('password'),
            'needs_personalization' => true,
        ]);

        // Create additional users with varying states
        User::factory()->create([
            'name' => 'Bob Wilson',
            'email' => 'bob@vinydeskline.com',
            'password' => bcrypt('password'),
            'height' => 182.0,
            'age' => 28,
            'needs_personalization' => false,
        ]);

        User::factory()->create([
            'name' => 'Alice Johnson',
            'email' => 'alice@vinydeskline.com',
            'password' => bcrypt('password'),
            'needs_personalization' => true,
        ]);
    }
}
