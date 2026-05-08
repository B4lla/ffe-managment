<?php

namespace Tests\Feature;

use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InformesTest extends TestCase
{
    use RefreshDatabase;

    public function test_informe_por_curso_usa_convenios_y_plazas(): void
    {
        $departamentoId = DB::table('departamentos')->insertGetId([
            'nombre' => 'Informatica',
            'familia_profesional' => 'Informatica',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cicloId = DB::table('ciclos')->insertGetId([
            'nombre' => 'DAM',
            'grado' => 'GS',
            'departamento_id' => $departamentoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cursos')->insert([
            'anio' => 1,
            'ciclo_id' => $cicloId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $empresa = Empresa::create([
            'nombre_razon_social' => 'Empresa Curso SL',
            'dni_cif' => 'B76543210',
        ]);

        $profesor = User::factory()->create([
            'rol_id' => 4,
            'departamento_id' => $departamentoId,
        ]);

        $admin = User::factory()->create(['rol_id' => 1]);

        $convenio = Convenio::create([
            'empresa_id' => $empresa->id,
            'profesor_id' => $profesor->id,
            'estado' => 'en_vigor',
            'observaciones' => 'Plazas desde convenio',
        ]);

        DB::table('convenio_ciclo')->insert([
            'convenio_id' => $convenio->id,
            'ciclo_id' => $cicloId,
            'plazas' => 3,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('informes.index', [
            'report_type' => 'curso_familia',
            'departamento_id' => $departamentoId,
            'curso_anio' => 1,
        ]));

        $response
            ->assertOk()
            ->assertViewHas('report', function (array $report) use ($cicloId): bool {
                return $report['rows'] !== []
                    && $report['rows'][0]['empresa'] === 'Empresa Curso SL'
                    && $report['rows'][0]['cells'][$cicloId] === '3';
            });
    }
}
