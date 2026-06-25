<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->text('descriptionInHindi')->nullable()->change();
            $table->text('descriptionInGujarati')->nullable()->change();
        });

        if (Schema::hasColumn('news', 'hindi_description')) {
            DB::statement("
                UPDATE news
                SET descriptionInHindi = COALESCE(NULLIF(descriptionInHindi, ''), hindi_description)
            ");
        }

        if (Schema::hasColumn('news', 'gujarati_description')) {
            DB::statement("
                UPDATE news
                SET descriptionInGujarati = COALESCE(NULLIF(descriptionInGujarati, ''), gujarati_description)
            ");
        }

        Schema::table('news', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('news', 'hindi_description')) {
                $columnsToDrop[] = 'hindi_description';
            }

            if (Schema::hasColumn('news', 'gujarati_description')) {
                $columnsToDrop[] = 'gujarati_description';
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('descriptionInHindi')->nullable()->change();
            $table->string('descriptionInGujarati')->nullable()->change();
            $table->text('hindi_description')->nullable()->after('title');
            $table->text('gujarati_description')->nullable()->after('hindi_description');
        });
    }
};
