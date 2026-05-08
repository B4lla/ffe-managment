<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="flex items-center gap-4">
            <div class="h-20 w-20 overflow-hidden rounded-full bg-gray-100 border border-gray-200">
                @if($user->foto_url)
                    <img src="{{ $user->foto_url }}" alt="Foto de perfil" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full w-full items-center justify-center text-xl font-semibold text-gray-500">
                        {{ strtoupper(mb_substr($user->nombre ?? $user->email ?? 'U', 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="text-sm text-gray-600">
                Sube una imagen o pega una URL. Si subes archivo, se usa ese archivo.
            </div>
        </div>

        <div>
            <x-input-label for="nombre" :value="__('Name')" />
            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="foto_archivo" value="Subir foto de perfil" />
            <input id="foto_archivo" name="foto_archivo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-gray-700">
            <x-input-error class="mt-2" :messages="$errors->get('foto_archivo')" />
        </div>

        <div>
            <x-input-label for="foto_url" value="URL de foto de perfil" />
            <x-text-input id="foto_url" name="foto_url" type="url" class="mt-1 block w-full" :value="old('foto_url', $user->foto_url)" placeholder="https://ejemplo.com/foto.jpg" />
            <x-input-error class="mt-2" :messages="$errors->get('foto_url')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
