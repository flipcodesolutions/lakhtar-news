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
        Schema::create('news', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('language_id')->nullable()->constrained('languages');

            $table->string('title');
            $table->string('slug')->unique();

            $table->text('short_description')->nullable();
            $table->longText('description');

            $table->string('image')->nullable();
            $table->string('video')->nullable();

            $table->enum('news_type', ['general', 'breaking', 'trending', 'live'])->default('general');
            $table->boolean('is_featured')->default(0);
            $table->bigInteger('total_views')->default(0);

            $table->dateTime('publish_date')->nullable();

            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
