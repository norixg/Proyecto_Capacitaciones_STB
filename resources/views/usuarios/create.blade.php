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

                    <form method="POST" action="{{ route('usuarios.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="block mb-1">Nombre</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2 text-black">
                            @error('name') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Correo</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2 text-black">
                            @error('email') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Contraseña</label>
                            <input type="password" name="password" class="w-full border rounded px-3 py-2 text-black">
                            @error('password') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2 text-black">
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Rol</label>
                            <select name="id_rol" class="w-full border rounded px-3 py-2 text-black">
                                <option value="">Seleccione</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id_rol }}" {{ old('id_rol') == $rol->id_rol ? 'selected' : '' }}>
                                        {{ $rol->rol }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_rol') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Empleado vinculado</label>
                            <select name="id_empleado" class="w-full border rounded px-3 py-2 text-black">
                                <option value="">Seleccione</option>
                                @foreach($empleados as $empleado)
                                    <option value="{{ $empleado->id_empleado }}" {{ old('id_empleado') == $empleado->id_empleado ? 'selected' : '' }}>
                                        {{ $empleado->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
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