<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'email',
        'email_hash',
        'password',
        'dni_cif',
        'departamento_id',
        'rol_id',
        'foto_url',
        'activo',
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'nombre' => 'encrypted',
        'email' => 'encrypted',
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
}
