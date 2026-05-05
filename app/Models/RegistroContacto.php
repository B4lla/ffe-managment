<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroContacto extends Model
{
    use HasFactory;

    protected $table = 'registro_contactos';

    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'profesor_id',
        'resultado',
        'observaciones',
        'fecha_contacto',
    ];

    protected $casts = [
        'fecha_contacto' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }
}
