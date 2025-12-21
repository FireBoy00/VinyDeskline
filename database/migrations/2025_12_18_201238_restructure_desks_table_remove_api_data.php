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
     * This migration restructures the desks table to only store organizational data (room/floor assignments)
     * Real-time data (position, speed, status, etc.) should be fetched from the API, not stored in DB.
     */
    public function up(): void
    {
        // SQLite doesn't support dropping primary keys directly, so we need to recreate the table
        Schema::dropIfExists('desks_backup');
        
        // Create a backup of important data (room/floor assignments)
        DB::statement('CREATE TABLE desks_backup AS SELECT desk_id, room_id, floor_id, is_removed_from_api FROM desks');
        
        // Drop the old table
        Schema::dropIfExists('desks');
        
        // Create the new streamlined desks table
        Schema::create('desks', function (Blueprint $table) {
            $table->string('desk_id')->primary(); // desk_id is now the primary key
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->foreignId('floor_id')->nullable()->constrained('floors')->onDelete('set null');
            $table->boolean('is_removed_from_api')->default(false);
            $table->timestamps();
        });
        
        // Restore the data
        DB::statement('INSERT INTO desks (desk_id, room_id, floor_id, is_removed_from_api, created_at, updated_at) 
                       SELECT desk_id, room_id, floor_id, is_removed_from_api, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP 
                       FROM desks_backup');
        
        // Clean up backup table
        Schema::dropIfExists('desks_backup');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backup current data
        Schema::dropIfExists('desks_backup');
        DB::statement('CREATE TABLE desks_backup AS SELECT desk_id, room_id, floor_id, is_removed_from_api FROM desks');
        
        // Drop the current table
        Schema::dropIfExists('desks');
        
        // Recreate the old structure
        Schema::create('desks', function (Blueprint $table) {
            $table->id();
            $table->string('desk_id')->unique();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->foreignId('floor_id')->nullable()->constrained('floors')->onDelete('set null');
            $table->boolean('is_removed_from_api')->default(false);
            $table->string('name')->nullable();
            $table->string('manufacturer')->nullable();
            $table->integer('position_mm')->nullable();
            $table->integer('speed_mms')->nullable();
            $table->string('status')->nullable();
            $table->integer('activations_counter')->default(0);
            $table->integer('sit_stand_counter')->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
        
        // Restore the data
        DB::statement('INSERT INTO desks (desk_id, room_id, floor_id, is_removed_from_api, created_at, updated_at) 
                       SELECT desk_id, room_id, floor_id, is_removed_from_api, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP 
                       FROM desks_backup');
        
        // Clean up
        Schema::dropIfExists('desks_backup');
    }
};
