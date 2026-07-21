<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.28em] text-slate-400">
                    Administración de usuarios
                </p>

                <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-slate-100">
                    Editar usuario
                </h2>

                <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                    Actualiza los datos de acceso, rol, empleado vinculado y estado del usuario.
                </p>
            </div>

            <a href="{{ route('usuarios.index') }}"
               class="inline-flex items-center justify-center rounded-full bg-blue-100 px-5 py-2 text-sm font-black text-blue-800 transition hover:-translate-y-0.5 hover:bg-blue-200">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

            @if ($esPropioUsuario)
                <div class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900 shadow-sm">
                    <p class="text-sm font-bold">
                        Estás editando tu propia cuenta. Desde esta pantalla no puedes cambiar tu rol ni desactivar tu usuario.
                    </p>
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm">
                    <strong class="font-black">Revisa los siguientes errores:</strong>

                    <ul class="mt-2 list-disc list-inside text-sm font-semibold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-[2rem] border border-slate-200/90 bg-white/90 shadow-xl shadow-slate-200/60 backdrop-blur dark:border-slate-700 dark:bg-slate-900/80 dark:shadow-none">
                <div class="border-b border-slate-100 px-6 py-6 dark:border-slate-800">
                    <p class="text-xs font-black uppercase tracking-[0.28em] text-slate-400">
                        Usuario registrado
                    </p>

                    <h3 class="mt-2 text-xl font-black text-slate-900 dark:text-slate-100">
                        {{ $usuario->name }}
                    </h3>

                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                        Modifica únicamente la información necesaria. Las contraseñas temporales se administran desde el listado de usuarios.
                    </p>
                </div>

                @php
                    $rolSeleccionado = old('id_rol', $usuario->rolesSistema->first()?->id_rol);
                    $idRolInstructor = (string) ($roles->first(fn ($rol) => strtolower((string) $rol->rol) === 'instructor')?->id_rol ?? '');
                    $opcionesInstructores = $instructores->map(function ($instructor) {
                        $etiqueta = $instructor->instructor
                            . ($instructor->institucion ? ' — '.$instructor->institucion : '');

                        return [
                            'id' => $instructor->id_instructor,
                            'etiqueta' => $etiqueta,
                            'busqueda' => \Illuminate\Support\Str::of($etiqueta)->ascii()->lower()->toString(),
                        ];
                    })->values();
                @endphp

                <form method="POST"
                      action="{{ route('usuarios.update', $usuario->id) }}"
                      class="px-6 py-6"
                      x-data="{ rolSeleccionado: @js((string) $rolSeleccionado) }">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Nombre
                            </label>

                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $usuario->name) }}"
                                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('name') border-red-500 @enderror">

                            @error('name')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Correo
                            </label>

                            <input type="email"
                                   name="email"
                                   value="{{ old('email', $usuario->email) }}"
                                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('email') border-red-500 @enderror">

                            @error('email')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Usuario
                            </label>

                            <input type="text"
                                   name="username"
                                   value="{{ old('username', $usuario->username) }}"
                                   autocomplete="off"
                                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('username') border-red-500 @enderror">

                            @error('username')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Rol
                            </label>

                            <select name="id_rol"
                                    x-model="rolSeleccionado"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('id_rol') border-red-500 @enderror {{ $esPropioUsuario ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                    {{ $esPropioUsuario ? 'disabled' : '' }}>
                                <option value="">Seleccione</option>

                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id_rol }}" {{ $rolSeleccionado == $rol->id_rol ? 'selected' : '' }}>
                                        {{ $rol->rol }}
                                    </option>
                                @endforeach
                            </select>

                            @if($esPropioUsuario)
                                <input type="hidden" name="id_rol" value="{{ $rolSeleccionado }}">

                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    No puedes cambiar tu propio rol desde esta pantalla.
                                </p>
                            @endif

                            @error('id_rol')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-cloak x-show="rolSeleccionado === @js($idRolInstructor)">
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Instructor vinculado de RR. HH.
                            </label>

                            <x-autocomplete-select
                                name="id_instructor"
                                :options="$opcionesInstructores"
                                :selected="old('id_instructor', $usuario->instructorUser?->id_instructor)"
                                placeholder="Escriba el nombre o institución del instructor"
                            />

                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Este vínculo determina las capacitaciones y seguimientos que puede administrar.
                            </p>

                            @error('id_instructor')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Empleado vinculado
                            </label>

                            <select name="id_empleado"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('id_empleado') border-red-500 @enderror">
                                <option value="">Seleccione</option>

                                @foreach($empleados as $empleado)
                                    <option value="{{ $empleado->id_empleado }}"
                                        {{ old('id_empleado', $usuario->empleadoUser?->id_empleado) == $empleado->id_empleado ? 'selected' : '' }}>
                                        {{ $empleado->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>

                            @error('id_empleado')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Estado
                            </label>

                            @php
                                $estadoSeleccionado = old('estado', $usuario->estado);
                            @endphp

                            <select name="estado"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('estado') border-red-500 @enderror {{ $esPropioUsuario ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                    {{ $esPropioUsuario ? 'disabled' : '' }}>
                                <option value="1" {{ $estadoSeleccionado == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ $estadoSeleccionado == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>

                            @if($esPropioUsuario)
                                <input type="hidden" name="estado" value="{{ $estadoSeleccionado }}">

                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    No puedes desactivarte a vos misma mientras estás usando esta cuenta.
                                </p>
                            @endif

                            @error('estado')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                        <a href="{{ route('usuarios.index') }}"
                           class="inline-flex items-center justify-center rounded-full bg-blue-100 px-6 py-3 text-sm font-black text-blue-800 transition hover:-translate-y-0.5 hover:bg-blue-200">
                            Cancelar
                        </a>

                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-full bg-slate-950 px-6 py-3 text-sm font-black text-white shadow-xl shadow-slate-300/70 transition hover:-translate-y-0.5 hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-950 dark:shadow-none">
                            Actualizar usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
