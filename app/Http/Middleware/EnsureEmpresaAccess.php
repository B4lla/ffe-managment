<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmpresaAccess
{
    public function handle(Request $request, Closure $next, string $ability = 'viewAny'): Response
    {
        $user = $request->user();

        abort_unless($user, 403);

        $role = $this->roleName($user);

        if ($role === 'administrador') {
            return $next($request);
        }

        $allowed = match ($ability) {
            'viewAny', 'view' => ['coordinador ffe', 'profesor tutor', 'profesor', 'secretaria'],
            'create', 'store' => ['coordinador ffe', 'profesor tutor', 'secretaria'],
            'edit', 'update', 'delete', 'export' => ['coordinador ffe', 'secretaria'],
            'contacts' => ['coordinador ffe', 'profesor tutor', 'profesor', 'secretaria'],
            default => [],
        };

        if (in_array($role, $allowed, true)) {
            return $next($request);
        }

        abort(403);
    }

    private function roleName($user): string
    {
        if (! $user->relationLoaded('rol')) {
            $user->load('rol');
        }

        return strtolower(trim((string) optional($user->rol)->nombre));
    }
}
