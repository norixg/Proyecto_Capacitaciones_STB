<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.28em] text-slate-400">
                    Gestión de instructores
                </p>

                <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-slate-100">
                    Editar instructor
                </h2>

                <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                    Actualiza la información de contacto, tipo y estado del instructor.
                </p>
            </div>

            <a href="{{ route('instructores.index') }}"
               class="inline-flex items-center justify-center rounded-full bg-blue-100 px-5 py-2 text-sm font-black text-blue-800 transition hover:-translate-y-0.5 hover:bg-blue-200">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

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
                        Instructor registrado
                    </p>

                    <h3 class="mt-2 text-xl font-black text-slate-900 dark:text-slate-100">
                        {{ $instructor->instructor }}
                    </h3>

                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                        Mantén actualizados los datos que se mostrarán en las capacitaciones.
                    </p>
                </div>

                <form method="POST"
                      action="{{ route('instructores.update', $instructor->id_instructor) }}"
                      class="px-6 py-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Nombre del instructor
                            </label>

                            <input type="text"
                                   name="instructor"
                                   value="{{ old('instructor', $instructor->instructor) }}"
                                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('instructor') border-red-500 @enderror">

                            @error('instructor')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror

                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Mínimo 3 caracteres.
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Correo
                            </label>

                            <input type="email"
                                   name="correo"
                                   value="{{ old('correo', $instructor->correo) }}"
                                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('correo') border-red-500 @enderror">

                            @error('correo')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Teléfono
                            </label>

                            <input type="text"
                                   name="telefono"
                                   value="{{ old('telefono', $instructor->telefono) }}"
                                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('telefono') border-red-500 @enderror">

                            @error('telefono')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror

                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Si lo ingresas, debe tener al menos 8 caracteres.
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Tipo de instructor
                            </label>

                            <select name="interno"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('interno') border-red-500 @enderror">
                                <option value="">Seleccione</option>
                                <option value="1" {{ old('interno', $instructor->interno) == '1' ? 'selected' : '' }}>Interno</option>
                                <option value="0" {{ old('interno', $instructor->interno) == '0' ? 'selected' : '' }}>Externo</option>
                            </select>

                            @error('interno')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
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
                                            {{ old('id_empleado', $instructor->id_empleado) == $empleado->id_empleado ? 'selected' : '' }}>
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

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Estado
                            </label>

                            <select name="estado"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('estado') border-red-500 @enderror">
                                <option value="1" {{ old('estado', $instructor->estado) == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('estado', $instructor->estado) == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>

                            @error('estado')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                        <a href="{{ route('instructores.index') }}"
                           class="inline-flex items-center justify-center rounded-full bg-blue-100 px-6 py-3 text-sm font-black text-blue-800 transition hover:-translate-y-0.5 hover:bg-blue-200">
                            Cancelar
                        </a>

                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-full bg-slate-950 px-6 py-3 text-sm font-black text-white shadow-xl shadow-slate-300/70 transition hover:-translate-y-0.5 hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-950 dark:shadow-none">
                            Actualizar instructor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>