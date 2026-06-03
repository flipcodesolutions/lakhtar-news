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
        // Step 1: Add new language enum column
        Schema::table('users', function (Blueprint $table) {
            $table->enum('language', ['eng', 'hin', 'guj'])
                ->default('eng')
                ->after('email');
        });

        // Step 2: Migrate existing data
        DB::table('users')
            ->where('language_id', 1)
            ->update(['language' => 'eng']);

        DB::table('users')
            ->where('language_id', 2)
            ->update(['language' => 'hin']);

        DB::table('users')
            ->where('language_id', 3)
            ->update(['language' => 'guj']);

        // Step 3: Drop foreign key and old column
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->dropColumn('language_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('language_id')->nullable()->after('email');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('language');
        });
    }
};
