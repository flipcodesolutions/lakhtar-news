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
            $table->boolean('notification_sent')
                ->default(false)
                ->after('publish_date');

            $table->index(
                ['status', 'notification_sent', 'publish_date'],
                'news_scheduled_notification_lookup'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex('news_scheduled_notification_lookup');
            $table->dropColumn('notification_sent');
        });
    }
};
