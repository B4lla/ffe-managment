<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'nombre_razon_social',
        'dni_cif',
        'actividad',
        'categoria',
        'tipo',
        'email',
        'telefono1',
        'telefono2',
        'provincia',
        'municipio',
        'direccion',
        'codigo_postal',
    ];
}
