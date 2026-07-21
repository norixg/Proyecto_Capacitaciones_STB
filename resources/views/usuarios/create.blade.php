<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Crear Usuario
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @php
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
                          action="{{ route('usuarios.store') }}"
                          x-data="{ rolSeleccionado: @js((string) old('id_rol', '')) }">
                        @csrf

                        <div class="mb-4">
                            <label class="block mb-1">Nombre</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2 text-black">
                            @error('name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Usuario</label>
                            <input type="text" name="username" value="{{ old('username') }}" autocomplete="off" class="w-full border rounded px-3 py-2 text-black">
                            <p class="mt-1 text-xs text-gray-500">Mínimo 3 caracteres: letras minúsculas, números, punto, guion o guion bajo.</p>
                            @error('username') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Correo</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2 text-black">
                            @error('email') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4 rounded-2xl border border-blue-200 bg-blue-50 p-4 text-sm font-semibold text-blue-900 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-100">
                            El sistema generará una contraseña temporal segura y la enviará al correo indicado. El usuario deberá cambiarla al iniciar sesión y tendrá 24 horas para hacerlo.
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Rol</label>
                            <select name="id_rol" x-model="rolSeleccionado" class="w-full border rounded px-3 py-2 text-black">
                                <option value="">Seleccione</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id_rol }}" {{ old('id_rol') == $rol->id_rol ? 'selected' : '' }}>
                                        {{ $rol->rol }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_rol') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4" x-cloak x-show="rolSeleccionado === @js($idRolInstructor)">
                            <label class="block mb-1" for="id_instructor">Instructor vinculado de RR. HH.</label>

                            <x-autocomplete-select
                                name="id_instructor"
                                :options="$opcionesInstructores"
                                :selected="old('id_instructor', '')"
                                placeholder="Escriba el nombre o institución del instructor"
                            />

                            <p class="mt-1 text-xs text-gray-500">
                                Este vínculo determina qué capacitaciones podrá administrar el usuario.
                            </p>
                            @error('id_instructor') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1" for="id_empleado">Empleado vinculado</label>
                            @php
                                $opcionesEmpleados = $empleados->map(function ($empleado) {
                                    $etiqueta = $empleado->nombre_completo
                                        . ($empleado->codigo_empleado ? ' — '.$empleado->codigo_empleado : '');

                                    return [
                                        'id' => $empleado->id_empleado,
                                        'etiqueta' => $etiqueta,
                                        'busqueda' => \Illuminate\Support\Str::of($etiqueta)->ascii()->lower()->toString(),
                                    ];
                                })->values();
                            @endphp

                            <div
                                class="relative"
                                x-data="selectAutocomplete(@js($opcionesEmpleados), @js(old('id_empleado', '')))"
                                @click.outside="abierto = false"
                            >
                                <input type="hidden" name="id_empleado" :value="idSeleccionado">

                                <div class="relative">
                                    <input
                                        id="id_empleado"
                                        x-ref="entrada"
                                        type="text"
                                        x-model="consulta"
                                        autocomplete="off"
                                        placeholder="Escriba el nombre o código del empleado"
                                        class="w-full border rounded px-3 py-2 pr-10 text-black"
                                        role="combobox"
                                        aria-autocomplete="list"
                                        :aria-expanded="abierto"
                                        @focus="abierto = true"
                                        @input="escribir()"
                                        @keydown.down.prevent="mover(1)"
                                        @keydown.up.prevent="mover(-1)"
                                        @keydown.enter.prevent="seleccionarActivo()"
                                        @keydown.escape="abierto = false"
                                    >

                                    <button
                                        x-show="consulta"
                                        type="button"
                                        class="absolute inset-y-0 right-0 px-3 text-lg text-gray-500 hover:text-gray-800"
                                        aria-label="Limpiar empleado seleccionado"
                                        @click="limpiar()"
                                    >&times;</button>
                                </div>

                                <div
                                    x-cloak
                                    x-show="abierto"
                                    class="absolute z-50 mt-1 max-h-64 w-full overflow-y-auto rounded border border-gray-200 bg-white shadow-lg"
                                    role="listbox"
                                >
                                    <template x-for="(empleado, indice) in coincidencias" :key="empleado.id">
                                        <button
                                            type="button"
                                            class="block w-full px-3 py-2 text-left text-sm text-gray-900"
                                            :class="indice === indiceActivo ? 'bg-blue-100' : 'hover:bg-gray-100'"
                                            role="option"
                                            :aria-selected="idSeleccionado === String(empleado.id)"
                                            @mouseenter="indiceActivo = indice"
                                            @mousedown.prevent="seleccionar(empleado)"
                                        >
                                            <span x-text="empleado.etiqueta"></span>
                                        </button>
                                    </template>

                                    <p x-show="coincidencias.length === 0" class="px-3 py-3 text-sm text-gray-500">
                                        No se encontraron empleados.
                                    </p>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Puede buscar por nombre o código de empleado.</p>
                            @error('id_empleado') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block mb-1">Estado</label>
                            <select name="estado" class="w-full border rounded px-3 py-2 text-black">
                                <option value="1" {{ old('estado') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('estado') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                                Guardar
                            </button>

                            <a href="{{ route('usuarios.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded">
                                Cancelar
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
