<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\Importable;

use App\Models\Convenio;

class ConveniosImport implements ToModel, SkipsEmptyRows
{
    use Importable;

    public $imported = [];
    public $errores = [];

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            $data = [
                'num_convenio' => $row[0] ?? null,
                'empresa_id' => $row[1] ?? null,
                'profesor_id' => $row[2] ?? null,
                'representante_id' => $row[3] ?? null,
                'resp_gestion_nombre' => $row[4] ?? null,
                'resp_gestion_telefono' => $row[5] ?? null,
                'resp_gestion_email' => $row[6] ?? null,
                'resp_ies_nombre' => $row[7] ?? null,
                'resp_ies_telefono' => $row[8] ?? null,
                'resp_ies_email' => $row[9] ?? null,
                'fecha_firma' => $row[10] ?? null,
                'fecha_fin' => $row[11] ?? null,
                'estado' => $row[12] ?? null,
                'observaciones' => $row[13] ?? null,
                'horario_practicas' => $row[16] ?? null,
            ];

            $this->imported[] = $data['num_convenio'] ?? 'Fila';

            return new Convenio($data);
        } catch (\Throwable $e) {
            $this->errores[] = ($row[0] ?? 'Fila').': '.$e->getMessage();
            return null;
        }
    }
}
