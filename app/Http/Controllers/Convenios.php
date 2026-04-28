<?php

namespace App\Http\Controllers;

use App\Models\Convenio;
use App\Models\Empresa;
use App\Models\Departamento;
use App\Models\Tutor;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Convenios extends Controller
{
	public function index(Request $request)
	{
		$query = Convenio::with('empresa')->whereHas('empresa', function ($q) use ($request) {
			if ($request->filled('categoria')) {
				$q->where('categoria', (string) $request->input('categoria'));
			}

			if ($request->filled('tipo')) {
				$q->where('tipo', (string) $request->input('tipo'));
			}

			if ($request->filled('q')) {
				$term = trim((string) $request->input('q'));
				$q->where(function ($subQuery) use ($term) {
					$subQuery->where('nombre_razon_social', 'like', "%{$term}%")
						->orWhere('dni_cif', 'like', "%{$term}%")
						->orWhere('email', 'like', "%{$term}%")
						->orWhere('telefono1', 'like', "%{$term}%")
						->orWhere('telefono2', 'like', "%{$term}%")
						->orWhere('municipio', 'like', "%{$term}%")
						->orWhere('provincia', 'like', "%{$term}%");
				});
			}
		});

		$convenios = $query
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

		return view('convenios', [
			'convenios' => $convenios,
			'categorias' => $categorias,
			'tipos' => $tipos,
			'filtros' => $request->only(['categoria', 'tipo', 'q']),
			'puede_crear' => $this->canCreateCompanies(Auth::user()),
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



    public function create()
    {
		$departamentos = Departamento::all();

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

		return view('convenios.create', compact('departamentos', 'tutoresByDept'));
    }

    public function store() 
    {
        $validatedData = request()->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'fecha_firma' => 'nullable|date',
            'estado' => 'required|string|max:255',
            'horario_practicas' => 'nullable|string|max:255',
        ]);
        
        Convenio::create($validatedData);

        return redirect()->route('convenios.index')->with('success', 'Convenio creado exitosamente.');
    }
}
