<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Convenio extends Model
{
    protected $fillable = [
        'empresa_id',
        'profesor_id',
        'representante_id',
        'fecha_fin',
        'fecha_firma',
        'estado',
        'horario_practicas',
        'num_convenio',
        'resp_gestion_nombre',
        'resp_gestion_telefono',
        'resp_gestion_email',
        'resp_ies_nombre',
        'resp_ies_telefono',
        'resp_ies_email',
        'observaciones',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}