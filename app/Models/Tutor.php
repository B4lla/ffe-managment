<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Crypt;

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

    public function getNombreCompletoAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setNombreCompletoAttribute($value): void
    {
        $this->attributes['nombre_completo'] = $this->encryptValue($value);
    }

    public function getDniAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setDniAttribute($value): void
    {
        $this->attributes['dni'] = $this->encryptValue($value);
    }

    public function getEmailAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $this->encryptValue($value);
    }

    public function getTelefonoAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setTelefonoAttribute($value): void
    {
        $this->attributes['telefono'] = $this->encryptValue($value);
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