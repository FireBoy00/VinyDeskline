<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('type');        // uniform / cleaning
            $table->string('title');
            $table->time('start_time');
            $table->time('end_time');
            $table->date('date')->nullable();
            $table->enum('frequency', ['once','daily','multiple'])->default('daily');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};

