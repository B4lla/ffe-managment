<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TareaPendiente;
use Illuminate\Support\Facades\Auth;

class TareasPendientesController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $tareas = TareaPendiente::where('usuario_id', $usuario->id)
            ->where('completada', false)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('tareas_pendientes.index', compact('tareas'));
    }

    public function completar($id)
    {
        $tarea = TareaPendiente::findOrFail($id);
        $tarea->completada = true;
        $tarea->save();
        return redirect()->route('tareas_pendientes.index')->with('status', 'Tarea marcada como completada.');
    }
}
