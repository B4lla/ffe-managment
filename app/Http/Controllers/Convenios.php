<?php

namespace App\Http\Controllers;

use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\Departamento;
use App\Models\Representante;
use App\Models\Tutor;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ConveniosImport;

class Convenios extends Controller
{

	public function importarConvenios(Request $request)
	{
		abort_unless($this->userCanCreateOrEdit(Auth::user()), 403);
		$request->validate([
			'csv_file' => 'required|file|mimes:xlsx,csv,txt',
		]);

		$file = $request->file('csv_file');
		$importer = new ConveniosImport();
		try {
			Excel::import($importer, $file);
			$msg = 'Convenios importados correctamente.';
			if (! empty($importer->imported)) {
				$msg .= ' Importados: '.count($importer->imported);
			}
			if (! empty($importer->errores)) {
				$msg .= ' Errores: '.implode(' | ', $importer->errores);
			}
		} catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
			$failures = $e->failures();
			$errors = [];
			foreach ($failures as $failure) {
				$errors[] = 'Fila '.($failure->row()).': '.implode(', ', $failure->errors());
			}
			$msg = 'Errores de validación: '.implode(' | ', $errors);
		} catch (\Throwable $e) {
			$msg = 'Error importando el archivo: '.$e->getMessage();
		}
		return redirect()->route('convenios.index')->with('status', $msg);
	}
	public function index(Request $request)
	{
	    $user = Auth::user();
	    $role = $this->currentRoleName($user);
	
	    $query = Convenio::with(['empresa.ultimoContactoFamilia.departamento', 'empresa.ultimoContactoFamilia.profesor'])
	        ->whereHas('empresa', function ($q) use ($request) {
	            if ($request->filled('categoria')) {
	                $q->where('categoria', (string) $request->input('categoria'));
	            }
	
	            if ($request->filled('tipo')) {
	                $q->where('tipo', (string) $request->input('tipo'));
	            }
	
	            if ($request->filled('q')) {
	                $q->searchByTerm($request->input('q'));
	            }
	        });

	    if ($request->filled('vigencia')) {
	        $this->applyVigenciaFilter($query, (string) $request->input('vigencia'));
	    }

	    if ($role === 'coordinador ffe' && $user?->departamento_id) {
	        $query->whereHas('empresa.contactosFamilia', function ($subQuery) use ($user) {
	            $subQuery->where('departamento_id', $user->departamento_id);
	        });
	    } elseif ($role === 'profesor tutor' && $user) {
	        $query->where('profesor_id', $user->id);
	    } elseif ($role === 'profesor' && $user) {
	        $query->where(function ($subQuery) use ($user) {
	            $subQuery->where('profesor_id', $user->id);
	            if ($user->departamento_id) {
	                $subQuery->orWhereHas('empresa.contactosFamilia', function ($contactos) use ($user) {
	                    $contactos->where('departamento_id', $user->departamento_id);
	                });
	            }
	        });
	    } elseif ($role === 'direccion') {
	        $query->whereIn('estado', ['pendiente_firma_direccion', 'firmado_empresa', 'en_vigor']);
	    } elseif ($role === 'empresa externa' && $user?->empresa_id) {
	        $query->where('empresa_id', $user->empresa_id);
	    }
	
	    $convenios = $query
	        ->orderByDesc('created_at')
	        ->paginate(15)
	        ->withQueryString();
	
	    $categorias = Empresa::categoriaOptions();
	    $tipos = Empresa::tipoOptions();
        $vigencias = Convenio::vigenciaOptions();
	
	    return view('convenios', [
	        'convenios' => $convenios,
	        'categorias' => $categorias,
	        'tipos' => $tipos,
	        'vigencias' => $vigencias,
	        'filtros' => $request->only(['categoria', 'tipo', 'q', 'vigencia']), 
	        'puede_crear' => $this->canCreateCompanies(Auth::user()),
	    ]);
	}

	public function destroy(int $id)
	{
		$convenio = Convenio::query()
			->with('empresa:id,nombre_razon_social')
			->findOrFail($id);

		DB::transaction(function () use ($convenio) {
			DB::table('documentos_pdf')->where('convenio_id', $convenio->id)->delete();
			DB::table('tareas_pendientes')->where('convenio_id', $convenio->id)->delete();
			DB::table('alumno_convenio')->where('convenio_id', $convenio->id)->delete();
			DB::table('convenio_tutor_empresa')->where('convenio_id', $convenio->id)->delete();
			DB::table('convenio_ciclo')->where('convenio_id', $convenio->id)->delete();

			$convenio->delete();
		});

		$empresaNombre = $convenio->empresa?->nombre_razon_social ?: 'sin empresa';

		return redirect()
			->route('convenios.index')
			->with('status', "Convenio eliminado correctamente ({$empresaNombre}).");
	}

	private function canCreateCompanies($user): bool
	{
		if (! $user) {
			return false;
		}

		if ($this->userHasRole($user, ['Administrador', 'Coordinador FFE', 'Profesor tutor', 'Secretaria'])) {
			return true;
		}

		return false;
	}



    public function create()
    {
		abort_unless($this->userCanCreateOrEdit(Auth::user()), 403);

		$departamentos = Departamento::all();
		$categorias = Empresa::categoriaOptions();
		$tipos = Empresa::tipoOptions();

		$empresas = Empresa::query()->orderBy('nombre_razon_social')->get(['id', 'nombre_razon_social']);

		$ciclos = DB::table('ciclos')
			->leftJoin('departamentos', 'ciclos.departamento_id', '=', 'departamentos.id')
			->orderBy('departamentos.nombre')
			->orderBy('ciclos.nombre')
			->get([
				'ciclos.id',
				'ciclos.nombre',
				'ciclos.grado',
				'departamentos.nombre as departamento_nombre',
			]);

		$profesores = User::query()
			->whereNotNull('departamento_id')
			->where('rol_id', 4) // rol 4 = Profesor tutor
			->get(['id', 'nombre', 'departamento_id']);

		$tutoresByDept = [];
		foreach ($profesores as $p) {
			$deptId = $p->departamento_id;
			if (! isset($tutoresByDept[$deptId])) {
				$tutoresByDept[$deptId] = [];
			}
			$tutoresByDept[$deptId][] = [
				'id' => $p->id,
				'name' => $p->nombre,
			];
		}

		return view('convenios.create', compact('departamentos', 'categorias', 'tipos', 'ciclos', 'tutoresByDept', 'empresas'));
    }

    public function store(Request $request)
    {
		abort_unless($this->userCanCreateOrEdit(Auth::user()), 403);

		$validated = $request->validate([
			'empresa_id' => 'nullable|exists:empresas,id',
			'responsable_nombre' => 'nullable|string|max:200',
			'responsable_telefono' => 'nullable|string|max:20',
			'responsable_email' => 'nullable|email|max:150',
			'departamento_id' => 'nullable|exists:departamentos,id',
			'tutor_id' => 'nullable|exists:usuarios,id',
			'tutor_telefono' => 'nullable|string|max:20',
			'tutor_email' => 'nullable|email|max:150',
			'empresa_nombre' => 'required_without:empresa_id|string|max:300',
			'empresa_dni_cif' => [
				'required_without:empresa_id',
				'string',
				'max:20',
				function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
					if ($request->filled('empresa_id')) {
						return;
					}

					$dniCifHash = Empresa::normalizeDniCif($value);

					if ($dniCifHash !== null && Empresa::query()->where('dni_cif_hash', hash('sha256', $dniCifHash))->exists()) {
						$fail('Ya existe una empresa con ese DNI/CIF. Selecciona la empresa existente.');
					}
				},
			],
			'empresa_actividad' => 'nullable|string|max:5000',
			'categoria' => 'nullable|in:ayuntamiento,colegios_institutos,empresa',
			'tipo' => 'nullable|in:verde,amarilla,roja',
			'domicilio_provincia' => 'nullable|string|max:100',
			'domicilio_municipio' => 'nullable|string|max:100',
			'domicilio_direccion' => 'nullable|string|max:5000',
			'domicilio_codigo_postal' => 'nullable|string|max:10',
			'contacto_telefono1' => 'nullable|string|max:20',
			'contacto_telefono2' => 'nullable|string|max:20',
			'contacto_email' => 'nullable|email|max:150',
			'representante_nif' => 'nullable|string|max:15',
			'representante_nombre' => 'nullable|string|max:150',
			'representante_apellido1' => 'nullable|string|max:100',
			'representante_apellido2' => 'nullable|string|max:100',
			'fecha_firma' => 'nullable|date',
			'estado' => 'nullable|string|max:50',
			'observaciones' => 'nullable|string',
			'direcciones' => 'nullable|array',
			'direcciones.*.provincia' => 'nullable|string|max:100',
			'direcciones.*.municipio' => 'nullable|string|max:100',
			'direcciones.*.direccion' => 'nullable|string|max:5000',
			'direcciones.*.codigo_postal' => 'nullable|string|max:10',
			'tutores' => 'nullable|array',
			'tutores.*.nombre_completo' => 'nullable|string|max:250',
			'tutores.*.dni' => 'nullable|string|max:15',
			'tutores.*.horarios' => 'nullable|array',
			'ciclo_ids' => 'nullable|array',
			'ciclo_ids.*' => 'exists:ciclos,id',
		]);

		$convenio = DB::transaction(function () use ($request, $validated) {
			if (! empty($validated['empresa_id'])) {
				$empresa = Empresa::find($validated['empresa_id']);
			} else {
				$empresa = Empresa::create([
					'nombre_razon_social' => $validated['empresa_nombre'],
					'dni_cif' => $validated['empresa_dni_cif'],
					'actividad' => $validated['empresa_actividad'] ?? null,
					'categoria' => $validated['categoria'] ?? null,
					'tipo' => $validated['tipo'] ?? null,
					'email' => $validated['contacto_email'] ?? null,
					'telefono1' => $validated['contacto_telefono1'] ?? null,
					'telefono2' => $validated['contacto_telefono2'] ?? null,
					'provincia' => $validated['domicilio_provincia'] ?? null,
					'municipio' => $validated['domicilio_municipio'] ?? null,
					'direccion' => $validated['domicilio_direccion'] ?? null,
					'codigo_postal' => $validated['domicilio_codigo_postal'] ?? null,
				]);
			}

			$representanteId = null;
			if (! empty($validated['representante_nif']) || ! empty($validated['representante_nombre']) || ! empty($validated['representante_apellido1']) || ! empty($validated['representante_apellido2'])) {
				$representante = Representante::create([
					'empresa_id' => $empresa->id,
					'nif' => $validated['representante_nif'] ?? null,
					'nombre' => $validated['representante_nombre'] ?? null,
					'apellido1' => $validated['representante_apellido1'] ?? null,
					'apellido2' => $validated['representante_apellido2'] ?? null,
				]);
				$representanteId = $representante->id;
			}

			$profesor = null;
			if (! empty($validated['tutor_id'])) {
				$profesor = User::query()->with('departamento')->find($validated['tutor_id']);
			}

			$horariosResumen = $this->buildHorarioResumen($validated['tutores'] ?? []);

			$convenio = Convenio::create([
				'empresa_id' => $empresa->id,
				'profesor_id' => $validated['tutor_id'] ?? null,
				'representante_id' => $representanteId,
				'resp_gestion_nombre' => $validated['responsable_nombre'] ?? null,
				'resp_gestion_telefono' => $validated['responsable_telefono'] ?? null,
				'resp_gestion_email' => $validated['responsable_email'] ?? null,
				'resp_ies_nombre' => $profesor?->nombre ?? null,
				'resp_ies_telefono' => $validated['tutor_telefono'] ?? null,
				'resp_ies_email' => $validated['tutor_email'] ?? null,
				'fecha_firma' => $validated['fecha_firma'] ?? null,
				'estado' => $validated['estado'] ?? 'borrador',
				'horario_practicas' => $horariosResumen,
				'observaciones' => $validated['observaciones'] ?? null,
			]);

			if (! empty($validated['departamento_id'])) {
				DB::table('empresa_contacto_familia')->insert([
					'empresa_id' => $empresa->id,
					'departamento_id' => $validated['departamento_id'],
					'profesor_id' => $validated['tutor_id'] ?? null,
					'created_at' => now(),
				]);
			}

			foreach (($validated['direcciones'] ?? []) as $direccion) {
				if (empty(array_filter($direccion ?? []))) {
					continue;
				}

				DB::table('centros_trabajo')->insert([
					'empresa_id' => $empresa->id,
					'direccion' => $this->encryptNullable($direccion['direccion'] ?? null),
					'municipio' => $this->encryptNullable($direccion['municipio'] ?? null),
					'provincia' => $this->encryptNullable($direccion['provincia'] ?? null),
					'codigo_postal' => $this->encryptNullable($direccion['codigo_postal'] ?? null),
					'created_at' => now(),
					'updated_at' => now(),
				]);
			}

			foreach (($validated['tutores'] ?? []) as $tutorData) {
				if (empty($tutorData['nombre_completo']) && empty($tutorData['dni'])) {
					continue;
				}

				$tutorEmpresa = Tutor::create([
					'empresa_id' => $empresa->id,
					'nombre_completo' => $tutorData['nombre_completo'] ?? '',
					'dni' => $tutorData['dni'] ?? null,
					'email' => $tutorData['email'] ?? null,
					'telefono' => $tutorData['telefono'] ?? null,
				]);

				DB::table('convenio_tutor_empresa')->insert([
					'convenio_id' => $convenio->id,
					'tutor_empresa_id' => $tutorEmpresa->id,
					'created_at' => now(),
				]);

				foreach (array_values(array_unique($tutorData['horarios'] ?? [])) as $slotNumero) {
					DB::table('horarios_practicas')->insert([
						'tutor_empresa_id' => $tutorEmpresa->id,
						'slot_numero' => (int) $slotNumero,
						'horario' => 'Horario '.$slotNumero,
						'created_at' => now(),
					]);
				}
			}

			foreach (array_values(array_unique($validated['ciclo_ids'] ?? [])) as $cicloId) {
				DB::table('convenio_ciclo')->insert([
					'convenio_id' => $convenio->id,
					'ciclo_id' => (int) $cicloId,
					'plazas' => 1,
					'created_at' => now(),
				]);
			}

			return $convenio;
		});

		return redirect()->route('convenios.show', $convenio->id)->with('success', 'Convenio creado correctamente con sus datos relacionados.');
    }

	private function buildHorarioResumen(array $tutores): ?string
	{
		$slots = [];

		foreach ($tutores as $tutorData) {
			foreach (($tutorData['horarios'] ?? []) as $slotNumero) {
				$slots[] = (string) $slotNumero;
			}
		}

		$slots = array_values(array_unique(array_filter($slots)));

		return $slots === [] ? null : implode(', ', $slots);
	}

    private function applyVigenciaFilter($query, string $vigencia): void
    {
        $today = Carbon::today();
        $caducadoDesde = $today->copy()->subYears(4);
        $caducaEnTresMesesDesde = $today->copy()->subYears(4)->addDay();
        $caducaEnTresMesesHasta = $today->copy()->subYears(3)->subMonths(9);
        $caducaEnSeisMesesDesde = $today->copy()->subYears(3)->subMonths(9)->addDay();
        $caducaEnSeisMesesHasta = $today->copy()->subYears(3)->subMonths(6);
        $caducaEnUnAnoDesde = $today->copy()->subYears(3)->subMonths(6)->addDay();
        $caducaEnUnAnoHasta = $today->copy()->subYears(3);
        $enVigorDesde = $today->copy()->subYears(4)->addDay();

        match ($vigencia) {
            'caducado' => $query->whereDate('fecha_firma', '<=', $caducadoDesde),
            'caduca_3_meses' => $query->whereBetween('fecha_firma', [$caducaEnTresMesesDesde, $caducaEnTresMesesHasta]),
            'caduca_6_meses' => $query->whereBetween('fecha_firma', [$caducaEnSeisMesesDesde, $caducaEnSeisMesesHasta]),
            'caduca_1_ano' => $query->whereBetween('fecha_firma', [$caducaEnUnAnoDesde, $caducaEnUnAnoHasta]),
            'en_vigor' => $query->whereDate('fecha_firma', '>=', $enVigorDesde),
            'sin_fecha_firma' => $query->whereNull('fecha_firma'),
            default => null,
        };
    }

	private function currentRoleName($user): string
	{
		if (! $user) {
			return '';
		}

		if (! $user->relationLoaded('rol')) {
			$user->load('rol');
		}

		return strtolower(trim((string) optional($user->rol)->nombre));
	}

	private function userCanCreateOrEdit($user): bool
	{
		return in_array($this->currentRoleName($user), ['administrador', 'coordinador ffe', 'profesor tutor', 'secretaria'], true);
	}

	private function encryptNullable($value): ?string
	{
		return $value === null || $value === '' ? null : Crypt::encryptString($value);
	}
}
