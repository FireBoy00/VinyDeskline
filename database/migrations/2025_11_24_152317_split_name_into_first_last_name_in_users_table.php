<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
        });

        // Migrate existing data: split 'name' into first_name and last_name
        DB::table('users')->get()->each(function ($user) {
            $nameParts = explode(' ', $user->name, 2);
            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $nameParts[0] ?? '',
                'last_name' => $nameParts[1] ?? '',
            ]);
        });

        // Remove the old 'name' column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
        });

        // Migrate data back: combine first_name and last_name into name
        DB::table('users')->get()->each(function ($user) {
            $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            DB::table('users')->where('id', $user->id)->update([
                'name' => $name ?: 'User',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
