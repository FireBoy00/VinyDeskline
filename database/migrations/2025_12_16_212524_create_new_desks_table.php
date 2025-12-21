<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('desks', function (Blueprint $table) {
            $table->id();
            $table->string('desk_id')->unique(); // From API
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->foreignId('floor_id')->nullable()->constrained('floors')->onDelete('set null');
            $table->boolean('is_removed_from_api')->default(false); // Track if desk no longer exists in API
            $table->string('name')->nullable(); // Desk name from API
            $table->string('manufacturer')->nullable(); // From API
            $table->integer('position_mm')->nullable(); // Current position from API
            $table->integer('speed_mms')->nullable(); // Current speed from API
            $table->string('status')->nullable(); // Status from API
            $table->integer('activations_counter')->default(0); // From API
            $table->integer('sit_stand_counter')->default(0); // From API
            $table->timestamp('last_synced_at')->nullable(); // Last time synced with API
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desks');
    }
};
