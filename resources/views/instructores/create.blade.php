<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Crear Instructor
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if ($errors->any())
                        <div class="mb-6 rounded border border-red-300 bg-red-100 px-4 py-3 text-red-800">
                            <strong>Revisa los siguientes errores:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('instructores.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="block mb-1">Nombre del instructor</label>
                            <input type="text" name="instructor" value="{{ old('instructor') }}"
                                class="w-full border rounded px-3 py-2 text-black @error('instructor') border-red-500 @enderror">
                            @error('instructor') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 mt-1">Mínimo 3 caracteres.</p>
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Correo</label>
                            <input type="email" name="correo" value="{{ old('correo') }}"
                                class="w-full border rounded px-3 py-2 text-black @error('correo') border-red-500 @enderror">
                            @error('correo') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Teléfono</label>
                            <input type="text" name="telefono" value="{{ old('telefono') }}"
                                class="w-full border rounded px-3 py-2 text-black @error('telefono') border-red-500 @enderror">
                            @error('telefono') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 mt-1">Si lo ingresas, debe tener al menos 8 caracteres.</p>
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Tipo de instructor</label>
                            <select name="interno" class="w-full border rounded px-3 py-2 text-black @error('interno') border-red-500 @enderror">
                                <option value="">Seleccione</option>
                                <option value="1" {{ old('interno') == '1' ? 'selected' : '' }}>Interno</option>
                                <option value="0" {{ old('interno') == '0' ? 'selected' : '' }}>Externo</option>
                            </select>
                            @error('interno') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Empleado vinculado
                            </label>

                            <select name="id_empleado"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('id_empleado') border-red-500 @enderror">
                                <option value="">Seleccione un empleado interno</option>

                                @foreach($empleados as $empleado)
                                    <option value="{{ $empleado->id_empleado }}"
                                            {{ old('id_empleado') == $empleado->id_empleado ? 'selected' : '' }}>
                                        {{ $empleado->nombre_completo }}{{ $empleado->correo ? ' - ' . $empleado->correo : '' }}
                                    </option>
                                @endforeach
                            </select>

                            @error('id_empleado')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror

                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Obligatorio para instructores internos. Para instructores externos se deja vacío por ahora.
                            </p>
                        </div>

                        <div class="mb-6">
                            <label class="block mb-1">Estado</label>
                            <select name="estado" class="w-full border rounded px-3 py-2 text-black @error('estado') border-red-500 @enderror">
                                <option value="1" {{ old('estado', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('estado') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                                Guardar
                            </button>

                            <a href="{{ route('instructores.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded">
                                Cancelar
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>