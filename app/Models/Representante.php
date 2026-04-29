<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Representante extends Model
{
    use HasFactory;

    protected $table = 'representantes';

    protected $fillable = [
        'empresa_id',
        'nif',
        'nombre',
        'apellido1',
        'apellido2',
    ];

    public function getNifAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setNifAttribute($value): void
    {
        $this->attributes['nif'] = $this->encryptValue($value);
    }

    public function getNombreAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setNombreAttribute($value): void
    {
        $this->attributes['nombre'] = $this->encryptValue($value);
    }

    public function getApellido1Attribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setApellido1Attribute($value): void
    {
        $this->attributes['apellido1'] = $this->encryptValue($value);
    }

    public function getApellido2Attribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setApellido2Attribute($value): void
    {
        $this->attributes['apellido2'] = $this->encryptValue($value);
    }

    private function decryptValue($value)
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    private function encryptValue($value)
    {
        return $value === null ? null : Crypt::encryptString($value);
    }
}
