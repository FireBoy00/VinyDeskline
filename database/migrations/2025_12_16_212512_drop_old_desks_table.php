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
        Schema::dropIfExists('desks');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate old structure if rollback is needed
        Schema::create('desks', function (Blueprint $table) {
            $table->id();
            $table->string('desk_id')->unique();
            $table->json('state')->nullable();
            $table->timestamps();
        });
    }
};
