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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_profile_id')->constrained('media_profiles')->cascadeOnDelete();
            $table->string('nomor_kontrak')->unique();
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir');
            $table->decimal('nilai_kontrak', 15, 2);
            $table->unsignedInteger('kuota_berita');
            $table->decimal('harga_per_publikasi', 15, 2);
            $table->string('jenis_layanan');
            $table->string('status')->default('draft')->index();
            $table->string('dokumen_kontrak')->nullable();
            $table->timestamps();

            $table->index(['media_profile_id', 'status']);
            $table->index(['tanggal_mulai', 'tanggal_berakhir']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
