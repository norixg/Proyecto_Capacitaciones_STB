<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.28em] text-slate-400">
                    Gestión de capacitaciones
                </p>

                <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-slate-100">
                    Crear nueva capacitación
                </h2>

                <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                    Registra los datos generales de la capacitación antes de construir sus módulos.
                </p>
            </div>

            <a href="{{ route('capacitaciones.index') }}"
               class="inline-flex items-center justify-center rounded-full bg-blue-100 px-5 py-2 text-sm font-black text-blue-800 transition hover:-translate-y-0.5 hover:bg-blue-200">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

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
                        Nueva capacitación
                    </p>

                    <h3 class="mt-2 text-xl font-black text-slate-900 dark:text-slate-100">
                        Datos principales
                    </h3>

                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                        Completa la información general. Después podrás organizar módulos, recursos, ejercicios y evaluaciones.
                    </p>
                </div>

                <form method="POST"
                      action="{{ route('capacitaciones.store') }}"
                      enctype="multipart/form-data"
                      class="px-6 py-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Nombre de la capacitación
                            </label>

                            <input type="text"
                                   name="capacitacion"
                                   value="{{ old('capacitacion') }}"
                                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('capacitacion') border-red-500 @enderror">

                            @error('capacitacion')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror

                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Mínimo 3 caracteres.
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Código
                            </label>

                            <input type="text"
                                   name="codigo"
                                   value="{{ old('codigo') }}"
                                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('codigo') border-red-500 @enderror">

                            @error('codigo')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Instructor
                            </label>

                            @if($esAdminCapacitacion)
                                <select name="id_instructor"
                                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('id_instructor') border-red-500 @enderror">
                                    <option value="">Seleccione</option>

                                    @foreach($instructores as $instructor)
                                        <option value="{{ $instructor->id_instructor }}"
                                                {{ old('id_instructor') == $instructor->id_instructor ? 'selected' : '' }}>
                                            {{ $instructor->instructor }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="id_instructor" value="">

                                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-black text-blue-800">
                                    {{ $instructorActual?->instructor }}
                                </div>

                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    Como instructor, la capacitación se asignará automáticamente a tu usuario.
                                </p>
                            @endif

                            @error('id_instructor')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Descripción
                            </label>

                            <textarea name="descripcion"
                                      rows="4"
                                      class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('descripcion') border-red-500 @enderror">{{ old('descripcion') }}</textarea>

                            @error('descripcion')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Portada de la capacitación
                            </label>

                            <div class="rounded-3xl border border-dashed border-blue-200 bg-blue-50/50 p-4 dark:border-blue-900/60 dark:bg-blue-950/20">
                                <input type="file"
                                       name="portada"
                                       accept="image/jpeg,image/png,image/webp"
                                       class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-black file:text-white hover:file:bg-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('portada') border-red-500 @enderror">

                                @error('portada')
                                    <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                                @enderror

                                <p class="mt-2 text-xs font-semibold text-slate-500">
                                    Opcional. Formatos permitidos: JPG, PNG o WEBP. Tamaño máximo: 4 MB.
                                </p>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Objetivo general
                            </label>

                            <textarea name="objetivo_general"
                                      rows="3"
                                      class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('objetivo_general') border-red-500 @enderror">{{ old('objetivo_general') }}</textarea>

                            @error('objetivo_general')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Horas estimadas
                            </label>

                            <input type="number"
                                   name="horas_estimadas"
                                   value="{{ old('horas_estimadas') }}"
                                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('horas_estimadas') border-red-500 @enderror">

                            @error('horas_estimadas')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Porcentaje de aprobación
                            </label>

                            <input type="number"
                                   step="0.01"
                                   name="porcentaje_aprobacion"
                                   value="{{ old('porcentaje_aprobacion', '70') }}"
                                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('porcentaje_aprobacion') border-red-500 @enderror">

                            @error('porcentaje_aprobacion')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Días de vigencia
                            </label>

                            <input type="number"
                                   name="dias_vigencia"
                                   value="{{ old('dias_vigencia') }}"
                                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('dias_vigencia') border-red-500 @enderror">

                            @error('dias_vigencia')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Obligatoria
                            </label>

                            <select name="obligatoria"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('obligatoria') border-red-500 @enderror">
                                <option value="1" {{ old('obligatoria') == '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('obligatoria', '0') == '0' ? 'selected' : '' }}>No</option>
                            </select>

                            @error('obligatoria')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Estado
                            </label>

                            <select name="estado"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('estado') border-red-500 @enderror">
                                <option value="1" {{ old('estado', '1') == '1' ? 'selected' : '' }}>Activa</option>
                                <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}>Inactiva</option>
                            </select>

                            @error('estado')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                        <a href="{{ route('capacitaciones.index') }}"
                           class="inline-flex items-center justify-center rounded-full bg-blue-100 px-6 py-3 text-sm font-black text-blue-800 transition hover:-translate-y-0.5 hover:bg-blue-200">
                            Cancelar
                        </a>

                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-full bg-slate-950 px-6 py-3 text-sm font-black text-white shadow-xl shadow-slate-300/70 transition hover:-translate-y-0.5 hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-950 dark:shadow-none">
                            Guardar capacitación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>