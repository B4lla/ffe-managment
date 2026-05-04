<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

use App\Models\EmpresaContactoFamilia;

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

    public const CATEGORIAS = [
        'ayuntamiento' => 'Ayuntamiento',
        'colegios_institutos' => 'Colegios/Institutos',
        'empresa' => 'Empresa',
    ];

    public const TIPOS = [
        'verde' => 'Empresas buenas / verdes',
        'amarilla' => 'Empresas que funcionan / amarillas',
        'roja' => 'Empresas regulares / rojas',
    ];

    public static function categoriaOptions(): array
    {
        return self::CATEGORIAS;
    }

    public static function tipoOptions(): array
    {
        return self::TIPOS;
    }

    public static function categoriaLabel(?string $value): string
    {
        return self::CATEGORIAS[$value] ?? ($value ?: '-');
    }

    public static function tipoLabel(?string $value): string
    {
        return self::TIPOS[$value] ?? ($value ?: '-');
    }

    public static function tipoBadgeClass(?string $value): string
    {
        return match ($value) {
            'verde' => 'bg-green-100 text-green-800',
            'amarilla' => 'bg-yellow-100 text-yellow-800',
            'roja' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function contactosFamilia()
    {
        return $this->hasMany(EmpresaContactoFamilia::class, 'empresa_id');
    }

    public function scopeSearchByTerm($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $normalizedEmail = self::normalizeEmail($term);
        $normalizedPhone = self::normalizePhone($term);
        $normalizedDniCif = self::normalizeDniCif($term);

        return $query->where(function ($subQuery) use ($term, $normalizedEmail, $normalizedPhone, $normalizedDniCif) {
            $subQuery->where('nombre_razon_social', 'like', "%{$term}%")
                ->orWhere('actividad', 'like', "%{$term}%")
                ->orWhere('categoria', 'like', "%{$term}%")
                ->orWhere('tipo', 'like', "%{$term}%");

            if ($normalizedEmail !== null) {
                $subQuery->orWhere('email_hash', hash('sha256', $normalizedEmail));
            }

            if ($normalizedPhone !== null) {
                $phoneHash = hash('sha256', $normalizedPhone);

                $subQuery->orWhere('telefono1_hash', $phoneHash)
                    ->orWhere('telefono2_hash', $phoneHash);
            }

            if ($normalizedDniCif !== null) {
                $subQuery->orWhere('dni_cif_hash', hash('sha256', $normalizedDniCif));
            }
        });
    }

    public static function normalizeEmail($value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        return $normalized === '' || ! filter_var($normalized, FILTER_VALIDATE_EMAIL) ? null : $normalized;
    }

    public static function normalizePhone($value): ?string
    {
        $normalized = preg_replace('/\D+/', '', (string) $value) ?? '';

        return $normalized === '' ? null : $normalized;
    }

    public static function normalizeDniCif($value): ?string
    {
        $normalized = strtoupper(preg_replace('/[^A-Z0-9]/i', '', trim((string) $value)) ?? '');

        return $normalized === '' ? null : $normalized;
    }

    public function ultimoContactoFamilia()
    {
        return $this->hasOne(EmpresaContactoFamilia::class, 'empresa_id')->latestOfMany();
    }

    public function getDniCifAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setDniCifAttribute($value): void
    {
        $this->attributes['dni_cif'] = $this->encryptValue($value);
        $this->attributes['dni_cif_hash'] = $this->hashNormalizedValue(self::normalizeDniCif($value));
    }

    public function getEmailAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $this->encryptValue($value);
        $this->attributes['email_hash'] = $this->hashNormalizedValue(self::normalizeEmail($value));
    }

    public function getTelefono1Attribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setTelefono1Attribute($value): void
    {
        $this->attributes['telefono1'] = $this->encryptValue($value);
        $this->attributes['telefono1_hash'] = $this->hashNormalizedValue(self::normalizePhone($value));
    }

    public function getTelefono2Attribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setTelefono2Attribute($value): void
    {
        $this->attributes['telefono2'] = $this->encryptValue($value);
        $this->attributes['telefono2_hash'] = $this->hashNormalizedValue(self::normalizePhone($value));
    }

    public function getProvinciaAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setProvinciaAttribute($value): void
    {
        $this->attributes['provincia'] = $this->encryptValue($value);
    }

    public function getMunicipioAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setMunicipioAttribute($value): void
    {
        $this->attributes['municipio'] = $this->encryptValue($value);
    }

    public function getDireccionAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setDireccionAttribute($value): void
    {
        $this->attributes['direccion'] = $this->encryptValue($value);
    }

    public function getCodigoPostalAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setCodigoPostalAttribute($value): void
    {
        $this->attributes['codigo_postal'] = $this->encryptValue($value);
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

    private function hashNormalizedValue(?string $value): ?string
    {
        return $value === null ? null : hash('sha256', $value);
    }
}
