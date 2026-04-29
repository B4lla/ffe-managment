<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'email',
        'email_hash',
        'password',
        'dni_cif',
        'departamento_id',
        'empresa_id',
        'rol_id',
        'foto_url',
        'activo',
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean',
    ];

    protected $hidden = [
        'nombre',
        'email',
        'email_hash',
        'password',
        'remember_token',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function getNameAttribute()
    {
            return $this->getAttribute('nombre');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['nombre'] = $value;
    }

    public function getNombreAttribute($value)
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

    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = $value === null ? null : Crypt::encryptString($value);
    }

    public function getEmailAttribute($value)
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

    public function setEmailAttribute($value)
    {
        $normalized = $value === null ? null : strtolower(trim((string) $value));

        $this->attributes['email'] = $normalized === null ? null : Crypt::encryptString($normalized);
        $this->attributes['email_hash'] = $normalized === null ? null : hash('sha256', $normalized);
    }

    public function getDniCifAttribute($value)
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

    public function setDniCifAttribute($value)
    {
        $this->attributes['dni_cif'] = $value === null ? null : Crypt::encryptString($value);
    }
}
