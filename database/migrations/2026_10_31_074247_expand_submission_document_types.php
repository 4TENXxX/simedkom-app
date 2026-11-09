<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table): void {
            $table->string('jenis_dokumen', 100)->change();
        });
    }

    public function down(): void
    {
        $hasExpandedTypes = Schema::hasTable('documents')
            && DB::table('documents')
                ->whereNotIn('jenis_dokumen', ['NIB', 'NPWP', 'Proposal'])
                ->exists();

        if ($hasExpandedTypes) {
            throw new RuntimeException('Cannot roll back document types while expanded document records exist.');
        }

        Schema::table('documents', function (Blueprint $table): void {
            $table->enum('jenis_dokumen', ['NIB', 'NPWP', 'Proposal'])->change();
        });
    }
};
