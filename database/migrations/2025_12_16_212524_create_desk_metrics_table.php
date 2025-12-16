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
        Schema::create('desk_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('desk_id'); // Store desk_id directly for faster queries
            $table->integer('height_mm'); // Current height in millimeters
            $table->boolean('is_sitting'); // True if height < 1000mm, false otherwise
            $table->timestamp('recorded_at'); // When this metric was recorded
            $table->timestamps();

            // Index for faster queries
            $table->index(['desk_id', 'recorded_at']);
            $table->index('recorded_at');

            // Foreign key to desks table
            $table->foreign('desk_id')->references('desk_id')->on('desks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desk_metrics');
    }
};
