<?php

namespace Tests\Feature;

use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\TareaPendiente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresasPendingTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_empresa_externa_ve_sus_tareas_pendientes_en_vista_empresa(): void
    {
        $empresa = Empresa::create([
            'nombre_razon_social' => 'Empresa Tareas SL',
            'dni_cif' => 'B44444444',
        ]);

        $empresaUser = User::factory()->create([
            'rol_id' => 7,
            'empresa_id' => $empresa->id,
        ]);

        $convenio = Convenio::create([
            'empresa_id' => $empresa->id,
            'estado' => 'pendiente_firma_empresa',
        ]);

        TareaPendiente::create([
            'convenio_id' => $convenio->id,
            'usuario_id' => $empresaUser->id,
            'tipo_tarea' => 'Firmar empresa',
            'descripcion' => 'Descarga el PDF inicial, firmalo y vuelve a subirlo.',
            'completada' => false,
        ]);

        $this->actingAs($empresaUser)
            ->get(route('empresas.index'))
            ->assertOk()
            ->assertSee('Tareas pendientes')
            ->assertSee('1');

        $this->actingAs($empresaUser)
            ->get(route('empresas.show', $empresa->id))
            ->assertOk()
            ->assertSee('Tareas pendientes')
            ->assertSee('Firmar empresa')
            ->assertSee('Descarga el PDF inicial')
            ->assertSee('Firmar por la empresa');
    }
}
