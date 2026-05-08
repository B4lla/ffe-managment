<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->mediumText('dni_cif')->change();
            $table->mediumText('email')->nullable()->change();
            $table->mediumText('telefono1')->nullable()->change();
            $table->mediumText('telefono2')->nullable()->change();
            $table->mediumText('provincia')->nullable()->change();
            $table->mediumText('municipio')->nullable()->change();
            $table->mediumText('direccion')->nullable()->change();
            $table->mediumText('codigo_postal')->nullable()->change();
        });

        Schema::table('usuarios', function (Blueprint $table) {
            $table->mediumText('nombre')->change();
            $table->mediumText('email')->change();
            $table->mediumText('dni_cif')->nullable()->change();
            $table->mediumText('foto_url')->nullable()->change();
        });

        Schema::table('centros_trabajo', function (Blueprint $table) {
            $table->mediumText('direccion')->nullable()->change();
            $table->mediumText('municipio')->nullable()->change();
            $table->mediumText('provincia')->nullable()->change();
            $table->mediumText('codigo_postal')->nullable()->change();
        });

        Schema::table('representantes', function (Blueprint $table) {
            $table->mediumText('nif')->nullable()->change();
            $table->mediumText('nombre')->nullable()->change();
            $table->mediumText('apellido1')->nullable()->change();
            $table->mediumText('apellido2')->nullable()->change();
        });

        Schema::table('tutores_empresa', function (Blueprint $table) {
            $table->mediumText('nombre_completo')->change();
            $table->mediumText('dni')->nullable()->change();
            $table->mediumText('email')->nullable()->change();
            $table->mediumText('telefono')->nullable()->change();
        });

        Schema::table('convenios', function (Blueprint $table) {
            $table->mediumText('resp_gestion_nombre')->nullable()->change();
            $table->mediumText('resp_gestion_telefono')->nullable()->change();
            $table->mediumText('resp_gestion_email')->nullable()->change();
            $table->mediumText('resp_ies_nombre')->nullable()->change();
            $table->mediumText('resp_ies_telefono')->nullable()->change();
            $table->mediumText('resp_ies_email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('dni_cif', 512)->change();
            $table->string('email', 512)->nullable()->change();
            $table->string('telefono1', 512)->nullable()->change();
            $table->string('telefono2', 512)->nullable()->change();
            $table->string('provincia', 512)->nullable()->change();
            $table->string('municipio', 512)->nullable()->change();
            $table->text('direccion')->nullable()->change();
            $table->string('codigo_postal', 512)->nullable()->change();
        });

        Schema::table('usuarios', function (Blueprint $table) {
            $table->text('nombre')->change();
            $table->text('email')->change();
            $table->string('dni_cif', 512)->nullable()->change();
            $table->text('foto_url')->nullable()->change();
        });

        Schema::table('centros_trabajo', function (Blueprint $table) {
            $table->text('direccion')->nullable()->change();
            $table->string('municipio', 512)->nullable()->change();
            $table->string('provincia', 512)->nullable()->change();
            $table->string('codigo_postal', 512)->nullable()->change();
        });

        Schema::table('representantes', function (Blueprint $table) {
            $table->string('nif', 512)->nullable()->change();
            $table->string('nombre', 512)->nullable()->change();
            $table->string('apellido1', 512)->nullable()->change();
            $table->string('apellido2', 512)->nullable()->change();
        });

        Schema::table('tutores_empresa', function (Blueprint $table) {
            $table->string('nombre_completo', 512)->change();
            $table->string('dni', 512)->nullable()->change();
            $table->string('email', 512)->nullable()->change();
            $table->string('telefono', 512)->nullable()->change();
        });

        Schema::table('convenios', function (Blueprint $table) {
            $table->text('resp_gestion_nombre')->nullable()->change();
            $table->text('resp_gestion_telefono')->nullable()->change();
            $table->text('resp_gestion_email')->nullable()->change();
            $table->text('resp_ies_nombre')->nullable()->change();
            $table->text('resp_ies_telefono')->nullable()->change();
            $table->text('resp_ies_email')->nullable()->change();
        });
    }
};
