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
        $tareas = TareaPendiente::with('convenio')
            ->where('usuario_id', $usuario->id)
            ->where('completada', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (TareaPendiente $tarea) {
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
                    'meter datos' => ['convenios.datos', 'Meter datos iniciales'],
                    'datos' => ['convenios.datos', 'Meter datos iniciales'],
                    'insertar' => ['convenios.insertar', 'Insertar convenio'],
                    'crear' => ['convenios.insertar', 'Insertar convenio'],
                    'ver convenio' => ['convenios.show', 'Ver convenio'],
                    'detalle' => ['convenios.show', 'Ver detalle'],
                ];

                foreach ($matches as $needle => [$routeName, $label]) {
                    if ($tipo !== '' && str_contains($tipo, $needle) && $convenioId) {
                        $tarea->action_label = $label;
                        $tarea->action_url = route($routeName, $convenioId);
                        return $tarea;
                    }
                }

                return $tarea;
            });
        return view('tareas_pendientes.index', compact('tareas'));
    }

    public function completar($id)
    {
        $tarea = TareaPendiente::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->firstOrFail();
        $tarea->completada = true;
        $tarea->save();
        return redirect()->route('tareas_pendientes.index')->with('status', 'Tarea marcada como completada.');
    }
}
