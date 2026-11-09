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
        Schema::table('journalists', function (Blueprint $table): void {
            $table->string('foto_wartawan_path')->nullable()->after('position');
            $table->string('sertifikat_kompetensi_path')->nullable()->after('foto_wartawan_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journalists', function (Blueprint $table): void {
            $table->dropColumn(['foto_wartawan_path', 'sertifikat_kompetensi_path']);
        });
    }
};
