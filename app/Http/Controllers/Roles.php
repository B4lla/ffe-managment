<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class Roles extends Controller
{
    public function index()
    {
        $roles = Rol::query()->orderBy('nombre')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150|unique:roles,nombre',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        Rol::create($validated);

        return Redirect::route('roles.index')->with('status', 'Rol creado.');
    }

    public function edit(int $id)
    {
        $rol = Rol::findOrFail($id);
        return view('roles.edit', compact('rol'));
    }

    public function update(Request $request, int $id)
    {
        $rol = Rol::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150|unique:roles,nombre,'.$rol->id,
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $rol->update($validated);

        return Redirect::route('roles.index')->with('status', 'Rol actualizado.');
    }

    public function destroy(int $id)
    {
        $rol = Rol::findOrFail($id);
        $rol->delete();
        return Redirect::route('roles.index')->with('status', 'Rol eliminado.');
    }
}
