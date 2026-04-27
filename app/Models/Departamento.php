<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $table = 'departamentos';

    protected $fillable = [
        'nombre',
        'familia_profesional',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class, 'departamento_id');
    }
}
