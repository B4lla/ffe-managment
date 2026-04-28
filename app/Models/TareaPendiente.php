<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TareaPendiente extends Model
{
    use HasFactory;

    protected $table = 'tareas_pendientes';

    protected $fillable = [
        'convenio_id',
        'usuario_id',
        'tipo_tarea',
        'descripcion',
        'completada',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function convenio()
    {
        return $this->belongsTo(Convenio::class, 'convenio_id');
    }
}
