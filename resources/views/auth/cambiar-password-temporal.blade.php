<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100">Establece tu contraseña</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            Por seguridad debes cambiar la contraseña temporal antes de ingresar al sistema.
        </p>
    </div>

    @if($passwordTemporalExpirada)
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">
            La contraseña temporal venció. Cierra sesión y solicita al administrador que genere una nueva.
        </div>
    @else
        <form method="POST"
              action="{{ route('password.temporal.update') }}"
              class="space-y-5"
              x-data="{ mostrarActual: false, mostrarNueva: false, mostrarConfirmacion: false }">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="password_actual" value="Contraseña temporal" />
                <div class="relative mt-1">
                    <x-text-input id="password_actual"
                                  name="password_actual"
                                  x-bind:type="mostrarActual ? 'text' : 'password'"
                                  class="block w-full !pr-12"
                                  required
                                  autofocus
                                  autocomplete="current-password" />
                    <button type="button"
                            class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-500 transition hover:text-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                            x-bind:aria-label="mostrarActual ? 'Ocultar contraseña temporal' : 'Mostrar contraseña temporal'"
                            x-bind:title="mostrarActual ? 'Ocultar contraseña temporal' : 'Mostrar contraseña temporal'"
                            x-on:click="mostrarActual = ! mostrarActual">
                        <svg x-show="! mostrarActual" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6S2.25 12 2.25 12Z" />
                            <circle cx="12" cy="12" r="2.75" />
                        </svg>
                        <svg x-cloak x-show="mostrarActual" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.15A10.8 10.8 0 0 1 12 6c6.25 0 9.75 6 9.75 6a17.6 17.6 0 0 1-2.1 2.8M6.5 7.15C3.75 9.05 2.25 12 2.25 12s3.5 6 9.75 6a9.8 9.8 0 0 0 3.1-.5M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_actual')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" value="Nueva contraseña" />
                <div class="relative mt-1">
                    <x-text-input id="password" name="password" x-bind:type="mostrarNueva ? 'text' : 'password'" class="block w-full !pr-12" required autocomplete="new-password" />
                    <button type="button"
                            class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-500 transition hover:text-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                            x-bind:aria-label="mostrarNueva ? 'Ocultar nueva contraseña' : 'Mostrar nueva contraseña'"
                            x-bind:title="mostrarNueva ? 'Ocultar nueva contraseña' : 'Mostrar nueva contraseña'"
                            x-on:click="mostrarNueva = ! mostrarNueva">
                        <svg x-show="! mostrarNueva" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6S2.25 12 2.25 12Z" />
                            <circle cx="12" cy="12" r="2.75" />
                        </svg>
                        <svg x-cloak x-show="mostrarNueva" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.15A10.8 10.8 0 0 1 12 6c6.25 0 9.75 6 9.75 6a17.6 17.6 0 0 1-2.1 2.8M6.5 7.15C3.75 9.05 2.25 12 2.25 12s3.5 6 9.75 6a9.8 9.8 0 0 0 3.1-.5M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Confirmar nueva contraseña" />
                <div class="relative mt-1">
                    <x-text-input id="password_confirmation" name="password_confirmation" x-bind:type="mostrarConfirmacion ? 'text' : 'password'" class="block w-full !pr-12" required autocomplete="new-password" />
                    <button type="button"
                            class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-500 transition hover:text-blue-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                            x-bind:aria-label="mostrarConfirmacion ? 'Ocultar confirmación de contraseña' : 'Mostrar confirmación de contraseña'"
                            x-bind:title="mostrarConfirmacion ? 'Ocultar confirmación de contraseña' : 'Mostrar confirmación de contraseña'"
                            x-on:click="mostrarConfirmacion = ! mostrarConfirmacion">
                        <svg x-show="! mostrarConfirmacion" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6S2.25 12 2.25 12Z" />
                            <circle cx="12" cy="12" r="2.75" />
                        </svg>
                        <svg x-cloak x-show="mostrarConfirmacion" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.15A10.8 10.8 0 0 1 12 6c6.25 0 9.75 6 9.75 6a17.6 17.6 0 0 1-2.1 2.8M6.5 7.15C3.75 9.05 2.25 12 2.25 12s3.5 6 9.75 6a9.8 9.8 0 0 0 3.1-.5M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                        </svg>
                    </button>
                </div>
            </div>

            <x-primary-button class="w-full justify-center">Guardar contraseña e ingresar</x-primary-button>
        </form>
    @endif

    <form method="POST" action="{{ route('logout') }}" class="mt-5 text-center">
        @csrf
        <button type="submit" class="text-sm font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">Cerrar sesión</button>
    </form>
</x-guest-layout>
