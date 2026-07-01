<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Crear evaluación
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Módulo: {{ $modulo->titulo }}
                </p>
            </div>

            <a href="{{ route('capacitacion_modulos.evaluaciones.index', $modulo->id_capacitacion_modulo) }}"
               class="px-4 py-2 bg-gray-600 text-white rounded text-sm">
                Volver a evaluaciones
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="esf-admin-modal-card w-full max-w-4xl mx-auto p-6 sm:p-8">
                <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">
                    Crear evaluación
                </h3>

                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Completa los datos generales de la evaluación. Después podrás agregar preguntas y opciones.
                </p>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <form method="POST"
                      action="{{ route('capacitacion_modulos.evaluaciones.store', $modulo->id_capacitacion_modulo) }}"
                      class="space-y-5">
                    @csrf

                    <input type="hidden" name="origen" value="builder">
                    <input type="hidden"
                        name="volver_modulo"
                        value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">

                    <div class="esf-admin-form-full">
                        <label class="block text-sm font-medium mb-1">Título</label>
                        <input type="text" name="titulo" value="{{ old('titulo') }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                    </div>

                    <div class="esf-admin-form-full">
                        <label class="block text-sm font-medium mb-1">Descripción</label>
                        <textarea name="descripcion" rows="3" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('descripcion') }}</textarea>
                    </div>

                    <div class="esf-admin-form-full">
                        <label class="block text-sm font-medium mb-1">Instrucciones</label>
                        <textarea name="instrucciones" rows="4" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('instrucciones') }}</textarea>

                        <p class="text-xs text-gray-500 mt-1">
                            Indicaciones que verá el usuario antes de presentar la evaluación.
                        </p>
                    </div>

                    <div class="esf-admin-form-grid">
                        <div>
                            <label class="block text-sm font-medium mb-1">Intentos máximos</label>
                            <input type="number" name="intentos_maximos" min="1" value="{{ old('intentos_maximos') }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                            <p class="text-xs text-gray-500 mt-1">Si lo dejas vacío, los intentos serán ilimitados.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Tiempo límite en minutos</label>
                            <input type="number" name="tiempo_limite_minutos" min="1" value="{{ old('tiempo_limite_minutos') }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                            <p class="text-xs text-gray-500 mt-1">Si lo dejas vacío, no tendrá temporizador.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">% aprobación</label>
                            <input type="number" name="porcentaje_aprobacion" min="1" max="100" step="0.01" value="{{ old('porcentaje_aprobacion', 70) }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Ubicar en sección/subsección</label>

                            <select name="id_capacitacion_modulo_seccion"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                <option value="">Contenido general del módulo</option>

                                @foreach($secciones as $seccion)
                                    <option value="{{ $seccion->id_capacitacion_modulo_seccion }}"
                                        {{ old('id_capacitacion_modulo_seccion', request('id_capacitacion_modulo_seccion')) == $seccion->id_capacitacion_modulo_seccion ? 'selected' : '' }}>
                                        {{ (int) $seccion->nivel === 2 ? '— ' : '' }}{{ $seccion->titulo }}
                                    </option>
                                @endforeach
                            </select>

                            <p class="text-xs text-gray-500 mt-1">
                                Aquí decides en qué parte del módulo aparecerá esta evaluación.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Orden</label>
                            <input type="number" name="orden" min="1" value="{{ old('orden', $siguienteOrden ?? 1) }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Obligatorio</label>
                            <select name="obligatorio" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                                <option value="1" {{ old('obligatorio', '1') == '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('obligatorio') == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Estado</label>
                            <select name="activa" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                                <option value="1" {{ old('activa', '1') == '1' ? 'selected' : '' }}>Activa</option>
                                <option value="0" {{ old('activa') == '0' ? 'selected' : '' }}>Inactiva</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Mostrar resultado inmediato</label>
                            <select name="mostrar_resultado_inmediato" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                                <option value="1" {{ old('mostrar_resultado_inmediato', '1') == '1' ? 'selected' : '' }}>Sí</option>
                                <option value="0" {{ old('mostrar_resultado_inmediato') == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

<input type="hidden" name="requiere_revision_manual" value="0">
                    </div>

                    <div class="esf-admin-actions-footer">
                        @php
                            $volverModuloEvaluacion = (int) request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) === 1;
                            $idSeccionRetornoEvaluacion = request('id_capacitacion_modulo_seccion');
                        @endphp

                        <button type="submit" class="esf-btn esf-btn-primary">
                            Guardar evaluación
                        </button>

                        @if($volverModuloEvaluacion && $idSeccionRetornoEvaluacion)
                            <a href="{{ route('capacitacion_modulos.edit', [
                                    'id' => $modulo->id_capacitacion_modulo,
                                    'origen' => 'builder',
                                ]) }}#seccion-modulo-{{ $idSeccionRetornoEvaluacion }}"
                            class="esf-btn esf-btn-soft">
                                Cancelar
                            </a>
                        @else
                            <a href="{{ route('capacitacion_modulos.evaluaciones.index', $modulo->id_capacitacion_modulo) }}"
                            class="esf-btn esf-btn-soft">
                                Cancelar
                            </a>
                        @endif
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>