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
        Schema::create('sensor_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('desk_id')->nullable();
            $table->float('temperature');
            $table->float('humidity');
            $table->float('light');
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->foreign('desk_id')->references('desk_id')->on('desks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_metrics');
    }
};
