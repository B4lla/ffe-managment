<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Carbon;

class Convenio extends Model
{
    protected $casts = [
        'fecha_firma' => 'date',
    ];

    protected $fillable = [
        'empresa_id',
        'profesor_id',
        'representante_id',
        'fecha_firma',
        'estado',
        'horario_practicas',
        'num_convenio',
        'resp_gestion_nombre',
        'resp_gestion_telefono',
        'resp_gestion_email',
        'resp_ies_nombre',
        'resp_ies_telefono',
        'resp_ies_email',
        'observaciones',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public static function vigenciaOptions(): array
    {
        return [
            'en_vigor' => 'En vigor',
            'caduca_1_ano' => 'Caducan en menos de 1 año',
            'caduca_6_meses' => 'Caducan en menos de 6 meses',
            'caduca_3_meses' => 'Caducan en menos de 3 meses',
            'caducado' => 'Caducados',
            'sin_fecha_firma' => 'Sin fecha de firma',
        ];
    }

    public static function vigenciaLabel(?string $value): string
    {
        return self::vigenciaOptions()[$value] ?? ($value ?: '-');
    }

    public static function vigenciaBadgeClass(?string $value): string
    {
        return match ($value) {
            'en_vigor' => 'bg-green-100 text-green-800',
            'caduca_1_ano' => 'bg-blue-100 text-blue-800',
            'caduca_6_meses' => 'bg-yellow-100 text-yellow-800',
            'caduca_3_meses' => 'bg-orange-100 text-orange-800',
            'caducado' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function getFechaCaducidadAttribute(): ?Carbon
    {
        if (! $this->fecha_firma) {
            return null;
        }

        return $this->fecha_firma->copy()->addYears(4);
    }

    public function getVigenciaKeyAttribute(): string
    {
        if (! $this->fecha_firma) {
            return 'sin_fecha_firma';
        }

        $today = now()->startOfDay();
        $fechaCaducidad = $this->fecha_caducidad?->copy()->startOfDay();

        if (! $fechaCaducidad) {
            return 'sin_fecha_firma';
        }

        if ($fechaCaducidad->lessThanOrEqualTo($today)) {
            return 'caducado';
        }

        if ($fechaCaducidad->lessThanOrEqualTo($today->copy()->addMonths(3))) {
            return 'caduca_3_meses';
        }

        if ($fechaCaducidad->lessThanOrEqualTo($today->copy()->addMonths(6))) {
            return 'caduca_6_meses';
        }

        if ($fechaCaducidad->lessThanOrEqualTo($today->copy()->addYear())) {
            return 'caduca_1_ano';
        }

        return 'en_vigor';
    }

    public function getRespGestionNombreAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setRespGestionNombreAttribute($value): void
    {
        $this->attributes['resp_gestion_nombre'] = $this->encryptValue($value);
    }

    public function getRespGestionTelefonoAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setRespGestionTelefonoAttribute($value): void
    {
        $this->attributes['resp_gestion_telefono'] = $this->encryptValue($value);
    }

    public function getRespGestionEmailAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setRespGestionEmailAttribute($value): void
    {
        $this->attributes['resp_gestion_email'] = $this->encryptValue($value);
    }

    public function getRespIesNombreAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setRespIesNombreAttribute($value): void
    {
        $this->attributes['resp_ies_nombre'] = $this->encryptValue($value);
    }

    public function getRespIesTelefonoAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setRespIesTelefonoAttribute($value): void
    {
        $this->attributes['resp_ies_telefono'] = $this->encryptValue($value);
    }

    public function getRespIesEmailAttribute($value)
    {
        return $this->decryptValue($value);
    }

    public function setRespIesEmailAttribute($value): void
    {
        $this->attributes['resp_ies_email'] = $this->encryptValue($value);
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
        return $value === null || $value === '' ? null : Crypt::encryptString($value);
    }
}
