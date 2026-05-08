<?php

namespace App\Http\Controllers;

use App\Exports\InformesExport;
use App\Models\Departamento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class InformesController extends Controller
{
    public function index(Request $request)
    {
        abort_if($this->userHasRole($request->user(), 'Empresa externa'), 403);

        [$filters, $report] = $this->buildReport($request);

        return view('informes', [
            'departamentos' => Departamento::query()->orderBy('nombre')->get(['id', 'nombre']),
            'reportTypes' => $this->reportTypes(),
            'filters' => $filters,
            'report' => $report,
        ]);
    }

    public function export(Request $request)
    {
        abort_if($this->userHasRole($request->user(), 'Empresa externa'), 403);

        [$filters, $report] = $this->buildReport($request);

        abort_if($report['rows'] === [], 422, 'No hay datos para exportar con los filtros seleccionados.');

        $filename = 'informe_'.Str::slug($report['type']).'_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new InformesExport($report, $filters), $filename);
    }

    private function buildReport(Request $request): array
    {
        $filters = $request->validate([
            'report_type' => 'nullable|in:global_familia,ciclos_familia,curso_familia',
            'departamento_id' => 'nullable|integer|exists:departamentos,id',
            'ciclo_ids' => 'nullable|array',
            'ciclo_ids.*' => 'integer|exists:ciclos,id',
            'curso_anio' => 'nullable|integer|min:1',
        ]);

        $filters['report_type'] = $filters['report_type'] ?? 'global_familia';
        $filters['ciclo_ids'] = array_values(array_unique(array_map('intval', $filters['ciclo_ids'] ?? [])));

        $department = null;
        $familyCycles = collect();
        $courseYears = collect();
        $report = [
            'type' => $filters['report_type'],
            'title' => 'Informes',
            'subtitle' => null,
            'columns' => [],
            'rows' => [],
            'message' => 'Selecciona una familia para generar el informe.',
        ];

        if (! empty($filters['departamento_id'])) {
            $department = Departamento::query()->find($filters['departamento_id']);
            $familyCycles = DB::table('ciclos')
                ->where('departamento_id', $department->id)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'grado']);

            $courseYears = DB::table('cursos')
                ->join('ciclos', 'cursos.ciclo_id', '=', 'ciclos.id')
                ->where('ciclos.departamento_id', $department->id)
                ->distinct()
                ->orderBy('cursos.anio')
                ->pluck('cursos.anio');

            if ($courseYears->isEmpty()) {
                $courseYears = collect([1, 2]);
            }

            $report = match ($filters['report_type']) {
                'ciclos_familia' => $this->buildCyclesReport($department, $familyCycles, $filters),
                'curso_familia' => $this->buildCourseReport($department, $familyCycles, $filters),
                default => $this->buildGlobalFamilyReport($department, $familyCycles),
            };
        }

        $filters['available_cycles'] = $familyCycles->map(fn ($cycle) => [
            'id' => (int) $cycle->id,
            'label' => $cycle->nombre,
        ])->all();
        $filters['available_course_years'] = $courseYears->all();

        return [$filters, $report];
    }

    private function buildGlobalFamilyReport($department, Collection $cycles): array
    {
        if ($cycles->isEmpty()) {
            return [
                'type' => 'global_familia',
                'title' => 'Vista global de empresas por familia',
                'subtitle' => $department->nombre,
                'columns' => [],
                'rows' => [],
                'message' => 'La familia seleccionada no tiene ciclos configurados.',
            ];
        }

        $rows = $this->buildConvenioCycleRows($department->id, $cycles->pluck('id')->all());

        return [
            'type' => 'global_familia',
            'title' => 'Vista global de empresas por familia',
            'subtitle' => $department->nombre,
            'columns' => $this->formatColumns($cycles),
            'rows' => $rows,
            'message' => $rows === [] ? 'No hay empresas con convenios vinculados a esta familia.' : null,
        ];
    }

    private function buildCyclesReport($department, Collection $familyCycles, array $filters): array
    {
        $cycles = $familyCycles->whereIn('id', $filters['ciclo_ids'] ?? [])->values();

        if ($cycles->isEmpty()) {
            return [
                'type' => 'ciclos_familia',
                'title' => 'Vista de empresas por ciclos de una familia',
                'subtitle' => $department->nombre,
                'columns' => [],
                'rows' => [],
                'message' => 'Selecciona uno o varios ciclos de la familia.',
            ];
        }

        $rows = $this->buildConvenioCycleRows($department->id, $cycles->pluck('id')->all());

        return [
            'type' => 'ciclos_familia',
            'title' => 'Vista de empresas por ciclos de una familia',
            'subtitle' => $department->nombre.' · '.implode(', ', $cycles->pluck('nombre')->all()),
            'columns' => $this->formatColumns($cycles),
            'rows' => $rows,
            'message' => $rows === [] ? 'No hay empresas con convenios en los ciclos seleccionados.' : null,
        ];
    }

    private function buildCourseReport($department, Collection $cycles, array $filters): array
    {
        if ($cycles->isEmpty()) {
            return [
                'type' => 'curso_familia',
                'title' => 'Vista de empresas por curso de una familia',
                'subtitle' => $department->nombre,
                'columns' => [],
                'rows' => [],
                'message' => 'La familia seleccionada no tiene ciclos configurados.',
            ];
        }

        if (empty($filters['curso_anio'])) {
            return [
                'type' => 'curso_familia',
                'title' => 'Vista de empresas por curso de una familia',
                'subtitle' => $department->nombre,
                'columns' => $this->formatColumns($cycles),
                'rows' => [],
                'message' => 'Selecciona primero o segundo curso para generar el informe.',
            ];
        }

        $courseCycles = $this->cyclesForCourse($cycles, (int) $filters['curso_anio']);
        $rows = $this->buildConvenioCycleRows($department->id, $courseCycles->pluck('id')->all());

        return [
            'type' => 'curso_familia',
            'title' => 'Vista de empresas por curso de una familia',
            'subtitle' => $department->nombre.' · Curso '.$filters['curso_anio'],
            'columns' => $this->formatColumns($courseCycles),
            'rows' => $rows,
            'message' => $rows === [] ? 'No hay empresas con convenios vinculados a ese curso.' : null,
        ];
    }

    private function buildConvenioCycleRows(int $departmentId, array $cycleIds): array
    {
        $rawRows = DB::table('convenios as c')
            ->join('empresas as e', 'e.id', '=', 'c.empresa_id')
            ->join('convenio_ciclo as cc', 'cc.convenio_id', '=', 'c.id')
            ->join('ciclos as ci', 'ci.id', '=', 'cc.ciclo_id')
            ->where('ci.departamento_id', $departmentId)
            ->whereIn('cc.ciclo_id', $cycleIds)
            ->select([
                'e.id as empresa_id',
                'e.nombre_razon_social',
                'c.id as convenio_id',
                'c.profesor_id',
                'c.observaciones',
                'cc.ciclo_id',
                'cc.plazas',
            ])
            ->orderBy('e.nombre_razon_social')
            ->get();

        return $this->aggregateRows($rawRows, $cycleIds, $departmentId);
    }

    private function cyclesForCourse(Collection $cycles, int $courseYear): Collection
    {
        $cycleIds = $cycles->pluck('id')->map(fn ($id) => (int) $id)->all();

        $courseCycleIds = DB::table('cursos')
            ->where('anio', $courseYear)
            ->whereIn('ciclo_id', $cycleIds)
            ->pluck('ciclo_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($courseCycleIds === []) {
            return $cycles;
        }

        return $cycles->whereIn('id', $courseCycleIds)->values();
    }

    private function aggregateRows(Collection $rawRows, array $cycleIds, int $departmentId): array
    {
        if ($rawRows->isEmpty()) {
            return [];
        }

        $contactProfessorIds = DB::table('empresa_contacto_familia')
            ->where('departamento_id', $departmentId)
            ->pluck('profesor_id', 'empresa_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $userIds = $rawRows->pluck('profesor_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->merge(array_values($contactProfessorIds))
            ->unique()
            ->values();

        $userNames = User::query()
            ->whereIn('id', $userIds->all())
            ->get(['id', 'nombre'])
            ->mapWithKeys(fn (User $user) => [$user->id => $user->nombre])
            ->all();

        $rows = [];

        foreach ($rawRows as $item) {
            $empresaId = (int) $item->empresa_id;

            if (! isset($rows[$empresaId])) {
                $rows[$empresaId] = [
                    'empresa' => $item->nombre_razon_social,
                    'tutor_centro' => [],
                    'cycle_values' => array_fill_keys($cycleIds, 0),
                    'observaciones' => [],
                ];
            }

            $profesorId = $item->profesor_id ? (int) $item->profesor_id : null;
            if ($profesorId && isset($userNames[$profesorId])) {
                $rows[$empresaId]['tutor_centro'][$profesorId] = $userNames[$profesorId];
            }

            $cycleId = (int) $item->ciclo_id;
            if (array_key_exists($cycleId, $rows[$empresaId]['cycle_values'])) {
                $rows[$empresaId]['cycle_values'][$cycleId] += (int) ($item->plazas ?? 0);
            }

            $observacion = trim((string) ($item->observaciones ?? ''));
            if ($observacion !== '') {
                $rows[$empresaId]['observaciones'][$observacion] = $observacion;
            }
        }

        foreach ($rows as $empresaId => &$row) {
            if ($row['tutor_centro'] === [] && isset($contactProfessorIds[$empresaId], $userNames[$contactProfessorIds[$empresaId]])) {
                $row['tutor_centro'][$contactProfessorIds[$empresaId]] = $userNames[$contactProfessorIds[$empresaId]];
            }

            $row['tutor_centro'] = $row['tutor_centro'] === [] ? '-' : implode(' / ', array_values($row['tutor_centro']));
            $row['observaciones'] = $row['observaciones'] === [] ? '-' : implode(' | ', array_values($row['observaciones']));
            $row['cells'] = [];

            foreach ($cycleIds as $cycleId) {
                $value = $row['cycle_values'][$cycleId] ?? 0;
                $row['cells'][$cycleId] = $value > 0 ? (string) $value : '';
            }

            unset($row['cycle_values']);
        }
        unset($row);

        uasort($rows, fn (array $a, array $b): int => strcmp($a['empresa'], $b['empresa']));

        return array_values($rows);
    }

    private function formatColumns(Collection $cycles): array
    {
        return $cycles->map(fn ($cycle) => [
            'id' => (int) $cycle->id,
            'label' => $cycle->nombre,
        ])->all();
    }

    private function reportTypes(): array
    {
        return [
            'global_familia' => 'Vista global por familia',
            'ciclos_familia' => 'Vista por ciclos de una familia',
            'curso_familia' => 'Vista por primer o segundo curso',
        ];
    }
}
