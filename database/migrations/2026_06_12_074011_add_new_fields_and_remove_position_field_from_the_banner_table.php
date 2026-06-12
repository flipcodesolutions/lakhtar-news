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
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('position');
            $table->date('start_date')->after('id')->default(now());
            $table->date('end_date')->after('id')->default(now()->addDays(7));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('position')->after('id')->default('top');
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
        });
    }
};
