<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Editar ejercicio
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $ejercicio->titulo }}
                </p>
            </div>

            <a href="{{ route('capacitacion_modulos.ejercicios.index', $modulo->id_capacitacion_modulo) }}"
               class="px-4 py-2 bg-gray-600 text-white rounded text-sm">
                Volver a ejercicios
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="rounded border border-red-300 bg-red-100 px-4 py-3 text-red-800">
                    <strong>Revisa los siguientes errores:</strong>

                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    Editar ejercicio
                </h3>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Modifica los datos generales del ejercicio.
                </p>

                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                    <strong>Módulo:</strong> {{ $modulo->titulo }}
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <form method="POST"
                      action="{{ route('ejercicios.update', $ejercicio->id_ejercicio) }}"
                      class="space-y-5">
                    @csrf
                    @method('PUT')

                    <input type="hidden"
                        name="volver_modulo"
                        value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">

                    <div>
                        <label class="block text-sm font-medium mb-1">Título</label>
                        <input type="text" name="titulo" value="{{ old('titulo', $ejercicio->titulo) }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Descripción</label>
                        <textarea name="descripcion" rows="3" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('descripcion', $ejercicio->descripcion) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Instrucciones</label>
                        <textarea name="instrucciones" rows="4" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('instrucciones', $ejercicio->instrucciones) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Intentos máximos</label>
                            <input type="number" name="intentos_maximos" min="1" value="{{ old('intentos_maximos', $ejercicio->intentos_maximos) }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Tiempo límite en minutos</label>
                            <input type="number" name="tiempo_limite_minutos" min="1" value="{{ old('tiempo_limite_minutos', $ejercicio->tiempo_limite_minutos) }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Porcentaje para aprobar</label>
                            <input type="number" name="porcentaje_aprobacion" min="1" max="100" step="0.01" value="{{ old('porcentaje_aprobacion', $ejercicio->porcentaje_aprobacion ?? 70) }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Ubicar en sección/subsección</label>

                            <select name="id_capacitacion_modulo_seccion"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                <option value="">Contenido general del módulo</option>

                                @foreach($secciones as $seccion)
                                    <option value="{{ $seccion->id_capacitacion_modulo_seccion }}"
                                            {{ old('id_capacitacion_modulo_seccion', $ejercicio->id_capacitacion_modulo_seccion) == $seccion->id_capacitacion_modulo_seccion ? 'selected' : '' }}>
                                        {{ (int) $seccion->nivel === 2 ? '— ' : '' }}{{ $seccion->titulo }}
                                    </option>
                                @endforeach
                            </select>

                            <p class="text-xs text-gray-500 mt-1">
                                Aquí decides en qué parte del módulo aparecerá este ejercicio.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Orden</label>
                            <input type="number" name="orden" min="1" value="{{ old('orden', $ejercicio->orden) }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Obligatorio</label>
                            <select name="obligatorio" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                                <option value="1" {{ old('obligatorio', $ejercicio->obligatorio) == 1 ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('obligatorio', $ejercicio->obligatorio) == 0 ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Estado</label>
                            <select name="estado" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                                <option value="1" {{ old('estado', $ejercicio->estado) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('estado', $ejercicio->estado) == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Mostrar resultado inmediato</label>
                            <select name="mostrar_resultado_inmediato" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                                <option value="1" {{ old('mostrar_resultado_inmediato', $ejercicio->mostrar_resultado_inmediato) == 1 ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('mostrar_resultado_inmediato', $ejercicio->mostrar_resultado_inmediato) == 0 ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

<input type="hidden" name="requiere_revision_manual" value="0">
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded">
                            Guardar cambios
                        </button>

                        @php
                            $volverModuloEjercicio = (int) request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) === 1;
                            $idSeccionRetornoEjercicio = request('id_capacitacion_modulo_seccion') ?: $ejercicio->id_capacitacion_modulo_seccion;
                        @endphp

                        @if($volverModuloEjercicio && $idSeccionRetornoEjercicio)
                            <a href="{{ route('capacitacion_modulos.edit', [
                                    'id' => $modulo->id_capacitacion_modulo,
                                    'origen' => 'builder',
                                ]) }}#seccion-modulo-{{ $idSeccionRetornoEjercicio }}"
                            class="px-4 py-2 bg-gray-600 text-white rounded">
                                Cancelar
                            </a>
                        @else
                            <a href="{{ route('capacitacion_modulos.ejercicios.index', $modulo->id_capacitacion_modulo) }}"
                            class="px-4 py-2 bg-gray-600 text-white rounded">
                                Cancelar
                            </a>
                        @endif

                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>