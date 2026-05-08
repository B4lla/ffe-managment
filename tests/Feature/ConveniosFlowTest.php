<?php

namespace Tests\Feature;

use App\Models\Convenio;
use App\Models\DocumentoPdf;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConveniosFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'https://api.resend.com/*' => Http::response(['id' => 'email_test'], 200),
        ]);
    }

    public function test_crear_con_fecha_no_deja_el_convenio_en_vigor_sin_pdf_final(): void
    {
        $secretaria = User::factory()->create(['rol_id' => 6]);

        $response = $this->actingAs($secretaria)->post(route('convenios.store'), [
            'empresa_nombre' => 'Empresa Nueva SL',
            'empresa_dni_cif' => 'B11111111',
            'fecha_firma' => '2026-05-05',
        ]);

        $convenio = Convenio::query()->firstOrFail();

        $response->assertRedirect(route('convenios.show', $convenio->id));
        $this->assertSame('pendiente_secretaria', $convenio->estado);
        $this->assertDatabaseMissing('documentos_pdf', ['convenio_id' => $convenio->id, 'tipo' => 'firmado_centro']);

        $empresaUser = User::factory()->create(['rol_id' => 7, 'empresa_id' => $convenio->empresa_id]);

        $this->actingAs($empresaUser)
            ->get(route('convenios.show', $convenio->id))
            ->assertOk()
            ->assertDontSee('Descargar convenio firmado');
    }

    public function test_flujo_documental_hasta_convenio_en_vigor(): void
    {
        Storage::fake('local');

        $departamentoId = \DB::table('departamentos')->insertGetId([
            'nombre' => 'Informatica',
            'familia_profesional' => 'Informatica',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $empresa = Empresa::create([
            'nombre_razon_social' => 'Acme SL',
            'dni_cif' => 'B12345678',
        ]);

        $secretaria = User::factory()->create(['rol_id' => 6]);
        $direccion = User::factory()->create(['rol_id' => 2]);
        $tutor = User::factory()->create(['rol_id' => 4, 'departamento_id' => $departamentoId]);
        $empresaUser = User::factory()->create(['rol_id' => 7, 'empresa_id' => $empresa->id]);

        $convenio = Convenio::create([
            'empresa_id' => $empresa->id,
            'profesor_id' => $tutor->id,
            'estado' => 'pendiente_secretaria',
        ]);

        $this->actingAs($empresaUser)
            ->get(route('convenios.show', $convenio->id))
            ->assertOk()
            ->assertDontSee('Descargar y firmar (Empresa)')
            ->assertDontSee('Descargar convenio firmado');

        $this->actingAs($secretaria)->post(route('convenios.generar_pdf.store', $convenio->id), [
            'pdf' => UploadedFile::fake()->create('inicial.pdf', 20, 'application/pdf'),
        ])->assertRedirect(route('convenios.show', $convenio->id));

        $this->assertSame('pendiente_firma_empresa', $convenio->fresh()->estado);
        $this->assertDatabaseHas('documentos_pdf', ['convenio_id' => $convenio->id, 'tipo' => 'provisional']);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.resend.com/emails'
            && in_array($empresaUser->email, $request['to'], true)
            && $request['subject'] === 'Tarea pendiente: Firmar empresa');

        $this->actingAs($empresaUser)
            ->get(route('convenios.show', $convenio->id))
            ->assertOk()
            ->assertSee('Descargar y firmar (Empresa)')
            ->assertDontSee('Descargar convenio firmado');

        $this->actingAs($empresaUser)
            ->get(route('tareas_pendientes.index'))
            ->assertOk()
            ->assertSee('Firmar empresa')
            ->assertSee('Descarga el PDF inicial');

        $this->actingAs($empresaUser)
            ->get(route('convenios.firmar_empresa', $convenio->id))
            ->assertOk()
            ->assertSee('Descargar PDF inicial')
            ->assertSee('Confirmar firma y subir PDF firmado')
            ->assertSee('Indicar error en vez de firmar');

        $this->actingAs($empresaUser)->post(route('convenios.firmar_empresa.store', $convenio->id), [
            'pdf' => UploadedFile::fake()->create('empresa.pdf', 20, 'application/pdf'),
        ])->assertRedirect(route('convenios.show', $convenio->id));

        $this->assertSame('pendiente_validacion_tutor', $convenio->fresh()->estado);
        $this->assertDatabaseHas('documentos_pdf', ['convenio_id' => $convenio->id, 'tipo' => 'firmado_empresa']);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.resend.com/emails'
            && in_array($tutor->email, $request['to'], true)
            && $request['subject'] === 'Tarea pendiente: Validar firma');

        $this->actingAs($tutor)
            ->get(route('convenios.show', $convenio->id))
            ->assertOk()
            ->assertSee('Validar firma de empresa');

        $this->actingAs($tutor)
            ->get(route('tareas_pendientes.index'))
            ->assertOk()
            ->assertSee('Validar firma')
            ->assertSee('La empresa ha subido el convenio firmado');

        $this->actingAs($direccion)
            ->get(route('convenios.show', $convenio->id))
            ->assertOk()
            ->assertDontSee('Firmar por el centro');

        $this->actingAs($tutor)->post(route('convenios.validar_firma.store', $convenio->id), [
            'decision' => 'ok',
        ])->assertRedirect(route('convenios.show', $convenio->id));

        $this->assertSame('pendiente_firma_direccion', $convenio->fresh()->estado);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.resend.com/emails'
            && in_array($direccion->email, $request['to'], true)
            && $request['subject'] === 'Tarea pendiente: Firmar centro');

        $this->actingAs($direccion)
            ->get(route('convenios.show', $convenio->id))
            ->assertOk()
            ->assertSee('Firmar por el centro');

        $this->actingAs($empresaUser)
            ->get(route('convenios.show', $convenio->id))
            ->assertOk()
            ->assertDontSee('Descargar convenio firmado');

        $this->actingAs($direccion)->post(route('convenios.firmar_centro.store', $convenio->id), [
            'pdf' => UploadedFile::fake()->create('centro.pdf', 20, 'application/pdf'),
            'fecha_firma' => '2026-05-05',
        ])->assertRedirect(route('convenios.show', $convenio->id));

        $convenio->refresh();
        $this->assertSame('en_vigor', $convenio->estado);
        $this->assertSame('2026-05-05', $convenio->fecha_firma->toDateString());
        $this->assertDatabaseHas('documentos_pdf', ['convenio_id' => $convenio->id, 'tipo' => 'firmado_centro']);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.resend.com/emails'
            && in_array($empresaUser->email, $request['to'], true)
            && $request['subject'] === 'Tarea pendiente: Descargar convenio firmado');

        $this->actingAs($empresaUser)
            ->get(route('convenios.show', $convenio->id))
            ->assertOk()
            ->assertSee('Descargar convenio firmado');
    }

    public function test_no_se_pueden_saltar_pasos_del_flujo_por_url(): void
    {
        Storage::fake('local');

        $departamentoId = \DB::table('departamentos')->insertGetId([
            'nombre' => 'Informatica',
            'familia_profesional' => 'Informatica',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $empresa = Empresa::create([
            'nombre_razon_social' => 'Orden Correcto SL',
            'dni_cif' => 'B22222222',
        ]);

        $secretaria = User::factory()->create(['rol_id' => 6]);
        $direccion = User::factory()->create(['rol_id' => 2]);
        $tutor = User::factory()->create(['rol_id' => 4, 'departamento_id' => $departamentoId]);
        $empresaUser = User::factory()->create(['rol_id' => 7, 'empresa_id' => $empresa->id]);

        $convenio = Convenio::create([
            'empresa_id' => $empresa->id,
            'profesor_id' => $tutor->id,
            'estado' => 'pendiente_secretaria',
        ]);

        $this->actingAs($empresaUser)->post(route('convenios.firmar_empresa.store', $convenio->id), [
            'pdf' => UploadedFile::fake()->create('empresa.pdf', 20, 'application/pdf'),
        ])->assertForbidden();

        $this->actingAs($tutor)->post(route('convenios.validar_firma.store', $convenio->id), [
            'decision' => 'ok',
        ])->assertForbidden();

        $this->actingAs($direccion)->post(route('convenios.firmar_centro.store', $convenio->id), [
            'pdf' => UploadedFile::fake()->create('centro.pdf', 20, 'application/pdf'),
        ])->assertForbidden();

        $this->actingAs($secretaria)->post(route('convenios.generar_pdf.store', $convenio->id), [
            'pdf' => UploadedFile::fake()->create('inicial.pdf', 20, 'application/pdf'),
        ])->assertRedirect(route('convenios.show', $convenio->id));

        $this->actingAs($direccion)
            ->get(route('convenios.index'))
            ->assertOk()
            ->assertDontSee('Orden Correcto SL');

        $this->actingAs($direccion)->post(route('convenios.firmar_centro.store', $convenio->id), [
            'pdf' => UploadedFile::fake()->create('centro.pdf', 20, 'application/pdf'),
        ])->assertForbidden();
    }

    public function test_profesor_consulta_convenio_pero_no_descarga_documentos_sensibles(): void
    {
        Storage::fake('local');

        $departamentoId = \DB::table('departamentos')->insertGetId([
            'nombre' => 'Informatica',
            'familia_profesional' => 'Informatica',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $empresa = Empresa::create([
            'nombre_razon_social' => 'Solo Consulta SL',
            'dni_cif' => 'B33333333',
        ]);

        $profesor = User::factory()->create(['rol_id' => 5, 'departamento_id' => $departamentoId]);
        $tutor = User::factory()->create(['rol_id' => 4, 'departamento_id' => $departamentoId]);

        \DB::table('empresa_contacto_familia')->insert([
            'empresa_id' => $empresa->id,
            'departamento_id' => $departamentoId,
            'profesor_id' => $tutor->id,
            'created_at' => now(),
        ]);

        $convenio = Convenio::create([
            'empresa_id' => $empresa->id,
            'profesor_id' => $tutor->id,
            'estado' => 'pendiente_firma_empresa',
        ]);

        Storage::disk('local')->put('convenios/'.$convenio->id.'/inicial.pdf', 'pdf');

        $documento = DocumentoPdf::create([
            'convenio_id' => $convenio->id,
            'subido_por' => $tutor->id,
            'tipo' => 'provisional',
            'estado_doc' => 'valido',
            'ruta_archivo' => 'convenios/'.$convenio->id.'/inicial.pdf',
            'es_erroneo' => false,
        ]);

        $this->actingAs($profesor)
            ->get(route('convenios.show', $convenio->id))
            ->assertOk()
            ->assertSee('Solo Consulta SL');

        $this->actingAs($profesor)
            ->get(route('convenios.documentos.descargar', [$convenio->id, $documento->id]))
            ->assertForbidden();
    }
}
