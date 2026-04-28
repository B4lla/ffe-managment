<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Empresas extends Controller
{
	public function index(Request $request)
	{
		$query = Empresa::query();

		if ($request->filled('categoria')) {
			$query->where('categoria', (string) $request->input('categoria'));
		}

		if ($request->filled('tipo')) {
			$query->where('tipo', (string) $request->input('tipo'));
		}

		if ($request->filled('q')) {
			$term = trim((string) $request->input('q'));
			$query->where(function ($subQuery) use ($term) {
				$subQuery->where('nombre_razon_social', 'like', "%{$term}%")
					->orWhere('dni_cif', 'like', "%{$term}%")
					->orWhere('email', 'like', "%{$term}%")
					->orWhere('telefono1', 'like', "%{$term}%")
					->orWhere('telefono2', 'like', "%{$term}%")
					->orWhere('municipio', 'like', "%{$term}%")
					->orWhere('provincia', 'like', "%{$term}%");
			});
		}

		$empresas = $query
			->orderByDesc('created_at')
			->paginate(15)
			->withQueryString();

		$categorias = Empresa::query()
			->whereNotNull('categoria')
			->where('categoria', '!=', '')
			->distinct()
			->orderBy('categoria')
			->pluck('categoria');

		$tipos = Empresa::query()
			->whereNotNull('tipo')
			->where('tipo', '!=', '')
			->distinct()
			->orderBy('tipo')
			->pluck('tipo');

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

		return view('empresas.create');
	}

	public function store(Request $request)
	{
		abort_unless($this->canCreateCompanies(Auth::user()), 403);

		$validated = $request->validate([
			'nombre_razon_social' => ['required', 'string', 'max:300'],
			'dni_cif' => ['required', 'string', 'max:20', 'unique:empresas,dni_cif'],
			'actividad' => ['nullable', 'string'],
			'categoria' => ['nullable', 'string', 'max:50'],
			'tipo' => ['nullable', 'string', 'max:50'],
			'email' => ['nullable', 'string', 'email', 'max:150'],
			'telefono1' => ['nullable', 'string', 'max:20'],
			'telefono2' => ['nullable', 'string', 'max:20'],
			'provincia' => ['nullable', 'string', 'max:100'],
			'municipio' => ['nullable', 'string', 'max:100'],
			'direccion' => ['nullable', 'string'],
			'codigo_postal' => ['nullable', 'string', 'max:10'],
		]);

		Empresa::create($validated);

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
}
