<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tutor extends Model
{
    protected $table = 'tutores_empresa';

    protected $fillable = [
        'empresa_id',
        'nombre_completo',
        'dni',
        'email',
        'telefono',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function convenios(): BelongsToMany
    {
        return $this->belongsToMany(
            Convenio::class,
            'convenio_tutor_empresa',
            'tutor_empresa_id',
            'convenio_id'
        )->withTimestamps();
    }
}