<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->enum('type', ['xml', 'json'])->default('xml');
            $table->string('source')->nullable(); // e.g., site name derived from feed metadata
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamp('last_item_published_at')->nullable();
            $table->string('status')->default('idle'); // idle, syncing, error
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'url']);
            $table->index(['user_id', 'type']);
            $table->index(['last_fetched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeds');
    }
};
