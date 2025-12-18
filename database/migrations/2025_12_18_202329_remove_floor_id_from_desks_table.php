<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove floor_id from desks table - desks get their floor from the room they're in.
     * Only room_id OR unassigned should exist, never both room_id and floor_id.
     */
    public function up(): void
    {
        Schema::table('desks', function (Blueprint $table) {
            $table->dropForeign(['floor_id']);
            $table->dropColumn('floor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('desks', function (Blueprint $table) {
            $table->foreignId('floor_id')->nullable()->after('room_id')->constrained('floors')->onDelete('set null');
        });
    }
};
