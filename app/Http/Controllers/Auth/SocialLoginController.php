<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        $this->ensureAllowedProvider($provider);

        $driver = Socialite::driver($provider);
        $scopes = config("services.{$provider}.scopes", []);

        if ($scopes !== []) {
            $driver->scopes($scopes);
        }

        return $driver->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->ensureAllowedProvider($provider);

        $socialUser = Socialite::driver($provider)->user();
        $email = strtolower(trim((string) $socialUser->getEmail()));

        if ($email === '') {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'El proveedor no ha devuelto un email verificable.']);
        }

        $user = User::query()
            ->where('oauth_provider', $provider)
            ->where('oauth_id', (string) $socialUser->getId())
            ->first();

        if (! $user) {
            $user = User::query()
                ->where('email_hash', hash('sha256', $email))
                ->first();
        }

        $name = $socialUser->getName()
            ?: $socialUser->getNickname()
            ?: Str::before($email, '@');

        if ($user) {
            $user->fill([
                'nombre' => $user->nombre ?: $name,
                'email' => $email,
                'oauth_provider' => $provider,
                'oauth_id' => (string) $socialUser->getId(),
                'foto_url' => $user->foto_url ?: $socialUser->getAvatar(),
            ]);
        } else {
            $user = new User([
                'nombre' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(48)),
                'oauth_provider' => $provider,
                'oauth_id' => (string) $socialUser->getId(),
                'foto_url' => $socialUser->getAvatar(),
                'rol_id' => 5,
                'activo' => true,
            ]);
        }

        if (Schema::hasColumn('usuarios', 'email_verified_at')) {
            $user->email_verified_at = $user->email_verified_at ?: now();
        }

        $user->save();

        Auth::login($user, true);

        request()->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function ensureAllowedProvider(string $provider): void
    {
        $allowed = array_filter(array_map(
            fn (string $item): string => trim($item),
            explode(',', (string) config('services.oauth_providers', 'google'))
        ));

        abort_unless(in_array($provider, $allowed, true), 404);
    }
}
