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
        Schema::create('scraping_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('news_clipping_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status')->index();
            $table->string('canonical_url')->nullable();
            $table->string('scraped_title')->nullable();
            $table->longText('scraped_content')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('scraped_at')->nullable();
            $table->timestamps();
        });

        Schema::create('keyword_matches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('news_clipping_id')->constrained()->cascadeOnDelete();
            $table->string('keyword');
            $table->unsignedInteger('match_count')->default(0);
            $table->timestamps();
            $table->index(['news_clipping_id', 'keyword']);
        });

        Schema::create('sentiment_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('news_clipping_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('confidence', 5, 2)->nullable();
            $table->string('engine_version');
            $table->longText('analyzed_text')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sentiment_results');
        Schema::dropIfExists('keyword_matches');
        Schema::dropIfExists('scraping_results');
    }
};
