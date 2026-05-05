<?php

namespace Tests\Feature;

use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class ConveniosImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createBusinessTables();
    }

    public function test_importa_un_convenio_con_empresa_y_relaciones_desde_csv(): void
    {
        DB::table('roles')->updateOrInsert(['id' => 4], ['nombre' => 'Profesor tutor']);
        DB::table('roles')->updateOrInsert(['id' => 6], ['nombre' => 'Secretaria']);

        $departamentoId = DB::table('departamentos')->insertGetId([
            'nombre' => 'Informatica',
            'familia_profesional' => 'Informática',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciclos')->insert([
            'id' => 10,
            'nombre' => 'DAM',
            'grado' => 'GS',
            'departamento_id' => $departamentoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $profesor = User::factory()->create([
            'rol_id' => 4,
            'departamento_id' => $departamentoId,
            'email' => 'tutor@example.com',
        ]);

        $secretaria = User::factory()->create([
            'rol_id' => 6,
            'email' => 'secretaria-import@example.com',
        ]);

        $csv = <<<'CSV'
num_convenio,empresa_nombre,empresa_dni_cif,empresa_actividad,categoria,tipo,contacto_email,contacto_telefono1,domicilio_provincia,domicilio_municipio,domicilio_direccion,domicilio_codigo_postal,representante_nif,representante_nombre,responsable_nombre,responsable_telefono,responsable_email,departamento,tutor_email,tutor_telefono,fecha_firma,estado,observaciones,tutor_empresa_nombre,tutor_empresa_dni,tutor_horarios,ciclos
CNV-001,Acme SL,B12345678,Informática,Colegios/Institutos,Empresas buenas / verdes,contacto@acme.com,600111222,Madrid,Madrid,Calle 1,28001,12345678A,Ana,Secretaría,911111111,secretaria@braille.es,Informatica,tutor@example.com,622333444,2025-01-15,en vigor,Obs inicial,Carlos Tutor,87654321B,"1,2,3",DAM
CSV;

        $file = UploadedFile::fake()->createWithContent('convenios.csv', $csv);

        $response = $this
            ->actingAs($secretaria)
            ->post(route('convenios.importar'), [
                'csv_file' => $file,
            ]);

        $response
            ->assertRedirect(route('convenios.index'))
            ->assertSessionHas('status');

        $empresa = Empresa::query()->firstOrFail();
        $convenio = Convenio::query()->firstOrFail();
        $tutorEmpresa = Tutor::query()->firstOrFail();

        $this->assertSame('Acme SL', $empresa->nombre_razon_social);
        $this->assertSame('B12345678', $empresa->dni_cif);
        $this->assertSame('colegios_institutos', $empresa->categoria);
        $this->assertSame('verde', $empresa->tipo);
        $this->assertSame('contacto@acme.com', $empresa->email);

        $this->assertSame('CNV-001', $convenio->num_convenio);
        $this->assertSame($empresa->id, $convenio->empresa_id);
        $this->assertSame($profesor->id, $convenio->profesor_id);
        $this->assertSame('en_vigor', $convenio->estado);
        $this->assertSame('Secretaría', $convenio->resp_gestion_nombre);
        $this->assertSame('secretaria@braille.es', $convenio->resp_gestion_email);
        $this->assertSame('tutor@example.com', $convenio->resp_ies_email);

        $this->assertSame('Carlos Tutor', $tutorEmpresa->nombre_completo);
        $this->assertSame('87654321B', $tutorEmpresa->dni);

        $this->assertDatabaseCount('representantes', 1);
        $this->assertDatabaseCount('empresa_contacto_familia', 1);
        $this->assertDatabaseCount('convenio_tutor_empresa', 1);
        $this->assertDatabaseCount('horarios_practicas', 3);
        $this->assertDatabaseCount('convenio_ciclo', 1);
    }

    private function createBusinessTables(): void
    {
        if (Schema::hasTable('roles')) {
            return;
        }

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('familia_profesional')->nullable();
            $table->timestamps();
        });

        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_razon_social');
            $table->text('dni_cif');
            $table->char('dni_cif_hash', 64)->unique();
            $table->text('actividad')->nullable();
            $table->string('categoria')->nullable();
            $table->string('tipo')->nullable();
            $table->text('email')->nullable();
            $table->char('email_hash', 64)->nullable();
            $table->text('telefono1')->nullable();
            $table->char('telefono1_hash', 64)->nullable();
            $table->text('telefono2')->nullable();
            $table->char('telefono2_hash', 64)->nullable();
            $table->text('provincia')->nullable();
            $table->text('municipio')->nullable();
            $table->text('direccion')->nullable();
            $table->text('codigo_postal')->nullable();
            $table->timestamps();
        });

        Schema::create('ciclos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('grado');
            $table->unsignedBigInteger('departamento_id');
            $table->timestamps();
        });

        Schema::create('representantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->text('nif')->nullable();
            $table->text('nombre')->nullable();
            $table->text('apellido1')->nullable();
            $table->text('apellido2')->nullable();
            $table->timestamps();
        });

        Schema::create('convenios', function (Blueprint $table) {
            $table->id();
            $table->string('num_convenio')->nullable()->unique();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('profesor_id')->nullable();
            $table->unsignedBigInteger('representante_id')->nullable();
            $table->text('resp_gestion_nombre')->nullable();
            $table->text('resp_gestion_telefono')->nullable();
            $table->text('resp_gestion_email')->nullable();
            $table->text('resp_ies_nombre')->nullable();
            $table->text('resp_ies_telefono')->nullable();
            $table->text('resp_ies_email')->nullable();
            $table->date('fecha_firma')->nullable();
            $table->string('estado')->default('borrador');
            $table->string('horario_practicas')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('empresa_contacto_familia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('departamento_id');
            $table->unsignedBigInteger('profesor_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('centros_trabajo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->text('direccion')->nullable();
            $table->text('municipio')->nullable();
            $table->text('provincia')->nullable();
            $table->text('codigo_postal')->nullable();
            $table->timestamps();
        });

        Schema::create('tutores_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->text('nombre_completo');
            $table->text('dni')->nullable();
            $table->text('email')->nullable();
            $table->text('telefono')->nullable();
            $table->timestamps();
        });

        Schema::create('convenio_tutor_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('convenio_id');
            $table->unsignedBigInteger('tutor_empresa_id');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('horarios_practicas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tutor_empresa_id');
            $table->integer('slot_numero');
            $table->string('horario')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('convenio_ciclo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('convenio_id');
            $table->unsignedBigInteger('ciclo_id');
            $table->integer('plazas')->default(1);
            $table->timestamp('created_at')->nullable();
        });
    }
}
