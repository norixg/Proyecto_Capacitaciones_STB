<x-guest-layout>
    <div class="auth-card-header">
        <p class="auth-card-kicker">Inicio de sesión</p>

        <h2 class="auth-card-title">
            Acceder
        </h2>

        <p class="auth-card-subtitle">
            Usa tu correo y contraseña para entrar a la plataforma.
        </p>
    </div>

    <x-auth-session-status class="auth-status" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <label for="email">Correo electrónico</label>

            <x-text-input id="email"
                          type="email"
                          name="email"
                          :value="old('email')"
                          required
                          autofocus
                          autocomplete="username" />

            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>

        <div class="mt-5">
            <label for="password">Contraseña</label>

            <x-text-input id="password"
                          type="password"
                          name="password"
                          required
                          autocomplete="current-password" />

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