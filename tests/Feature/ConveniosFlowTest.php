<?php

namespace Tests\Feature;

use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConveniosFlowTest extends TestCase
{
    use RefreshDatabase;

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

        $this->actingAs($secretaria)->post(route('convenios.generar_pdf.store', $convenio->id), [
            'pdf' => UploadedFile::fake()->create('inicial.pdf', 20, 'application/pdf'),
        ])->assertRedirect(route('convenios.show', $convenio->id));

        $this->assertSame('pendiente_firma_empresa', $convenio->fresh()->estado);
        $this->assertDatabaseHas('documentos_pdf', ['convenio_id' => $convenio->id, 'tipo' => 'provisional']);

        $this->actingAs($empresaUser)->post(route('convenios.firmar_empresa.store', $convenio->id), [
            'pdf' => UploadedFile::fake()->create('empresa.pdf', 20, 'application/pdf'),
        ])->assertRedirect(route('convenios.show', $convenio->id));

        $this->assertSame('pendiente_validacion_tutor', $convenio->fresh()->estado);
        $this->assertDatabaseHas('documentos_pdf', ['convenio_id' => $convenio->id, 'tipo' => 'firmado_empresa']);

        $this->actingAs($tutor)->post(route('convenios.validar_firma.store', $convenio->id), [
            'decision' => 'ok',
        ])->assertRedirect(route('convenios.show', $convenio->id));

        $this->assertSame('pendiente_firma_direccion', $convenio->fresh()->estado);

        $this->actingAs($direccion)->post(route('convenios.firmar_centro.store', $convenio->id), [
            'pdf' => UploadedFile::fake()->create('centro.pdf', 20, 'application/pdf'),
            'fecha_firma' => '2026-05-05',
        ])->assertRedirect(route('convenios.show', $convenio->id));

        $convenio->refresh();
        $this->assertSame('en_vigor', $convenio->estado);
        $this->assertSame('2026-05-05', $convenio->fecha_firma->toDateString());
        $this->assertDatabaseHas('documentos_pdf', ['convenio_id' => $convenio->id, 'tipo' => 'firmado_centro']);
    }
}
