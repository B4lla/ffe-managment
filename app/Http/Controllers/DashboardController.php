<?php

namespace App\Http\Controllers;

use App\Models\Convenio;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Convenio::query()
            ->leftJoin('empresas', 'convenios.empresa_id', '=', 'empresas.id')
            ->select('convenios.*', 'empresas.nombre_razon_social as empresa_nombre');

        if ($this->userHasAnyRole($user, ['Tutor', 'Profesor tutor'])) {
            $query->where('convenios.profesor_id', $user->id);
        }

        $conveniosRecientes = $query
            ->orderByDesc('convenios.created_at')
            ->take(5)
            ->get()
            ->map(function ($convenio) {
                $convenio->empresa_nombre = $convenio->empresa_nombre ?? 'Sin empresa';
                $convenio->estado_clase = $this->estadoClase($convenio->estado);

                return $convenio;
            });

        $data = [
            'convenios_recientes' => $conveniosRecientes,
            'tareas_pendientes' => $this->getTareasPendientes($user),
            'conteo_estados' => [
                'generados' => Convenio::where('estado', 'generado')->count(),
                'firmados' => Convenio::where('estado', 'firmado_empresa')->count(),
                'en_vigor' => Convenio::where('estado', 'en_vigor')->count(),
            ]
        ];

        return view('prueba', $data);
    }

    private function getTareasPendientes($user)
    {
        
        if (! $user) {
            return collect();
        }

        $query = Convenio::query()
            ->leftJoin('empresas', 'convenios.empresa_id', '=', 'empresas.id')
            ->select('convenios.*', 'empresas.nombre_razon_social as empresa_nombre');

        if ($this->userHasRole($user, 'Direccion')) {
            return $query->where('convenios.estado', 'firmado_empresa')->get();
        }
        
        if ($this->userHasRole($user, 'Secretaria')) {
            return $query->where('convenios.estado', 'nuevo_solicitado')->get();
        }

        return collect();
    }

    private function estadoClase(?string $estado): string
    {
        return match ($estado) {
            'generado' => 'bg-blue-100 text-blue-700',
            'firmado_empresa' => 'bg-yellow-100 text-yellow-700',
            'en_vigor' => 'bg-green-100 text-green-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

}