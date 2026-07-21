<x-guest-layout>
    <div class="auth-card-header">
        <p class="auth-card-kicker">Inicio de sesión</p>

        <h2 class="auth-card-title">
            Acceder
        </h2>

        <p class="auth-card-subtitle">
            Usa tu nombre de usuario o correo y contraseña para entrar a la plataforma.
        </p>
    </div>

    <x-auth-session-status class="auth-status" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <label for="login">Usuario o correo electrónico</label>

            <x-text-input id="login"
                          type="text"
                          name="login"
                          :value="old('login')"
                          required
                          autofocus
                          autocomplete="username" />

            <x-input-error :messages="$errors->get('login')" class="auth-error" />
        </div>

        <div class="mt-5">
            <label for="password">Contraseña</label>

            <div class="relative" x-data="{ mostrarPassword: false }">
                <x-text-input id="password"
                              x-bind:type="mostrarPassword ? 'text' : 'password'"
                              name="password"
                              class="!pr-12"
                              required
                              autocomplete="current-password" />

                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-500 transition hover:text-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                    x-bind:aria-label="mostrarPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                    x-bind:title="mostrarPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                    x-on:click="mostrarPassword = ! mostrarPassword"
                >
                    <svg x-show="! mostrarPassword" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6S2.25 12 2.25 12Z" />
                        <circle cx="12" cy="12" r="2.75" />
                    </svg>

                    <svg x-cloak x-show="mostrarPassword" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.15A10.8 10.8 0 0 1 12 6c6.25 0 9.75 6 9.75 6a17.6 17.6 0 0 1-2.1 2.8M6.5 7.15C3.75 9.05 2.25 12 2.25 12s3.5 6 9.75 6a9.8 9.8 0 0 0 3.1-.5M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>

        <div class="mt-5 flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center gap-2 !mb-0">
                <input id="remember_me"
                       type="checkbox"
                       name="remember">

                <span class="text-sm font-medium text-slate-500">
                    Recordarme
                </span>
            </label>

            @if (Route::has('password.request'))
                <a class="auth-secondary-link" href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <div class="mt-7 flex items-center justify-end">
            <button type="submit" class="auth-primary-button">
                Iniciar sesión
            </button>
        </div>
    </form>
</x-guest-layout>
