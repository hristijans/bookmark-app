<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feed_id')->constrained('feeds')->cascadeOnDelete();
            $table->string('guid')->nullable();
            $table->string('title')->nullable();
            $table->string('url')->nullable();
            $table->longText('content')->nullable();
            $table->text('summary')->nullable();
            $table->string('author')->nullable();
            $table->json('tags')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['feed_id', 'guid']);
            $table->index(['user_id']);
            $table->index(['feed_id', 'published_at']);
            $table->index(['published_at']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_items');
    }
};
