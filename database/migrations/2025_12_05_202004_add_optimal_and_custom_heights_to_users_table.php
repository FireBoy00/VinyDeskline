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
        Schema::table('users', function (Blueprint $table) {
            $table->string('desk_id')->nullable()->after('is_admin');
            $table->integer('optimal_sitting_height')->nullable()->after('desk_id');
            $table->integer('optimal_standing_height')->nullable()->after('optimal_sitting_height');
            $table->integer('custom_sitting_height')->nullable()->after('optimal_standing_height');
            $table->integer('custom_standing_height')->nullable()->after('custom_sitting_height');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['optimal_sitting_height', 'optimal_standing_height', 'custom_sitting_height', 'custom_standing_height']);
        });
    }
};
