<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoPdf extends Model
{
    protected $table = 'documentos_pdf';

    protected $fillable = [
        'convenio_id',
        'subido_por',
        'tipo',
        'estado_doc',
        'ruta_archivo',
        'es_erroneo',
        'motivo_error',
    ];

    protected $casts = [
        'es_erroneo' => 'boolean',
    ];

    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenio::class, 'convenio_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public static function tipoLabel(?string $tipo): string
    {
        return [
            'provisional' => 'PDF generado por secretaria',
            'firmado_empresa' => 'PDF firmado por empresa',
            'firmado_centro' => 'PDF firmado por centro',
        ][$tipo] ?? ($tipo ?: '-');
    }
}
