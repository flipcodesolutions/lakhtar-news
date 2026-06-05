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
        Schema::table('news', function (Blueprint $table) {
            // Drop foreign key constraint first before removing the column
            $table->dropForeign(['language_id']);
            $table->dropColumn('language_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // Add foreign key constraint back
            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
        });
    }
};
