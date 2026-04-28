<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function userHasRole($user, string|array $roles): bool
    {
        if (! $user) {
            return false;
        }

        if (! $user->relationLoaded('rol')) {
            $user->load('rol');
        }

        $currentRole = strtolower(trim((string) optional($user->rol)->nombre));
        $roles = array_map(
            fn (string $role): string => strtolower(trim($role)),
            is_array($roles) ? $roles : [$roles]
        );

        return $currentRole !== '' && in_array($currentRole, $roles, true);
    }

    protected function userHasAnyRole($user, array $roles): bool
    {
        return $this->userHasRole($user, $roles);
    }
}
