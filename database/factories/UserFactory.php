<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * List of first names for generating users
     */
    protected static array $firstNames = [
        'John', 'Jane', 'Michael', 'Sarah', 'David', 'Emily', 'Robert', 'Lisa',
        'James', 'Mary', 'William', 'Patricia', 'Richard', 'Jennifer', 'Charles',
        'Linda', 'Thomas', 'Elizabeth', 'Daniel', 'Susan', 'Matthew', 'Jessica',
        'Anthony', 'Karen', 'Mark', 'Nancy', 'Donald', 'Betty', 'Steven', 'Margaret'
    ];

    /**
     * List of last names for generating users
     */
    protected static array $lastNames = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller',
        'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez',
        'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
        'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark',
        'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King'
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->randomElement(self::$firstNames);
        $lastName = fake()->randomElement(self::$lastNames);
        
        // Generate email: first letter of first name + first 4 letters of last name (or all if shorter)
        $emailPrefix = strtolower(substr($firstName, 0, 1) . substr($lastName, 0, min(4, strlen($lastName))));
        $email = $emailPrefix . '@vinydeskline.com';

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'needs_personalization' => true,
            'is_admin' => false,
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }

    /**
     * Indicate that the user has completed personalization.
     */
    public function personalized(): static
    {
        return $this->state(fn (array $attributes) => [
            'needs_personalization' => false,
            'height' => fake()->randomFloat(1, 150, 200),
            'age' => fake()->numberBetween(22, 65),
        ]);
    }

    /**
     * Indicate that the user needs personalization.
     */
    public function needsPersonalization(): static
    {
        return $this->state(fn (array $attributes) => [
            'needs_personalization' => true,
            'height' => null,
            'age' => null,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
