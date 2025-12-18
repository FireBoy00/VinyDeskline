<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Reorder columns to: desk_id, room_id, floor_id, is_removed_from_api, name, created_at, updated_at
     */
    public function up(): void
    {
        // SQLite requires table recreation to reorder columns
        Schema::table('desks', function (Blueprint $table) {
            // Backup data
            DB::statement('CREATE TABLE desks_backup AS SELECT desk_id, room_id, floor_id, is_removed_from_api, name, created_at, updated_at FROM desks');
        });

        // Drop old table
        Schema::dropIfExists('desks');

        // Create new table with correct column order
        Schema::create('desks', function (Blueprint $table) {
            $table->string('desk_id')->primary();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->foreignId('floor_id')->nullable()->constrained('floors')->onDelete('set null');
            $table->boolean('is_removed_from_api')->default(0);
            $table->string('name')->nullable();
            $table->timestamps();
        });

        // Restore data
        DB::statement('INSERT INTO desks (desk_id, room_id, floor_id, is_removed_from_api, name, created_at, updated_at) 
                       SELECT desk_id, room_id, floor_id, is_removed_from_api, name, created_at, updated_at 
                       FROM desks_backup');

        // Drop backup table
        DB::statement('DROP TABLE desks_backup');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backup data
        DB::statement('CREATE TABLE desks_backup AS SELECT desk_id, room_id, floor_id, is_removed_from_api, name, created_at, updated_at FROM desks');

        // Drop new table
        Schema::dropIfExists('desks');

        // Recreate old table
        Schema::create('desks', function (Blueprint $table) {
            $table->string('desk_id')->primary();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->boolean('is_removed_from_api')->default(0);
            $table->timestamps();
            $table->foreignId('floor_id')->nullable()->constrained('floors')->onDelete('set null');
            $table->string('name')->nullable();
        });

        // Restore data
        DB::statement('INSERT INTO desks (desk_id, room_id, floor_id, is_removed_from_api, name, created_at, updated_at) 
                       SELECT desk_id, room_id, floor_id, is_removed_from_api, name, created_at, updated_at 
                       FROM desks_backup');

        // Drop backup table
        DB::statement('DROP TABLE desks_backup');
    }
};
