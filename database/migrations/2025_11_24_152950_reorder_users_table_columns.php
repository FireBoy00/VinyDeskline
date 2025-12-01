<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Reorder users table columns in a logical order:
     * 1. ID (primary key)
     * 2. Personal Information (first_name, last_name, email)
     * 3. Authentication (password, email_verified_at, remember_token)
     * 4. User Settings (height, age, needs_personalization)
     * 5. Role (is_admin)
     * 6. Timestamps (created_at, updated_at)
     */
    public function up(): void
    {
        // Create a new temporary table with the correct column order
        Schema::create('users_new', function (Blueprint $table) {
            // Primary Key
            $table->id();
            
            // Personal Information
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            
            // Authentication
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            
            // User Settings / Personalization
            $table->decimal('height', 5, 2)->nullable();
            $table->integer('age')->nullable();
            $table->boolean('needs_personalization')->default(true);
            
            // Role
            $table->boolean('is_admin')->default(false);
            
            // Timestamps
            $table->timestamps();
        });

        // Copy data from old table to new table
        DB::statement(<<<SQL
            INSERT INTO users_new (
                id,
                first_name,
                last_name,
                email,
                email_verified_at,
                password,
                remember_token,
                height,
                age,
                needs_personalization,
                is_admin,
                created_at,
                updated_at
            )
            SELECT
                id,
                first_name,
                last_name,
                email,
                email_verified_at,
                password,
                remember_token,
                height,
                age,
                needs_personalization,
                is_admin,
                created_at,
                updated_at
            FROM users
        SQL);

        // Drop the old table
        Schema::dropIfExists('users');

        // Rename the new table to the original name
        Schema::rename('users_new', 'users');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse as this is just a column reordering
        // The data remains the same, only the internal column order changes
    }
};
