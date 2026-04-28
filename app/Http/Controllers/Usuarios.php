<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Usuarios extends Controller
{
	public function index(Request $request)
	{
		$currentUser = Auth::user();

		$query = User::query()->with([
			'rol:id,nombre',
			'departamento:id,nombre',
		]);

		if ($request->filled('rol_id')) {
			$query->where('rol_id', (int) $request->input('rol_id'));
		}

		if ($request->filled('departamento_id')) {
			$query->where('departamento_id', (int) $request->input('departamento_id'));
		}

		if ($request->filled('activo')) {
			$query->where('activo', (bool) $request->input('activo'));
		}

		if ($request->filled('email')) {
			$email = strtolower(trim((string) $request->input('email')));
			$query->where('email_hash', hash('sha256', $email));
		}

		$usuarios = $query
			->orderByDesc('created_at')
			->paginate(15)
			->withQueryString();

		$roles = Rol::query()
			->orderBy('nombre')
			->get(['id', 'nombre']);

		$departamentos = Departamento::query()
			->orderBy('nombre')
			->get(['id', 'nombre']);

		return view('usuarios', [
			'usuarios' => $usuarios,
			'roles' => $roles,
			'departamentos' => $departamentos,
			'puede_gestionar' => $this->canManageUsers($currentUser),
			'filtros' => $request->only(['rol_id', 'departamento_id', 'activo', 'email']),
		]);
	}

	public function create()
	{
		abort_unless($this->canManageUsers(Auth::user()), 403);

		$roles = Rol::query()
			->orderBy('nombre')
			->get(['id', 'nombre']);

		$departamentos = Departamento::query()
			->orderBy('nombre')
			->get(['id', 'nombre']);

		return view('usuarios.create', [
			'roles' => $roles,
			'departamentos' => $departamentos,
		]);
	}

	public function store(Request $request)
	{
		abort_unless($this->canManageUsers(Auth::user()), 403);

		$email = strtolower(trim((string) $request->input('email')));
		$request->merge(['email_hash' => hash('sha256', $email)]);

		$validated = $request->validate([
			'nombre' => ['required', 'string', 'max:200'],
			'email' => ['required', 'string', 'email', 'max:150'],
			'email_hash' => ['required', 'string', 'size:64', 'unique:usuarios,email_hash'],
			'dni_cif' => ['nullable', 'string', 'max:20'],
			'password' => ['required', 'confirmed', 'min:8'],
			'departamento_id' => ['nullable', 'integer', 'exists:departamentos,id'],
			'rol_id' => ['nullable', 'integer', 'exists:roles,id'],
			'activo' => ['nullable', 'boolean'],
		]);

		User::create([
			'nombre' => $validated['nombre'],
			'email' => $validated['email'],
			'email_hash' => $validated['email_hash'],
			'dni_cif' => $validated['dni_cif'] ?? null,
			'password' => Hash::make($validated['password']),
			'departamento_id' => $validated['departamento_id'] ?? null,
			'rol_id' => $validated['rol_id'] ?? null,
			'activo' => $request->boolean('activo', true),
		]);

		return redirect()
			->route('usuarios.index')
			->with('status', 'Usuario creado correctamente.');
	}

	private function canManageUsers($user): bool
	{
		if (! $user) {
			return false;
		}

		if ($this->userHasRole($user, 'Administrador')) {
			return true;
		}

		return false;
	}
}
