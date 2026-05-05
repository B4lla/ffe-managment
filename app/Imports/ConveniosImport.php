<?php

namespace App\Imports;

use App\Models\Convenio;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Representante;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ConveniosImport implements ToCollection, SkipsEmptyRows
{
    use Importable;

    private const HEADER_ALIASES = [
        'num_convenio' => ['num_convenio', 'numero_convenio', 'n_convenio', 'convenio_numero'],
        'empresa_id' => ['empresa_id'],
        'empresa_nombre' => ['empresa_nombre', 'nombre_empresa', 'empresa', 'nombre_razon_social', 'razon_social'],
        'empresa_dni_cif' => ['empresa_dni_cif', 'dni_cif', 'cif', 'nif_empresa'],
        'empresa_actividad' => ['empresa_actividad', 'actividad'],
        'categoria' => ['categoria', 'categoria_empresa'],
        'tipo' => ['tipo', 'tipo_empresa'],
        'contacto_email' => ['contacto_email', 'email_contacto', 'email_empresa', 'email'],
        'contacto_telefono1' => ['contacto_telefono1', 'telefono1', 'telefono_1', 'telefono_principal'],
        'contacto_telefono2' => ['contacto_telefono2', 'telefono2', 'telefono_2', 'telefono_secundario'],
        'domicilio_provincia' => ['domicilio_provincia', 'provincia', 'provincia_social'],
        'domicilio_municipio' => ['domicilio_municipio', 'municipio', 'municipio_social'],
        'domicilio_direccion' => ['domicilio_direccion', 'direccion', 'direccion_social'],
        'domicilio_codigo_postal' => ['domicilio_codigo_postal', 'codigo_postal', 'cp', 'cp_social'],
        'centro_trabajo_provincia' => ['centro_trabajo_provincia', 'provincia_centro_trabajo'],
        'centro_trabajo_municipio' => ['centro_trabajo_municipio', 'municipio_centro_trabajo'],
        'centro_trabajo_direccion' => ['centro_trabajo_direccion', 'direccion_centro_trabajo'],
        'centro_trabajo_codigo_postal' => ['centro_trabajo_codigo_postal', 'codigo_postal_centro_trabajo', 'cp_centro_trabajo'],
        'representante_id' => ['representante_id'],
        'representante_nif' => ['representante_nif', 'nif_representante', 'representante_dni', 'representante_cif'],
        'representante_nombre' => ['representante_nombre', 'nombre_representante'],
        'representante_apellido1' => ['representante_apellido1', 'apellido1_representante', 'representante_apellido_1'],
        'representante_apellido2' => ['representante_apellido2', 'apellido2_representante', 'representante_apellido_2'],
        'responsable_nombre' => ['responsable_nombre', 'resp_gestion_nombre', 'responsable_gestion_nombre'],
        'responsable_telefono' => ['responsable_telefono', 'resp_gestion_telefono', 'responsable_gestion_telefono'],
        'responsable_email' => ['responsable_email', 'resp_gestion_email', 'responsable_gestion_email'],
        'departamento_id' => ['departamento_id'],
        'departamento' => ['departamento', 'departamento_nombre'],
        'profesor_id' => ['profesor_id', 'tutor_id', 'usuario_tutor_id'],
        'tutor_nombre' => ['tutor_nombre', 'profesor_nombre', 'resp_ies_nombre', 'responsable_ies_nombre'],
        'tutor_telefono' => ['tutor_telefono', 'profesor_telefono', 'resp_ies_telefono', 'responsable_ies_telefono'],
        'tutor_email' => ['tutor_email', 'profesor_email', 'resp_ies_email', 'responsable_ies_email'],
        'fecha_firma' => ['fecha_firma', 'fecha'],
        'estado' => ['estado', 'estado_convenio'],
        'observaciones' => ['observaciones', 'observacion'],
        'horario_practicas' => ['horario_practicas', 'horarios', 'horario'],
        'ciclos' => ['ciclos', 'ciclo', 'ciclo_ids', 'ciclos_ids'],
        'tutor_empresa_nombre' => ['tutor_empresa_nombre', 'nombre_tutor_empresa', 'tutor_nombre_completo'],
        'tutor_empresa_dni' => ['tutor_empresa_dni', 'dni_tutor_empresa', 'tutor_dni'],
        'tutor_empresa_email' => ['tutor_empresa_email', 'email_tutor_empresa'],
        'tutor_empresa_telefono' => ['tutor_empresa_telefono', 'telefono_tutor_empresa'],
        'tutor_horarios' => ['tutor_horarios', 'horarios_tutor', 'horario_tutor'],
    ];

    private const LEGACY_POSITIONAL_MAP = [
        0 => 'num_convenio',
        1 => 'empresa_id',
        2 => 'profesor_id',
        3 => 'representante_id',
        4 => 'responsable_nombre',
        5 => 'responsable_telefono',
        6 => 'responsable_email',
        7 => 'tutor_nombre',
        8 => 'tutor_telefono',
        9 => 'tutor_email',
        10 => 'fecha_firma',
        12 => 'estado',
        13 => 'observaciones',
        14 => 'categoria',
        15 => 'tipo',
        16 => 'horario_practicas',
    ];

    public array $created = [];
    public array $updated = [];
    public array $errores = [];

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $headerMap = $this->detectHeaderMap($rows->first());
        $dataRows = $headerMap !== null ? $rows->slice(1)->values() : $rows->values();
        $startingLine = $headerMap !== null ? 2 : 1;

        foreach ($dataRows as $index => $row) {
            $lineNumber = $startingLine + $index;
            $payload = $this->mapRow($row, $headerMap);

            if ($this->isEmptyPayload($payload)) {
                continue;
            }

            try {
                DB::transaction(function () use ($payload): void {
                    $this->importRow($payload);
                });
            } catch (\Throwable $e) {
                $this->errores[] = 'Fila '.$lineNumber.': '.$e->getMessage();
            }
        }
    }

    private function detectHeaderMap($firstRow): ?array
    {
        $row = $this->rowToArray($firstRow);
        $map = [];
        $recognized = 0;

        foreach ($row as $index => $value) {
            $canonical = $this->canonicalizeHeader((string) $value);
            if ($canonical === '') {
                continue;
            }

            foreach (self::HEADER_ALIASES as $field => $aliases) {
                $canonicalAliases = array_map(fn (string $alias): string => $this->canonicalizeHeader($alias), $aliases);
                if (in_array($canonical, $canonicalAliases, true)) {
                    $map[$index] = $field;
                    $recognized++;
                    break;
                }
            }
        }

        return $recognized >= 2 ? $map : null;
    }

    private function mapRow($row, ?array $headerMap): array
    {
        $values = $this->rowToArray($row);
        $payload = [];

        if ($headerMap !== null) {
            foreach ($values as $index => $value) {
                if (! array_key_exists($index, $headerMap)) {
                    continue;
                }

                $payload[$headerMap[$index]] = $this->normalizeCellValue($value);
            }

            return $payload;
        }

        foreach (self::LEGACY_POSITIONAL_MAP as $index => $field) {
            $payload[$field] = $this->normalizeCellValue($values[$index] ?? null);
        }

        return $payload;
    }

    private function importRow(array $payload): void
    {
        $empresa = $this->resolveEmpresa($payload);
        $representante = $this->resolveRepresentante($empresa, $payload);
        [$profesor, $departamento] = $this->resolveProfesorAndDepartamento($payload);
        $convenio = $this->saveConvenio($empresa, $representante, $profesor, $payload);

        $this->upsertEmpresaContactoFamilia($empresa, $departamento, $profesor);
        $this->upsertCentroTrabajo($empresa, $payload);
        $this->upsertTutorEmpresa($empresa, $convenio, $payload);
        $this->syncCiclos($convenio, $payload);
    }

    private function resolveEmpresa(array $payload): Empresa
    {
        $empresa = null;
        $empresaId = $this->nullableInt($payload['empresa_id'] ?? null);
        $dniCif = $this->cleanString($payload['empresa_dni_cif'] ?? null);
        $empresaNombre = $this->cleanString($payload['empresa_nombre'] ?? null);

        if ($empresaId !== null) {
            $empresa = Empresa::query()->find($empresaId);
        }

        if (! $empresa && $dniCif !== null) {
            $empresa = Empresa::query()
                ->where('dni_cif_hash', hash('sha256', Empresa::normalizeDniCif($dniCif)))
                ->first();
        }

        if (! $empresa && $empresaNombre !== null) {
            $empresa = Empresa::query()
                ->whereRaw('LOWER(nombre_razon_social) = ?', [Str::lower($empresaNombre)])
                ->first();
        }

        if (! $empresa && $empresaNombre === null && $dniCif === null) {
            throw new \RuntimeException('La fila no tiene empresa identificable: falta empresa_id, nombre y DNI/CIF.');
        }

        $attributes = array_filter([
            'nombre_razon_social' => $empresaNombre,
            'dni_cif' => $dniCif,
            'actividad' => $this->cleanString($payload['empresa_actividad'] ?? null),
            'categoria' => $this->normalizeCategoria($payload['categoria'] ?? null),
            'tipo' => $this->normalizeTipo($payload['tipo'] ?? null),
            'email' => $this->cleanString($payload['contacto_email'] ?? null),
            'telefono1' => $this->cleanString($payload['contacto_telefono1'] ?? null),
            'telefono2' => $this->cleanString($payload['contacto_telefono2'] ?? null),
            'provincia' => $this->cleanString($payload['domicilio_provincia'] ?? null),
            'municipio' => $this->cleanString($payload['domicilio_municipio'] ?? null),
            'direccion' => $this->cleanString($payload['domicilio_direccion'] ?? null),
            'codigo_postal' => $this->cleanString($payload['domicilio_codigo_postal'] ?? null),
        ], fn ($value) => $value !== null);

        if ($empresa) {
            $empresa->fill($attributes);
            if ($empresa->isDirty()) {
                $empresa->save();
            }
            return $empresa;
        }

        if ($empresaNombre === null || $dniCif === null) {
            throw new \RuntimeException('Para crear una empresa desde importación hacen falta al menos nombre y DNI/CIF.');
        }

        return Empresa::create($attributes);
    }

    private function resolveRepresentante(Empresa $empresa, array $payload): ?Representante
    {
        $representanteId = $this->nullableInt($payload['representante_id'] ?? null);
        if ($representanteId !== null) {
            $representante = Representante::query()->find($representanteId);
            if ($representante) {
                return $representante;
            }
        }

        $nif = $this->cleanString($payload['representante_nif'] ?? null);
        $nombre = $this->cleanString($payload['representante_nombre'] ?? null);
        $apellido1 = $this->cleanString($payload['representante_apellido1'] ?? null);
        $apellido2 = $this->cleanString($payload['representante_apellido2'] ?? null);

        if ($nif === null && $nombre === null && $apellido1 === null && $apellido2 === null) {
            return null;
        }

        $representante = Representante::query()
            ->where('empresa_id', $empresa->id)
            ->get()
            ->first(function (Representante $candidate) use ($nif, $nombre, $apellido1, $apellido2): bool {
                if ($nif !== null && Str::lower((string) $candidate->nif) === Str::lower($nif)) {
                    return true;
                }

                return $nombre !== null
                    && Str::lower((string) $candidate->nombre) === Str::lower($nombre)
                    && Str::lower((string) ($candidate->apellido1 ?? '')) === Str::lower((string) ($apellido1 ?? ''))
                    && Str::lower((string) ($candidate->apellido2 ?? '')) === Str::lower((string) ($apellido2 ?? ''));
            });

        $attributes = array_filter([
            'empresa_id' => $empresa->id,
            'nif' => $nif,
            'nombre' => $nombre,
            'apellido1' => $apellido1,
            'apellido2' => $apellido2,
        ], fn ($value) => $value !== null);

        if ($representante) {
            $representante->fill($attributes);
            if ($representante->isDirty()) {
                $representante->save();
            }
            return $representante;
        }

        return Representante::create($attributes);
    }

    private function resolveProfesorAndDepartamento(array $payload): array
    {
        $departamento = null;
        $departamentoId = $this->nullableInt($payload['departamento_id'] ?? null);
        $departamentoNombre = $this->cleanString($payload['departamento'] ?? null);

        if ($departamentoId !== null) {
            $departamento = Departamento::query()->find($departamentoId);
        } elseif ($departamentoNombre !== null) {
            $departamento = Departamento::query()
                ->whereRaw('LOWER(nombre) = ?', [Str::lower($departamentoNombre)])
                ->first();
        }

        $profesor = null;
        $profesorId = $this->nullableInt($payload['profesor_id'] ?? null);
        $tutorNombre = $this->cleanString($payload['tutor_nombre'] ?? null);
        $tutorEmail = $this->cleanString($payload['tutor_email'] ?? null);

        if ($profesorId !== null) {
            $profesor = User::query()->find($profesorId);
        } elseif ($tutorEmail !== null) {
            $profesor = User::query()
                ->where('email_hash', hash('sha256', Str::lower($tutorEmail)))
                ->first();

            if (! $profesor) {
                $profesor = User::query()->get()->first(function (User $candidate) use ($tutorEmail): bool {
                    return Str::lower((string) $candidate->email) === Str::lower($tutorEmail);
                });
            }
        } elseif ($tutorNombre !== null) {
            $query = User::query();

            if ($departamento?->id) {
                $query->where('departamento_id', $departamento->id);
            }

            $profesor = $query->get()->first(function (User $candidate) use ($tutorNombre): bool {
                return Str::lower((string) $candidate->nombre) === Str::lower($tutorNombre);
            });
        }

        if (! $departamento && $profesor?->departamento_id) {
            $departamento = Departamento::query()->find($profesor->departamento_id);
        }

        return [$profesor, $departamento];
    }

    private function saveConvenio(Empresa $empresa, ?Representante $representante, ?User $profesor, array $payload): Convenio
    {
        $numConvenio = $this->cleanString($payload['num_convenio'] ?? null);
        $fechaFirma = $this->parseDate($payload['fecha_firma'] ?? null);
        $estado = $this->normalizeEstado($payload['estado'] ?? null, $fechaFirma);
        $horarioPracticas = $this->firstNonEmpty(
            $this->normalizeHorarioResumen($payload['horario_practicas'] ?? null),
            $this->normalizeHorarioResumen($payload['tutor_horarios'] ?? null)
        );

        $attributes = [
            'empresa_id' => $empresa->id,
            'profesor_id' => $profesor?->id,
            'representante_id' => $representante?->id,
            'resp_gestion_nombre' => $this->cleanString($payload['responsable_nombre'] ?? null),
            'resp_gestion_telefono' => $this->cleanString($payload['responsable_telefono'] ?? null),
            'resp_gestion_email' => $this->cleanString($payload['responsable_email'] ?? null),
            'resp_ies_nombre' => $this->firstNonEmpty($profesor?->nombre, $this->cleanString($payload['tutor_nombre'] ?? null)),
            'resp_ies_telefono' => $this->cleanString($payload['tutor_telefono'] ?? null),
            'resp_ies_email' => $this->cleanString($payload['tutor_email'] ?? null),
            'fecha_firma' => $fechaFirma?->toDateString(),
            'estado' => $estado,
            'horario_practicas' => $horarioPracticas,
            'observaciones' => $this->cleanString($payload['observaciones'] ?? null),
        ];

        if ($numConvenio !== null) {
            $attributes['num_convenio'] = $numConvenio;
        }

        $convenio = null;
        if ($numConvenio !== null) {
            $convenio = Convenio::query()->where('num_convenio', $numConvenio)->first();
        }

        if (! $convenio && $fechaFirma !== null) {
            $convenio = Convenio::query()
                ->where('empresa_id', $empresa->id)
                ->whereDate('fecha_firma', $fechaFirma->toDateString())
                ->first();
        }

        if ($convenio) {
            $convenio->fill($attributes);
            if ($convenio->isDirty()) {
                $convenio->save();
            }
            $this->updated[] = $numConvenio ?? ('empresa-'.$empresa->id.'-'.$convenio->id);
            return $convenio;
        }

        $convenio = Convenio::create($attributes);
        $this->created[] = $numConvenio ?? ('empresa-'.$empresa->id.'-'.$convenio->id);

        return $convenio;
    }

    private function upsertEmpresaContactoFamilia(Empresa $empresa, ?Departamento $departamento, ?User $profesor): void
    {
        if (! $departamento) {
            return;
        }

        $existing = DB::table('empresa_contacto_familia')
            ->where('empresa_id', $empresa->id)
            ->where('departamento_id', $departamento->id)
            ->first();

        if ($existing) {
            if ($profesor && (int) $existing->profesor_id !== (int) $profesor->id) {
                DB::table('empresa_contacto_familia')
                    ->where('id', $existing->id)
                    ->update(['profesor_id' => $profesor->id]);
            }
            return;
        }

        DB::table('empresa_contacto_familia')->insert([
            'empresa_id' => $empresa->id,
            'departamento_id' => $departamento->id,
            'profesor_id' => $profesor?->id,
            'created_at' => now(),
        ]);
    }

    private function upsertCentroTrabajo(Empresa $empresa, array $payload): void
    {
        $direccion = $this->cleanString($payload['centro_trabajo_direccion'] ?? null);
        $municipio = $this->cleanString($payload['centro_trabajo_municipio'] ?? null);
        $provincia = $this->cleanString($payload['centro_trabajo_provincia'] ?? null);
        $codigoPostal = $this->cleanString($payload['centro_trabajo_codigo_postal'] ?? null);

        if ($direccion === null && $municipio === null && $provincia === null && $codigoPostal === null) {
            return;
        }

        $exists = DB::table('centros_trabajo')
            ->where('empresa_id', $empresa->id)
            ->get()
            ->first(function ($row) use ($direccion, $municipio, $provincia, $codigoPostal): bool {
                return $this->decryptNullable($row->direccion) === $direccion
                    && $this->decryptNullable($row->municipio) === $municipio
                    && $this->decryptNullable($row->provincia) === $provincia
                    && $this->decryptNullable($row->codigo_postal) === $codigoPostal;
            });

        if ($exists) {
            return;
        }

        DB::table('centros_trabajo')->insert([
            'empresa_id' => $empresa->id,
            'direccion' => $this->encryptNullable($direccion),
            'municipio' => $this->encryptNullable($municipio),
            'provincia' => $this->encryptNullable($provincia),
            'codigo_postal' => $this->encryptNullable($codigoPostal),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function upsertTutorEmpresa(Empresa $empresa, Convenio $convenio, array $payload): void
    {
        $nombre = $this->cleanString($payload['tutor_empresa_nombre'] ?? null);
        $dni = $this->cleanString($payload['tutor_empresa_dni'] ?? null);
        $email = $this->cleanString($payload['tutor_empresa_email'] ?? null);
        $telefono = $this->cleanString($payload['tutor_empresa_telefono'] ?? null);

        if ($nombre === null && $dni === null && $email === null && $telefono === null) {
            return;
        }

        $tutor = Tutor::query()
            ->where('empresa_id', $empresa->id)
            ->get()
            ->first(function (Tutor $candidate) use ($nombre, $dni, $email): bool {
                if ($dni !== null && Str::lower((string) $candidate->dni) === Str::lower($dni)) {
                    return true;
                }

                if ($email !== null && Str::lower((string) $candidate->email) === Str::lower($email)) {
                    return true;
                }

                return $nombre !== null && Str::lower((string) $candidate->nombre_completo) === Str::lower($nombre);
            });

        $attributes = array_filter([
            'empresa_id' => $empresa->id,
            'nombre_completo' => $nombre,
            'dni' => $dni,
            'email' => $email,
            'telefono' => $telefono,
        ], fn ($value) => $value !== null);

        if ($tutor) {
            $tutor->fill($attributes);
            if ($tutor->isDirty()) {
                $tutor->save();
            }
        } else {
            $tutor = Tutor::create($attributes);
        }

        $pivotExists = DB::table('convenio_tutor_empresa')
            ->where('convenio_id', $convenio->id)
            ->where('tutor_empresa_id', $tutor->id)
            ->exists();

        if (! $pivotExists) {
            DB::table('convenio_tutor_empresa')->insert([
                'convenio_id' => $convenio->id,
                'tutor_empresa_id' => $tutor->id,
                'created_at' => now(),
            ]);
        }

        $horarios = $this->parseHorarios($payload['tutor_horarios'] ?? null);
        if ($horarios === []) {
            return;
        }

        $existingSlots = DB::table('horarios_practicas')
            ->where('tutor_empresa_id', $tutor->id)
            ->pluck('slot_numero')
            ->map(fn ($slot) => (int) $slot)
            ->all();

        foreach ($horarios as $slot) {
            if (in_array($slot, $existingSlots, true)) {
                continue;
            }

            DB::table('horarios_practicas')->insert([
                'tutor_empresa_id' => $tutor->id,
                'slot_numero' => $slot,
                'horario' => 'Horario '.$slot,
                'created_at' => now(),
            ]);
        }
    }

    private function syncCiclos(Convenio $convenio, array $payload): void
    {
        $raw = $payload['ciclos'] ?? null;
        if ($raw === null || $raw === '') {
            return;
        }

        $tokens = $this->splitValues($raw);
        if ($tokens === []) {
            return;
        }

        $resolvedIds = [];
        foreach ($tokens as $token) {
            if (ctype_digit($token)) {
                $resolvedIds[] = (int) $token;
                continue;
            }

            $cicloId = DB::table('ciclos')
                ->whereRaw('LOWER(nombre) = ?', [Str::lower($token)])
                ->value('id');

            if ($cicloId) {
                $resolvedIds[] = (int) $cicloId;
            }
        }

        foreach (array_values(array_unique($resolvedIds)) as $cicloId) {
            $exists = DB::table('convenio_ciclo')
                ->where('convenio_id', $convenio->id)
                ->where('ciclo_id', $cicloId)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('convenio_ciclo')->insert([
                'convenio_id' => $convenio->id,
                'ciclo_id' => $cicloId,
                'plazas' => 1,
                'created_at' => now(),
            ]);
        }
    }

    private function normalizeEstado($value, ?Carbon $fechaFirma): string
    {
        $normalized = $this->canonicalizeHeader((string) $value);

        return match ($normalized) {
            'nuevo', 'nuevo_solicitado' => 'nuevo_solicitado',
            'pendiente_datos' => 'pendiente_datos',
            'pendiente_secretaria' => 'pendiente_secretaria',
            'pendiente_firma_empresa' => 'pendiente_firma_empresa',
            'firmado_empresa' => 'firmado_empresa',
            'pendiente_validacion_tutor' => 'pendiente_validacion_tutor',
            'validado_tutor' => 'validado_tutor',
            'pendiente_firma_direccion' => 'pendiente_firma_direccion',
            'firmado_centro' => 'firmado_centro',
            'en_vigor' => 'en_vigor',
            'borrador' => 'borrador',
            default => $fechaFirma ? 'en_vigor' : 'borrador',
        };
    }

    private function normalizeCategoria($value): ?string
    {
        $normalized = $this->canonicalizeHeader((string) $value);

        return match ($normalized) {
            'ayuntamiento' => 'ayuntamiento',
            'colegios_institutos', 'colegios', 'institutos', 'colegios_instituto' => 'colegios_institutos',
            'empresa', 'empresas' => 'empresa',
            default => null,
        };
    }

    private function normalizeTipo($value): ?string
    {
        $normalized = $this->canonicalizeHeader((string) $value);

        return match ($normalized) {
            'verde', 'empresas_buenas_verdes', 'empresas_buenas', 'buenas' => 'verde',
            'amarilla', 'amarillas', 'empresas_que_funcionan_amarillas', 'funcionan' => 'amarilla',
            'roja', 'rojas', 'empresas_regulares_rojas', 'regulares' => 'roja',
            default => null,
        };
    }

    private function parseDate($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance(\DateTime::createFromInterface($value));
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
        }

        $string = trim((string) $value);
        if ($string === '') {
            return null;
        }

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd.m.Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $string);
            } catch (\Throwable $e) {
            }
        }

        try {
            return Carbon::parse($string);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Fecha de firma no válida: '.$string);
        }
    }

    private function parseHorarios($value): array
    {
        return array_values(array_filter(array_map(
            fn (string $token): ?int => ctype_digit($token) && (int) $token >= 1 && (int) $token <= 16 ? (int) $token : null,
            $this->splitValues($value)
        )));
    }

    private function normalizeHorarioResumen($value): ?string
    {
        $horarios = $this->parseHorarios($value);
        return $horarios === [] ? null : implode(', ', $horarios);
    }

    private function splitValues($value): array
    {
        if ($value === null) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (string $item): string => trim($item),
            preg_split('/[;,|]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: []
        )));
    }

    private function rowToArray($row): array
    {
        if ($row instanceof Collection) {
            return $row->toArray();
        }

        return is_array($row) ? $row : (array) $row;
    }

    private function normalizeCellValue($value)
    {
        if (is_string($value)) {
            $value = trim($value);
            return $value === '' ? null : $value;
        }

        return $value;
    }

    private function isEmptyPayload(array $payload): bool
    {
        foreach ($payload as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }

        return true;
    }

    private function canonicalizeHeader(string $value): string
    {
        return trim((string) Str::of(Str::ascii($value))
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_'));
    }

    private function cleanString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function nullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function firstNonEmpty(...$values)
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function encryptNullable(?string $value): ?string
    {
        return $value === null || $value === '' ? null : Crypt::encryptString($value);
    }

    private function decryptNullable($value): ?string
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
}
