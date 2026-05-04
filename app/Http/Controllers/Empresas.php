<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\EmpresaContactoFamilia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

		$categorias = Empresa::categoriaOptions();
		$tipos = Empresa::tipoOptions();

		return view('empresas', [
			'empresas' => $empresas,
			'categorias' => $categorias,
			'tipos' => $tipos,
			'filtros' => $request->only(['categoria', 'tipo', 'q']),
			'puede_crear' => $this->canCreateCompanies(Auth::user()),
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
}
