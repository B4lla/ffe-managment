<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('roles') && Schema::hasTable('convenios') && Schema::hasTable('documentos_pdf')) {
            return;
        }

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        DB::table('roles')->insert([
            ['id' => 1, 'nombre' => 'Administrador', 'descripcion' => 'Gestion total de la aplicacion'],
            ['id' => 2, 'nombre' => 'Direccion', 'descripcion' => 'Firma convenios por parte del centro'],
            ['id' => 3, 'nombre' => 'Coordinador FFE', 'descripcion' => 'Gestion de convenios por departamento'],
            ['id' => 4, 'nombre' => 'Profesor tutor', 'descripcion' => 'Gestion y validacion de convenios asignados'],
            ['id' => 5, 'nombre' => 'Profesor', 'descripcion' => 'Consulta de convenios sin modificacion'],
            ['id' => 6, 'nombre' => 'Secretaria', 'descripcion' => 'Generacion y carga documental de convenios'],
            ['id' => 7, 'nombre' => 'Empresa externa', 'descripcion' => 'Acceso limitado a sus propios convenios y datos'],
        ]);

        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('familia_profesional', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_razon_social', 300);
            $table->mediumText('dni_cif');
            $table->char('dni_cif_hash', 64)->unique();
            $table->text('actividad')->nullable();
            $table->enum('categoria', ['ayuntamiento', 'colegios_institutos', 'empresa'])->nullable();
            $table->enum('tipo', ['verde', 'amarilla', 'roja'])->nullable();
            $table->mediumText('email')->nullable();
            $table->char('email_hash', 64)->nullable()->index();
            $table->mediumText('telefono1')->nullable();
            $table->char('telefono1_hash', 64)->nullable()->index();
            $table->mediumText('telefono2')->nullable();
            $table->char('telefono2_hash', 64)->nullable()->index();
            $table->mediumText('provincia')->nullable();
            $table->mediumText('municipio')->nullable();
            $table->mediumText('direccion')->nullable();
            $table->mediumText('codigo_postal')->nullable();
            $table->timestamps();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->mediumText('nombre');
            $table->mediumText('email');
            $table->mediumText('dni_cif')->nullable();
            $table->char('email_hash', 64)->unique();
            $table->string('password');
            $table->rememberToken();
            $table->mediumText('foto_url')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->nullOnDelete();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('rol_id')->nullable()->default(5)->constrained('roles')->nullOnDelete();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('ciclos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('grado', 50);
            $table->foreignId('departamento_id')->constrained('departamentos')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->integer('anio');
            $table->foreignId('ciclo_id')->constrained('ciclos')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('dni', 15)->nullable()->unique();
            $table->foreignId('ciclo_id')->constrained('ciclos')->cascadeOnDelete();
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('centros_trabajo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->mediumText('direccion')->nullable();
            $table->mediumText('municipio')->nullable();
            $table->mediumText('provincia')->nullable();
            $table->mediumText('codigo_postal')->nullable();
            $table->timestamps();
        });

        Schema::create('representantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->mediumText('nif')->nullable();
            $table->mediumText('nombre')->nullable();
            $table->mediumText('apellido1')->nullable();
            $table->mediumText('apellido2')->nullable();
            $table->timestamps();
        });

        Schema::create('tutores_empresa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->mediumText('nombre_completo');
            $table->mediumText('dni')->nullable();
            $table->mediumText('email')->nullable();
            $table->mediumText('telefono')->nullable();
            $table->timestamps();
        });

        Schema::create('convenios', function (Blueprint $table) {
            $table->id();
            $table->string('num_convenio', 50)->nullable()->unique();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('profesor_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('representante_id')->nullable()->constrained('representantes')->nullOnDelete();
            $table->mediumText('resp_gestion_nombre')->nullable();
            $table->mediumText('resp_gestion_telefono')->nullable();
            $table->mediumText('resp_gestion_email')->nullable();
            $table->mediumText('resp_ies_nombre')->nullable();
            $table->mediumText('resp_ies_telefono')->nullable();
            $table->mediumText('resp_ies_email')->nullable();
            $table->date('fecha_firma')->nullable();
            $table->string('estado', 50)->default('borrador');
            $table->string('horario_practicas', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('empresa_contacto_familia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('departamento_id')->constrained('departamentos')->cascadeOnDelete();
            $table->foreignId('profesor_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('convenio_tutor_empresa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios')->cascadeOnDelete();
            $table->foreignId('tutor_empresa_id')->constrained('tutores_empresa')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['convenio_id', 'tutor_empresa_id'], 'unique_convenio_tutor');
        });

        Schema::create('convenio_ciclo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios')->cascadeOnDelete();
            $table->foreignId('ciclo_id')->constrained('ciclos')->cascadeOnDelete();
            $table->integer('plazas')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['convenio_id', 'ciclo_id'], 'unique_convenio_ciclo');
        });

        Schema::create('alumno_convenio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('tutor_empresa_id')->nullable()->constrained('tutores_empresa')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['convenio_id', 'alumno_id'], 'unique_convenio_alumno');
        });

        Schema::create('horarios_practicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_empresa_id')->constrained('tutores_empresa')->cascadeOnDelete();
            $table->integer('slot_numero');
            $table->string('horario', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('registro_contactos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('profesor_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('resultado', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_contacto')->useCurrent();
        });

        Schema::create('documentos_pdf', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios')->cascadeOnDelete();
            $table->foreignId('subido_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('tipo', 50);
            $table->string('estado_doc', 50)->default('valido');
            $table->text('ruta_archivo');
            $table->boolean('es_erroneo')->default(false);
            $table->text('motivo_error')->nullable();
            $table->timestamps();
        });

        Schema::create('tareas_pendientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->nullable()->constrained('convenios')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->cascadeOnDelete();
            $table->string('tipo_tarea', 100)->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('completada')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('tareas_pendientes');
        Schema::dropIfExists('documentos_pdf');
        Schema::dropIfExists('registro_contactos');
        Schema::dropIfExists('horarios_practicas');
        Schema::dropIfExists('alumno_convenio');
        Schema::dropIfExists('convenio_ciclo');
        Schema::dropIfExists('convenio_tutor_empresa');
        Schema::dropIfExists('empresa_contacto_familia');
        Schema::dropIfExists('convenios');
        Schema::dropIfExists('tutores_empresa');
        Schema::dropIfExists('representantes');
        Schema::dropIfExists('centros_trabajo');
        Schema::dropIfExists('alumnos');
        Schema::dropIfExists('cursos');
        Schema::dropIfExists('ciclos');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('empresas');
        Schema::dropIfExists('departamentos');
        Schema::dropIfExists('roles');

        Schema::enableForeignKeyConstraints();
    }
};
