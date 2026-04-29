<?php

namespace App\Http\Middleware;

use App\Models\Convenio;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureConvenioAccess
{
    public function handle(Request $request, Closure $next, string $ability = 'viewAny'): Response
    {
        $user = $request->user();

        abort_unless($user, 403);

        $role = $this->roleName($user);
        $convenio = $this->resolveConvenio($request);

        if ($role === 'administrador') {
            return $next($request);
        }

        if ($ability === 'viewAny') {
            if (in_array($role, ['direccion', 'coordinador ffe', 'profesor tutor', 'profesor', 'secretaria', 'empresa externa'], true)) {
                return $next($request);
            }

            abort(403);
        }

        if (! $convenio instanceof Convenio) {
            abort(404);
        }

        if ($role === 'empresa externa') {
            $this->ensureOwnCompany($user, $convenio);
        }

        if ($role === 'coordinador ffe') {
            $this->ensureSameDepartment($user, $convenio);
        }

        if ($role === 'profesor tutor') {
            $this->ensureAssignedTutor($user, $convenio);
        }

        $allowed = match ($ability) {
            'view' => ['direccion', 'coordinador ffe', 'profesor tutor', 'profesor', 'secretaria', 'empresa externa'],
            'create', 'store' => ['coordinador ffe', 'profesor tutor', 'secretaria'],
            'editInitial' => ['coordinador ffe', 'profesor tutor', 'secretaria'],
            'generatePdf' => ['secretaria'],
            'downloadProvisional' => ['direccion', 'coordinador ffe', 'profesor tutor', 'secretaria', 'empresa externa'],
            'firmEmpresa' => ['empresa externa'],
            'validateSignature' => ['profesor tutor'],
            'signCenter' => ['direccion'],
            'downloadFinal' => ['direccion', 'empresa externa'],
            'delete' => ['coordinador ffe'],
            default => [],
        };

        if (! in_array($role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }

    private function resolveConvenio(Request $request): ?Convenio
    {
        $convenio = $request->route('convenio') ?? $request->route('id');

        if ($convenio instanceof Convenio) {
            return $convenio->loadMissing('empresa.contactosFamilia');
        }

        if (is_numeric($convenio)) {
            return Convenio::with('empresa.contactosFamilia')->find((int) $convenio);
        }

        return null;
    }

    private function roleName($user): string
    {
        if (! $user->relationLoaded('rol')) {
            $user->load('rol');
        }

        return strtolower(trim((string) optional($user->rol)->nombre));
    }

    private function ensureOwnCompany($user, Convenio $convenio): void
    {
        if (! $user->empresa_id || (int) $convenio->empresa_id !== (int) $user->empresa_id) {
            abort(403);
        }
    }

    private function ensureSameDepartment($user, Convenio $convenio): void
    {
        if (! $user->departamento_id) {
            abort(403);
        }

        $matchesDepartment = $convenio->empresa?->contactosFamilia()
            ->where('departamento_id', $user->departamento_id)
            ->exists();

        if (! $matchesDepartment) {
            abort(403);
        }
    }

    private function ensureAssignedTutor($user, Convenio $convenio): void
    {
        if ((int) $convenio->profesor_id !== (int) $user->id) {
            abort(403);
        }
    }
}
