<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleAccess
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user, 403);

        $role = $this->roleName($user);

        if ($role === 'administrador') {
            return $next($request);
        }

        $allowedRoles = [];
        foreach ($roles as $roleList) {
            foreach (preg_split('/\s*,\s*/', $roleList, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $roleName) {
                $allowedRoles[] = strtolower(trim($roleName));
            }
        }

        if (in_array($role, $allowedRoles, true)) {
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
