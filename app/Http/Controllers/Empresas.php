<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\EmpresaContactoFamilia;
use App\Models\RegistroContacto;
use App\Models\TareaPendiente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Convenio;

class Empresas extends Controller
{
	public function index(Request $request)
	{
		$user = Auth::user();
		$role = $this->currentRoleName($user);

		$query = Empresa::with(['ultimoContactoFamilia.departamento', 'ultimoContactoFamilia.profesor']);

		if ($request->filled('categoria')) {
			$query->where('categoria', (string) $request->input('categoria'));
		}

		if ($request->filled('tipo')) {
			$query->where('tipo', (string) $request->input('tipo'));
		}

		if ($request->filled('q')) {
			$query->searchByTerm($request->input('q'));
		}

		if ($role === 'empresa externa' && $user?->empresa_id) {
			$query->where('id', $user->empresa_id);
		}

		$empresas = $query
			->orderByDesc('created_at')
			->paginate(15)
			->withQueryString();

		$tareasPendientesPorEmpresa = $this->pendingTaskCountsByEmpresa(
			$empresas->getCollection()->pluck('id')->all(),
			$user,
			$role
		);

		$categorias = Empresa::categoriaOptions();
		$tipos = Empresa::tipoOptions();

		return view('empresas', [
			'empresas' => $empresas,
			'categorias' => $categorias,
			'tipos' => $tipos,
			'filtros' => $request->only(['categoria', 'tipo', 'q']),
			'puede_crear' => $this->canCreateCompanies(Auth::user()),
			'tareasPendientesPorEmpresa' => $tareasPendientesPorEmpresa,
		]);
	}

	public function create()
	{
		abort_unless($this->canCreateCompanies(Auth::user()), 403);

		$departamentos = Departamento::orderBy('nombre')->get();
		$profesores = User::query()
			->whereIn('rol_id', [3, 4])
			->orderBy('nombre')
			->get(['id', 'nombre', 'departamento_id']);

		return view('empresas.create', [
			'categorias' => Empresa::categoriaOptions(),
			'tipos' => Empresa::tipoOptions(),
			'departamentos' => $departamentos,
			'profesores' => $profesores,
		]);
	}

	public function store(Request $request)
	{
		abort_unless($this->canCreateCompanies(Auth::user()), 403);

		$validated = $request->validate([
			'nombre_razon_social' => ['required', 'string', 'max:300'],
			'dni_cif' => [
				'required',
				'string',
				'max:20',
				function (string $attribute, mixed $value, \Closure $fail): void {
					$dniCifHash = Empresa::normalizeDniCif($value);

					if ($dniCifHash !== null && Empresa::query()->where('dni_cif_hash', hash('sha256', $dniCifHash))->exists()) {
						$fail('Ya existe una empresa con ese DNI/CIF.');
					}
				},
			],
			'actividad' => ['nullable', 'string'],
			'categoria' => ['nullable', 'in:ayuntamiento,colegios_institutos,empresa'],
			'tipo' => ['nullable', 'in:verde,amarilla,roja'],
			'email' => ['nullable', 'string', 'email', 'max:150'],
			'telefono1' => ['nullable', 'string', 'max:20'],
			'telefono2' => ['nullable', 'string', 'max:20'],
			'provincia' => ['nullable', 'string', 'max:100'],
			'municipio' => ['nullable', 'string', 'max:100'],
			'direccion' => ['nullable', 'string'],
			'codigo_postal' => ['nullable', 'string', 'max:10'],
			'departamento_id' => ['nullable', 'exists:departamentos,id'],
			'profesor_id' => ['nullable', 'exists:usuarios,id'],
		]);

		$empresa = Empresa::create($validated);

		if ($request->filled('departamento_id')) {
			EmpresaContactoFamilia::create([
				'empresa_id' => $empresa->id,
				'departamento_id' => $validated['departamento_id'],
				'profesor_id' => $validated['profesor_id'] ?? null,
			]);
		}

		return redirect()
			->route('empresas.index')
			->with('status', 'Empresa creada correctamente.');
	}

	public function contactosIndex(Request $request, Empresa $empresa)
	{
		$contactos = RegistroContacto::query()
			->with('profesor:id,nombre')
			->where('empresa_id', $empresa->id)
			->orderByDesc('fecha_contacto')
			->paginate(15)
			->withQueryString();

		return view('empresas.contactos', [
			'empresa' => $empresa,
			'contactos' => $contactos,
		]);
	}

	public function contactosStore(Request $request, Empresa $empresa)
	{
		$validated = $request->validate([
			'resultado' => ['nullable', 'string', 'max:100'],
			'observaciones' => ['nullable', 'string'],
		]);

		RegistroContacto::create([
			'empresa_id' => $empresa->id,
			'profesor_id' => (int) $request->user()->id,
			'resultado' => $validated['resultado'] ?? null,
			'observaciones' => $validated['observaciones'] ?? null,
			'fecha_contacto' => now(),
		]);

		return redirect()
			->route('empresas.contactos.index', $empresa->id)
			->with('status', 'Contacto guardado correctamente.');
	}

	public function show(Empresa $empresa)
	{
		$user = Auth::user();
		$role = $this->currentRoleName($user);
		$empresa->load(['ultimoContactoFamilia.departamento', 'ultimoContactoFamilia.profesor']);
		$tareasPendientes = $this->pendingTasksForEmpresa($empresa, $user, $role);

		return view('empresas.show', [
			'empresa' => $empresa,
			'puede_crear' => $this->canCreateCompanies($user),
			'tareasPendientes' => $tareasPendientes,
			'esEmpresaExterna' => $role === 'empresa externa',
		]);
	}

	public function edit(Empresa $empresa)
	{
		abort_unless($this->canCreateCompanies(Auth::user()), 403);

		$departamentos = Departamento::orderBy('nombre')->get();
		$profesores = User::query()
			->whereIn('rol_id', [3, 4])
			->orderBy('nombre')
			->get(['id', 'nombre', 'departamento_id']);

		return view('empresas.edit', [
			'empresa' => $empresa,
			'categorias' => Empresa::categoriaOptions(),
			'tipos' => Empresa::tipoOptions(),
			'departamentos' => $departamentos,
			'profesores' => $profesores,
		]);
	}

	public function update(Request $request, Empresa $empresa)
	{
		abort_unless($this->canCreateCompanies(Auth::user()), 403);

		$validated = $request->validate([
			'nombre_razon_social' => ['required', 'string', 'max:300'],
			'dni_cif' => ['required', 'string', 'max:20'],
			'actividad' => ['nullable', 'string'],
			'categoria' => ['nullable', 'in:ayuntamiento,colegios_institutos,empresa'],
			'tipo' => ['nullable', 'in:verde,amarilla,roja'],
			'email' => ['nullable', 'string', 'email', 'max:150'],
			'telefono1' => ['nullable', 'string', 'max:20'],
			'telefono2' => ['nullable', 'string', 'max:20'],
			'provincia' => ['nullable', 'string', 'max:100'],
			'municipio' => ['nullable', 'string', 'max:100'],
			'direccion' => ['nullable', 'string'],
			'codigo_postal' => ['nullable', 'string', 'max:10'],
			'departamento_id' => ['nullable', 'exists:departamentos,id'],
			'profesor_id' => ['nullable', 'exists:usuarios,id'],
		]);

		$empresa->update($validated);

		if ($request->filled('departamento_id')) {
			EmpresaContactoFamilia::updateOrCreate(
				['empresa_id' => $empresa->id, 'departamento_id' => $validated['departamento_id']],
				['profesor_id' => $validated['profesor_id'] ?? null]
			);
		}

		return redirect()->route('empresas.show', $empresa->id)->with('status', 'Empresa actualizada correctamente.');
	}

	public function destroy(Empresa $empresa)
	{
		abort_unless($this->canCreateCompanies(Auth::user()), 403);

		$empresa->delete();

		return redirect()->route('empresas.index')->with('status', 'Empresa eliminada.');
	}

	public function export(Request $request): StreamedResponse
	{
		abort_unless($this->canCreateCompanies(Auth::user()), 403);

		$csvHeaders = [
			'Content-Type' => 'text/csv',
			'Content-Disposition' => 'attachment; filename="empresas.csv"',
		];

		$callback = function () {
			$out = fopen('php://output', 'w');
			fputcsv($out, ['ID', 'Nombre', 'DNI_CIF', 'Email', 'Telefono1', 'Provincia', 'Municipio']);
			Empresa::query()->orderBy('id')->chunk(200, function ($empresas) use ($out) {
				foreach ($empresas as $e) {
					fputcsv($out, [$e->id, $e->nombre_razon_social, $e->dni_cif, $e->email, $e->telefono1, $e->provincia, $e->municipio]);
				}
			});
			fclose($out);
		};

		return response()->stream($callback, 200, $csvHeaders);
	}

	public function search(Request $request)
	{
		$request->validate([
			'q' => ['required', 'string', 'min:2'],
		]);

		$q = trim((string) $request->input('q'));

		$empresas = Empresa::query()
			->where(function ($builder) use ($q) {
				$builder->where('nombre_razon_social', 'like', "%{$q}%")
					->orWhere('dni_cif', 'like', "%{$q}%")
					->orWhere('email', 'like', "%{$q}%")
					->orWhere('telefono1', 'like', "%{$q}%")
					->orWhere('telefono2', 'like', "%{$q}%");
			})
			->limit(15)
			->get(['id', 'nombre_razon_social', 'dni_cif', 'email', 'telefono1', 'provincia', 'municipio']);

		$convenios = Convenio::query()
			->with('empresa')
			->whereHas('empresa', function ($builder) use ($q) {
				$builder->where('nombre_razon_social', 'like', "%{$q}%")
					->orWhere('dni_cif', 'like', "%{$q}%")
					->orWhere('email', 'like', "%{$q}%")
					->orWhere('telefono1', 'like', "%{$q}%")
					->orWhere('telefono2', 'like', "%{$q}%");
			})
			->limit(15)
			->get();

		$conveniosTransformed = $convenios->map(function ($c) {
			return [
				'id' => $c->id,
				'empresa_id' => $c->empresa_id,
				'empresa_nombre' => $c->empresa?->nombre_razon_social,
				'estado' => $c->estado,
				'fecha' => optional($c->created_at)->toDateString(),
			];
		});

		return response()->json([
			'empresas' => $empresas,
			'convenios' => $conveniosTransformed,
		]);
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

	private function pendingTaskCountsByEmpresa(array $empresaIds, $user, string $role): array
	{
		if ($empresaIds === []) {
			return [];
		}

		$query = TareaPendiente::query()
			->join('convenios', 'convenios.id', '=', 'tareas_pendientes.convenio_id')
			->where('tareas_pendientes.completada', false)
			->whereIn('convenios.empresa_id', $empresaIds)
			->groupBy('convenios.empresa_id')
			->select('convenios.empresa_id', DB::raw('COUNT(*) as total'));

		if ($role === 'empresa externa') {
			$query->where('tareas_pendientes.usuario_id', $user?->id);
		}

		return $query
			->pluck('total', 'empresa_id')
			->map(fn ($total) => (int) $total)
			->all();
	}

	private function pendingTasksForEmpresa(Empresa $empresa, $user, string $role)
	{
		$query = TareaPendiente::query()
			->with(['usuario:id,nombre', 'convenio:id,empresa_id,estado'])
			->where('completada', false)
			->whereHas('convenio', fn ($convenio) => $convenio->where('empresa_id', $empresa->id))
			->orderByDesc('created_at');

		if ($role === 'empresa externa') {
			$query->where('usuario_id', $user?->id);
		}

		return $query
			->get()
			->map(fn (TareaPendiente $tarea) => $this->decoratePendingTask($tarea));
	}

	private function decoratePendingTask(TareaPendiente $tarea): TareaPendiente
	{
		$tipo = strtolower(trim((string) $tarea->tipo_tarea));
		$convenioId = $tarea->convenio_id;

		$tarea->action_label = 'Abrir convenio';
		$tarea->action_url = $convenioId ? route('convenios.show', $convenioId) : null;

		$matches = [
			'firmar centro' => ['convenios.firmar_centro', 'Firmar por el centro'],
			'firma centro' => ['convenios.firmar_centro', 'Firmar por el centro'],
			'firmar empresa' => ['convenios.firmar_empresa', 'Firmar por la empresa'],
			'firma empresa' => ['convenios.firmar_empresa', 'Firmar por la empresa'],
			'validar firma' => ['convenios.validar_firma', 'Validar firma'],
			'validar' => ['convenios.validar_firma', 'Validar firma'],
			'generar pdf' => ['convenios.generar_pdf', 'Generar PDF'],
			'pdf' => ['convenios.generar_pdf', 'Generar PDF'],
			'descargar convenio firmado' => ['convenios.descargar_firmado', 'Descargar convenio firmado'],
			'descargar firmado' => ['convenios.descargar_firmado', 'Descargar convenio firmado'],
		];

		foreach ($matches as $needle => [$routeName, $label]) {
			if ($tipo !== '' && str_contains($tipo, $needle) && $convenioId) {
				$tarea->action_label = $label;
				$tarea->action_url = route($routeName, $convenioId);
				break;
			}
		}

		return $tarea;
	}
}
