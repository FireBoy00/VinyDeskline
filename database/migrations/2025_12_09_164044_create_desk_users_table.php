<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desk_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('desk_id');
            $table->string('status');       // Occupied / Not Occupied
            $table->string('height');       // Numeric or text input (your UI allows text)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desk_users');
    }
};
