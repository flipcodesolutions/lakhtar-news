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
            $table->string('titleInHindi')->nullable();
            $table->string('descriptionInHindi')->nullable();
            $table->string('titleInGujarati')->nullable();
            $table->string('descriptionInGujarati')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('titleInHindi');
            $table->dropColumn('descriptionInHindi');
            $table->dropColumn('titleInGujarati');
            $table->dropColumn('descriptionInGujarati');
        });
    }
};
