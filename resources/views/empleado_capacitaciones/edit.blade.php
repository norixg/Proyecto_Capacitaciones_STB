<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.28em] text-slate-400">
                    Asignaciones de capacitación
                </p>

                <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-slate-100">
                    Editar asignación
                </h2>

                <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                    Actualiza fechas, obligatoriedad y estado de la asignación.
                </p>
            </div>

            <a href="{{ route('empleado_capacitaciones.index') }}"
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

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-blue-100 bg-blue-50/80 px-5 py-4 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">
                        Estado actual
                    </p>

                    <p class="mt-2 text-lg font-black text-slate-900">
                        {{ ucfirst(str_replace('_', ' ', $asignacion->estado)) }}
                    </p>
                </div>

                <div class="rounded-3xl border border-emerald-100 bg-emerald-50/80 px-5 py-4 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-600">
                        Progreso
                    </p>

                    <p class="mt-2 text-lg font-black text-slate-900">
                        {{ number_format((float) $asignacion->progreso, 2) }}%
                    </p>
                </div>

                <div class="rounded-3xl border border-purple-100 bg-purple-50/80 px-5 py-4 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-purple-500">
                        Seguimiento
                    </p>

                    <p class="mt-2 text-lg font-black text-slate-900">
                        {{ $tieneSeguimiento ? 'Sí' : 'No' }}
                    </p>
                </div>
            </div>

            @if($tieneSeguimiento)
                <div class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900 shadow-sm">
                    <p class="text-sm font-bold">
                        Esta asignación ya tiene seguimiento. Por seguridad, ya no se puede cambiar el empleado, la capacitación ni el estado manualmente.
                    </p>
                </div>
            @endif

            <div class="rounded-[2rem] border border-slate-200/90 bg-white/90 shadow-xl shadow-slate-200/60 backdrop-blur dark:border-slate-700 dark:bg-slate-900/80 dark:shadow-none">
                <div class="border-b border-slate-100 px-6 py-6 dark:border-slate-800">
                    <p class="text-xs font-black uppercase tracking-[0.28em] text-slate-400">
                        Registro asignado
                    </p>

                    <h3 class="mt-2 text-xl font-black text-slate-900 dark:text-slate-100">
                        Datos de la asignación
                    </h3>

                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                        Mantén las fechas y la configuración alineadas con el seguimiento real del empleado.
                    </p>
                </div>

                <form method="POST"
                      action="{{ route('empleado_capacitaciones.update', $asignacion->id_empleado_capacitacion) }}"
                      class="px-6 py-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Empleado
                            </label>

                            @if($tieneSeguimiento)
                                <input type="hidden" name="id_empleado" value="{{ old('id_empleado', $asignacion->id_empleado) }}">
                            @endif

                            <select name="id_empleado"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('id_empleado') border-red-500 @enderror {{ $tieneSeguimiento ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                    @disabled($tieneSeguimiento)>
                                @foreach($empleados as $empleado)
                                    <option value="{{ $empleado->id_empleado }}" {{ old('id_empleado', $asignacion->id_empleado) == $empleado->id_empleado ? 'selected' : '' }}>
                                        {{ $empleado->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>

                            @error('id_empleado')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Capacitación
                            </label>

                            @if($tieneSeguimiento)
                                <input type="hidden" name="id_capacitacion" value="{{ old('id_capacitacion', $asignacion->id_capacitacion) }}">
                            @endif

                            <select name="id_capacitacion"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('id_capacitacion') border-red-500 @enderror {{ $tieneSeguimiento ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                    @disabled($tieneSeguimiento)>
                                @foreach($capacitaciones as $capacitacion)
                                    <option value="{{ $capacitacion->id_capacitacion }}" {{ old('id_capacitacion', $asignacion->id_capacitacion) == $capacitacion->id_capacitacion ? 'selected' : '' }}>
                                        {{ $capacitacion->capacitacion }}
                                    </option>
                                @endforeach
                            </select>

                            @error('id_capacitacion')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Obligatoria
                            </label>

                            <select name="obligatoria"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('obligatoria') border-red-500 @enderror">
                                <option value="1" {{ old('obligatoria', $asignacion->obligatoria) == '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('obligatoria', $asignacion->obligatoria) == '0' ? 'selected' : '' }}>No</option>
                            </select>

                            @error('obligatoria')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Fecha de asignación
                            </label>

                            <input type="date"
                                   name="fecha_asignacion"
                                   required
                                   value="{{ old('fecha_asignacion', optional($asignacion->fecha_asignacion)->format('Y-m-d')) }}"
                                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('fecha_asignacion') border-red-500 @enderror">

                            @error('fecha_asignacion')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Fecha límite
                            </label>

                            <input type="date"
                                   name="fecha_limite"
                                   required
                                   value="{{ old('fecha_limite', optional($asignacion->fecha_limite)->format('Y-m-d')) }}"
                                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('fecha_limite') border-red-500 @enderror">

                            @error('fecha_limite')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Fecha de vencimiento
                            </label>

                            <input type="date"
                                   name="fecha_vencimiento"
                                   required
                                   value="{{ old('fecha_vencimiento', optional($asignacion->fecha_vencimiento)->format('Y-m-d')) }}"
                                   class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('fecha_vencimiento') border-red-500 @enderror">

                            @error('fecha_vencimiento')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                                Estado
                            </label>

                            @if($tieneSeguimiento)
                                <input type="hidden" name="estado" value="{{ $asignacion->estado }}">
                            @endif

                            <select name="estado"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm transition focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 @error('estado') border-red-500 @enderror {{ $tieneSeguimiento ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                    @disabled($tieneSeguimiento)>
                                @if($tieneSeguimiento)
                                    <option value="{{ $asignacion->estado }}" selected>
                                        {{ ucfirst(str_replace('_', ' ', $asignacion->estado)) }}
                                    </option>
                                @else
                                    <option value="pendiente" {{ old('estado', $asignacion->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="cancelada" {{ old('estado', $asignacion->estado) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                                @endif
                            </select>

                            @error('estado')
                                <p class="mt-1 text-sm font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                        <a href="{{ route('empleado_capacitaciones.index') }}"
                           class="inline-flex items-center justify-center rounded-full bg-blue-100 px-6 py-3 text-sm font-black text-blue-800 transition hover:-translate-y-0.5 hover:bg-blue-200">
                            Cancelar
                        </a>

                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-full bg-slate-950 px-6 py-3 text-sm font-black text-white shadow-xl shadow-slate-300/70 transition hover:-translate-y-0.5 hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-950 dark:shadow-none">
                            Actualizar asignación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>