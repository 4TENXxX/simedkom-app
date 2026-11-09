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
        Schema::create('news_clippings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('online')->index();
            $table->string('url')->nullable();
            $table->string('title');
            $table->date('published_at');
            $table->string('author')->nullable();
            $table->string('category')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('diajukan')->index();
            $table->text('admin_notes')->nullable();
            $table->string('duplicate_status')->default('no_duplicate')->index();
            $table->string('keyword_status')->default('manual_review');
            $table->string('sentiment_label')->nullable();
            $table->decimal('sentiment_confidence', 5, 2)->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->index(['contract_id', 'status']);
            $table->index(['media_profile_id', 'created_at']);
            $table->index('url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_clippings');
    }
};
