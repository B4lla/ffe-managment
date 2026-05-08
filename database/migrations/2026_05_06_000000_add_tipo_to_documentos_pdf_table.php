<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('documentos_pdf') || Schema::hasColumn('documentos_pdf', 'tipo')) {
            return;
        }

        Schema::table('documentos_pdf', function (Blueprint $table) {
            $table->string('tipo', 50)->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('documentos_pdf') || ! Schema::hasColumn('documentos_pdf', 'tipo')) {
            return;
        }

        Schema::table('documentos_pdf', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
