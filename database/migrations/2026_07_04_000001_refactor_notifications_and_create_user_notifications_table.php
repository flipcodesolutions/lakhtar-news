<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'is_read']);
            $table->string('reference_type')->nullable()->after('type');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            $table->string('audience')->nullable()->after('reference_id');
        });

        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['notification_id', 'user_id']);
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['reference_type', 'reference_id', 'audience']);
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->boolean('is_read')->default(false);
        });
    }
};
