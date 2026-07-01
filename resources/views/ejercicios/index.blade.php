<x-app-layout>

    @php
        $volverAlModuloDesdeEjercicios = (int) request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) === 1;
        $idSeccionRetornoEjercicios = request('id_capacitacion_modulo_seccion');

        $urlRegresoEjercicios = $volverAlModuloDesdeEjercicios
            ? route('capacitacion_modulos.edit', [
                'id' => $modulo->id_capacitacion_modulo,
                'origen' => 'builder',
            ]) . ($idSeccionRetornoEjercicios ? '#seccion-modulo-' . $idSeccionRetornoEjercicios : '')
            : route('capacitaciones.builder', $modulo->capacitacion?->id_capacitacion);

        $parametrosCrearEjercicio = [
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
            'crear' => 1,
        ];

        if ($volverAlModuloDesdeEjercicios && $idSeccionRetornoEjercicios) {
            $parametrosCrearEjercicio['volver_modulo'] = 1;
            $parametrosCrearEjercicio['id_capacitacion_modulo_seccion'] = $idSeccionRetornoEjercicios;
        }
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                    Ejercicios del módulo
                </p>

                <h2 class="esf-seguimiento-title">
                    Ejercicios
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Módulo: {{ $modulo->titulo }}
                </p>
            </div>

        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded border border-green-300 bg-green-100 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded border border-red-300 bg-red-100 px-4 py-3 text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="esf-admin-sheet-card">
                <div class="flex flex-col gap-4 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                            Ejercicios registrados
                        </p>

                        <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">
                            Ejercicios del módulo
                        </h3>

                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Administra los ejercicios interactivos de este módulo: preguntas, opciones y reglas de revisión.
                        </p>

                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                            <strong>Capacitación:</strong> {{ $modulo->capacitacion?->capacitacion }}
                        </p>
                    </div>



                    <div class="flex flex-col md:flex-row gap-2">
                        <input type="text"
                            id="buscarEjercicio"
                            placeholder="Buscar ejercicio..."
                            class="min-w-[220px] rounded-full border border-slate-200 bg-white/90 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">

                        <div class="flex flex-wrap gap-2 md:justify-end">
                            <button type="button"
                                    onclick="abrirModal('modalCrearEjercicio')"
                                    class="esf-btn esf-btn-primary">
                                Crear ejercicio
                            </button>

                            <a href="{{ $urlRegresoEjercicios }}"
                            class="esf-btn esf-btn-soft">
                                Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div id="modalCrearEjercicio"
                class="fixed inset-0 z-50 hidden items-start justify-center overflow-y-auto bg-black/55 px-4 py-10">
                <div class="esf-admin-modal-card w-full max-w-4xl p-6 sm:p-8 my-8">
                    <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">
                        Crear ejercicio
                    </h3>

                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Primero guarda los datos generales del ejercicio. Luego se abrirá en esta misma pantalla para agregar preguntas y opciones.
                    </p>

                    <form method="POST"
                        action="{{ route('capacitacion_modulos.ejercicios.store', $modulo->id_capacitacion_modulo) }}"
                        class="space-y-5">
                        @csrf

                        <input type="hidden"
                            name="volver_modulo"
                            value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">

                        <div>
                            <label class="block text-sm font-medium mb-1">Título</label>
                            <input type="text"
                                name="titulo"
                                value="{{ old('titulo') }}"
                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Descripción</label>
                            <textarea name="descripcion"
                                    rows="3"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('descripcion') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Instrucciones</label>
                            <textarea name="instrucciones"
                                    rows="4"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('instrucciones') }}</textarea>
                        </div>

                        <div class="esf-admin-modal-grid">
                            <div>
                                <label class="block text-sm font-medium mb-1">Intentos máximos</label>
                                <input type="number"
                                    name="intentos_maximos"
                                    min="1"
                                    value="{{ old('intentos_maximos') }}"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                <p class="text-xs text-gray-500 mt-1">Si lo dejas vacío, los intentos serán ilimitados.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Tiempo límite en minutos</label>
                                <input type="number"
                                    name="tiempo_limite_minutos"
                                    min="1"
                                    value="{{ old('tiempo_limite_minutos') }}"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                <p class="text-xs text-gray-500 mt-1">Si lo dejas vacío, no tendrá temporizador.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Porcentaje para aprobar</label>
                                <input type="number"
                                    name="porcentaje_aprobacion"
                                    min="1"
                                    max="100"
                                    step="0.01"
                                    value="{{ old('porcentaje_aprobacion', 70) }}"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    required>
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
                                    Aquí decides en qué parte del módulo aparecerá este ejercicio.
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Orden</label>
                                <input type="number"
                                    name="orden"
                                    min="1"
                                    value="{{ old('orden', $siguienteOrdenEjercicio ?? 1) }}"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Obligatorio</label>
                                <select name="obligatorio"
                                        class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                        required>
                                    <option value="1" {{ old('obligatorio', '1') == '1' ? 'selected' : '' }}>Sí</option>
                                    <option value="0" {{ old('obligatorio') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Mostrar resultado inmediato</label>
                                <select name="mostrar_resultado_inmediato"
                                        class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                        required>
                                    <option value="1" {{ old('mostrar_resultado_inmediato', '1') == '1' ? 'selected' : '' }}>Sí</option>
                                    <option value="0" {{ old('mostrar_resultado_inmediato') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <input type="hidden"
                            name="estado"
                            value="{{ request('id_capacitacion_modulo_seccion') ? 1 : 0 }}">
                                                <input type="hidden" name="requiere_revision_manual" value="0">

                        <div class="rounded border border-yellow-300 bg-yellow-100 px-4 py-3 text-yellow-800 text-sm">
                            El ejercicio se guardará como borrador/inactivo. Podrás activarlo cuando tenga preguntas y opciones válidas.
                        </div>


                        <div class="esf-admin-actions-footer">
                            @php
                                $volverModuloEjercicio = (int) request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) === 1;
                                $idSeccionRetornoEjercicio = request('id_capacitacion_modulo_seccion');
                            @endphp

                            <button type="submit" class="esf-btn esf-btn-primary">
                                Guardar ejercicio
                            </button>

                            <button type="button"
                                    onclick="cerrarModal('modalCrearEjercicio')"
                                    class="esf-btn esf-btn-soft">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="contenedorEjercicios" class="space-y-4">
                @forelse($modulo->ejercicios as $ejercicio)
                    <details id="ejercicio-{{ $ejercicio->id_ejercicio }}"
                        class="ejercicio-card esf-learning-admin-card transition hover:-translate-y-1 hover:shadow-xl">
                        <summary class="esf-learning-admin-summary">
                            <div class="inline-flex w-full flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <p class="ejercicio-titulo font-bold text-gray-900 dark:text-gray-100">
                                        {{ $ejercicio->orden }}. {{ $ejercicio->titulo }}
                                    </p>

                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $ejercicio->preguntas->count() }} pregunta(s)
                                        · {{ (int) $ejercicio->obligatorio === 1 ? 'Obligatorio' : 'Opcional' }}
                                        · Intentos: {{ $ejercicio->intentos_maximos ?: 'Sin límite' }}
                                        · Ubicación: {{ $ejercicio->seccion?->titulo ?? 'Contenido general del módulo' }}
                                        · {{ (int) $ejercicio->estado === 1 ? 'Activo' : 'Inactivo' }}
                                    </p>
                                </div>

                                <span class="px-3 py-1 text-xs rounded-full {{ (int) $ejercicio->estado === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ (int) $ejercicio->estado === 1 ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </summary>

                        <div class="esf-learning-admin-body space-y-4">
                            <div class="esf-learning-info-panel">
                                <div class="esf-learning-info-layout">
                                    <div>
                                        <h4 class="text-lg font-black text-slate-900 dark:text-slate-100">
                                            Información del ejercicio
                                        </h4>

                                        <div class="esf-learning-info-grid mt-4">
                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Descripción:</span>
                                                {{ $ejercicio->descripcion ?: 'Sin descripción.' }}
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Orden:</span>
                                                {{ $ejercicio->orden }}
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Instrucciones:</span>
                                                {{ $ejercicio->instrucciones ?: 'Sin instrucciones.' }}
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Porcentaje aprobación:</span>
                                                {{ number_format((float) ($ejercicio->porcentaje_aprobacion ?? 70), 2) }}%
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Tiempo límite:</span>
                                                {{ $ejercicio->tiempo_limite_minutos ? $ejercicio->tiempo_limite_minutos . ' minuto(s)' : 'Sin temporizador' }}
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Intentos:</span>
                                                {{ $ejercicio->intentos_maximos ?: 'Sin límite' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="esf-learning-inline-actions">
                                        <button type="button"
                                                onclick="abrirModal('modalEditarEjercicio{{ $ejercicio->id_ejercicio }}')"
                                                class="esf-action-btn esf-action-edit justify-center text-center">
                                            Editar ejercicio
                                        </button>

                                        <form method="POST"
                                            action="{{ route('ejercicios.destroy', $ejercicio->id_ejercicio) }}"
                                            onsubmit="return confirm('¿Eliminar este ejercicio?');">
                                            @csrf
                                            @method('DELETE')

                                            <input type="hidden" name="volver_modulo" value="{{ request('volver_modulo', $ejercicio->id_capacitacion_modulo_seccion ? 1 : 0) }}">
                                            <input type="hidden" name="id_capacitacion_modulo_seccion" value="{{ request('id_capacitacion_modulo_seccion', $ejercicio->id_capacitacion_modulo_seccion) }}">

                                            <button type="submit"
                                                    class="esf-action-btn esf-action-delete w-full justify-center text-center">
                                                Eliminar ejercicio
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="esf-learning-question-panel">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h4 class="text-lg font-black text-slate-900 dark:text-slate-100">
                                            Preguntas del ejercicio
                                        </h4>

                                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            Preguntas, opciones y configuración del ejercicio.
                                        </p>
                                    </div>

                                    <button type="button"
                                            onclick="abrirModal('modalCrearPreguntaEjercicio{{ $ejercicio->id_ejercicio }}')"
                                            class="esf-btn esf-btn-primary">
                                        + Pregunta
                                    </button>
                                </div>

                                <div class="mt-4 space-y-3">
                                    @forelse($ejercicio->preguntas as $pregunta)
                                        @php
                                            $configPregunta = json_decode($pregunta->configuracion_json ?? '{}', true);
                                        @endphp

                                        <div class="esf-question-admin-card">
                                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                                <div class="flex-1">
                                                    <p class="esf-question-title">
                                                        Pregunta {{ $loop->iteration }}
                                                    </p>

                                                    <p class="esf-question-text">
                                                        {{ $pregunta->enunciado ?? $pregunta->pregunta ?? 'Sin enunciado.' }}
                                                    </p>

                                                    <p class="esf-question-meta">
                                                        Tipo: {{ ucfirst(str_replace('_', ' ', $pregunta->tipo_pregunta ?? '')) }}
                                                        · Puntaje: {{ number_format((float) ($pregunta->puntaje ?? 0), 2) }}
                                                        · Activa: {{ (int) ($pregunta->activa ?? 0) === 1 ? 'Sí' : 'No' }}
                                                    </p>

                                                    @if(in_array($pregunta->tipo_pregunta, ['opcion_unica', 'checklist_guiado'], true) && !empty($configPregunta['imagen_pregunta']))
                                                        <div class="mt-3">
                                                            <img src="{{ asset('storage/' . $configPregunta['imagen_pregunta']) }}"
                                                                alt="Imagen de la pregunta"
                                                                class="max-h-64 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
                                                        </div>
                                                    @endif

                                                    @if($pregunta->tipo_pregunta === 'actividad_visual_identificacion' && !empty($configPregunta['imagen']))
                                                        <div class="mt-3">
                                                            <img src="{{ asset('storage/' . $configPregunta['imagen']) }}"
                                                                class="max-h-48 rounded-2xl border border-slate-200 shadow-sm"
                                                                alt="Imagen de referencia">
                                                        </div>
                                                    @endif

                                                    @if($pregunta->respuesta_correcta_texto)
                                                        <p class="mt-2 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-2 text-xs font-semibold text-blue-800">
                                                            Respuesta correcta texto: {{ $pregunta->respuesta_correcta_texto }}
                                                        </p>
                                                    @endif
                                                </div>

                                                <div class="esf-question-actions">
                                                    <button type="button"
                                                            onclick="abrirModal('modalEditarPreguntaEjercicio{{ $pregunta->id_ejercicio_pregunta }}')"
                                                            class="esf-action-btn esf-action-edit">
                                                        Editar pregunta
                                                    </button>

                                                    <form method="POST"
                                                        action="{{ route('ejercicio_preguntas.destroy', $pregunta->id_ejercicio_pregunta) }}"
                                                        onsubmit="return confirm('¿Eliminar esta pregunta de ejercicio?');">
                                                        @csrf
                                                        @method('DELETE')

                                                        <input type="hidden" name="volver_modulo" value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">
                                                        <input type="hidden" name="id_capacitacion_modulo_seccion" value="{{ request('id_capacitacion_modulo_seccion', $pregunta->ejercicio->id_capacitacion_modulo_seccion) }}">

                                                        <button type="submit" class="esf-btn-danger-soft">
                                                            Eliminar pregunta
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            @if($pregunta->tipo_pregunta === 'checklist_guiado')
                                                <div class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                                                    <strong>Opción múltiple:</strong> cada opción representa una respuesta que el usuario podrá marcar.
                                                </div>
                                            @endif

                                            @if($pregunta->tipo_pregunta === 'ordenar')
                                                <div class="mt-3 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-800">
                                                    <strong>Importante:</strong> las opciones deben guardarse aquí en el orden correcto final. El usuario las verá mezcladas.
                                                </div>
                                            @endif

                                            @if($pregunta->opciones->count())
                                                <div class="esf-question-option-list">
                                                    @foreach($pregunta->opciones as $opcion)
                                                        <div class="esf-question-option-card">
                                                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                                                <div>
                                                                    <p class="text-sm font-black text-slate-900 dark:text-slate-100">
                                                                        {{ $opcion->orden }}. {{ $opcion->opcion }}
                                                                    </p>

                                                                    <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                                                        Lado: {{ $opcion->lado ?: '—' }}
                                                                        · Clave: {{ $opcion->clave_relacion ?: '—' }}
                                                                        · Correcta: {{ $opcion->es_correcta === null ? '—' : ((int) $opcion->es_correcta === 1 ? 'Sí' : 'No') }}
                                                                    </p>
                                                                </div>

                                                                <form method="POST"
                                                                    action="{{ route('ejercicio_opciones.destroy', $opcion->id_ejercicio_opcion) }}"
                                                                    onsubmit="return confirm('¿Eliminar esta opción?');">
                                                                    @csrf
                                                                    @method('DELETE')

                                                                    <button type="submit" class="esf-btn-danger-soft">
                                                                        Eliminar
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-500">
                                                    Esta pregunta todavía no tiene opciones registradas.
                                                </p>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-5 text-sm font-semibold text-slate-500">
                                            Este ejercicio todavía no tiene preguntas.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </details>

                    <div id="modalEditarEjercicio{{ $ejercicio->id_ejercicio }}"
                        class="fixed inset-0 z-50 hidden items-start justify-center overflow-y-auto bg-black/55 px-4 py-10">

                        <div class="esf-admin-modal-card w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 sm:p-8 my-8">
                            <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">
                                Editar ejercicio
                            </h3>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                Modifica los datos generales del ejercicio.
                            </p>

                            <form method="POST"
                                action="{{ route('ejercicios.update', $ejercicio->id_ejercicio) }}"
                                class="space-y-5">
                                @csrf
                                @method('PUT')

                                <input type="hidden" name="volver_modulo" value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">
                                <input type="hidden" name="requiere_revision_manual" value="0">

                                <div>
                                    <label class="block text-sm font-medium mb-1">Título</label>
                                    <input type="text"
                                        name="titulo"
                                        value="{{ old('titulo', $ejercicio->titulo) }}"
                                        class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                        required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Descripción</label>
                                    <textarea name="descripcion"
                                            rows="3"
                                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('descripcion', $ejercicio->descripcion) }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Instrucciones</label>
                                    <textarea name="instrucciones"
                                            rows="4"
                                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('instrucciones', $ejercicio->instrucciones) }}</textarea>
                                </div>

                                <div class="esf-admin-modal-grid">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Intentos máximos</label>
                                        <input type="number"
                                            name="intentos_maximos"
                                            min="1"
                                            value="{{ old('intentos_maximos', $ejercicio->intentos_maximos) }}"
                                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                        <p class="esf-help-text">Si lo dejas vacío, los intentos serán ilimitados.</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Tiempo límite en minutos</label>
                                        <input type="number"
                                            name="tiempo_limite_minutos"
                                            min="1"
                                            value="{{ old('tiempo_limite_minutos', $ejercicio->tiempo_limite_minutos) }}"
                                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                        <p class="esf-help-text">Si lo dejas vacío, no tendrá temporizador.</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Porcentaje para aprobar</label>
                                        <input type="number"
                                            name="porcentaje_aprobacion"
                                            min="1"
                                            max="100"
                                            step="0.01"
                                            value="{{ old('porcentaje_aprobacion', $ejercicio->porcentaje_aprobacion ?? 70) }}"
                                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                            required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Ubicar en sección/subsección</label>
                                        <select name="id_capacitacion_modulo_seccion"
                                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                            <option value="">Contenido general del módulo</option>

                                            @foreach($modulo->secciones->where('estado', 1) as $seccion)
                                                <option value="{{ $seccion->id_capacitacion_modulo_seccion }}"
                                                        {{ old('id_capacitacion_modulo_seccion', $ejercicio->id_capacitacion_modulo_seccion) == $seccion->id_capacitacion_modulo_seccion ? 'selected' : '' }}>
                                                    {{ (int) $seccion->nivel === 2 ? '— ' : '' }}{{ $seccion->titulo }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <p class="esf-help-text">
                                            Aquí decides en qué parte del módulo aparecerá este ejercicio.
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Orden</label>
                                        <input type="number"
                                            name="orden"
                                            min="1"
                                            value="{{ old('orden', $ejercicio->orden) }}"
                                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                            required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Obligatorio</label>
                                        <select name="obligatorio"
                                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                                required>
                                            <option value="1" {{ old('obligatorio', $ejercicio->obligatorio) == 1 ? 'selected' : '' }}>Sí</option>
                                            <option value="0" {{ old('obligatorio', $ejercicio->obligatorio) == 0 ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Estado</label>
                                        <select name="estado"
                                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                                required>
                                            <option value="1" {{ old('estado', $ejercicio->estado) == 1 ? 'selected' : '' }}>Activo</option>
                                            <option value="0" {{ old('estado', $ejercicio->estado) == 0 ? 'selected' : '' }}>Inactivo</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Mostrar resultado inmediato</label>
                                        <select name="mostrar_resultado_inmediato"
                                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                                required>
                                            <option value="1" {{ old('mostrar_resultado_inmediato', $ejercicio->mostrar_resultado_inmediato) == 1 ? 'selected' : '' }}>Sí</option>
                                            <option value="0" {{ old('mostrar_resultado_inmediato', $ejercicio->mostrar_resultado_inmediato) == 0 ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="esf-admin-actions-footer">
                                    <button type="submit" class="esf-btn esf-btn-primary">
                                        Guardar cambios
                                    </button>

                                    <button type="button"
                                            onclick="cerrarModal('modalEditarEjercicio{{ $ejercicio->id_ejercicio }}')"
                                            class="esf-btn esf-btn-soft">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="esf-admin-sheet-card p-8 text-center">
                        <p class="text-lg font-black text-slate-900 dark:text-slate-100">
                            Este módulo todavía no tiene ejercicios registrados.
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Cuando crees ejercicios, aparecerán aquí ordenados para administrarlos.
                        </p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    @foreach($modulo->ejercicios as $ejercicio)
        <div id="modalCrearPreguntaEjercicio{{ $ejercicio->id_ejercicio }}" class="modal-builder hidden fixed inset-0 z-50 bg-black bg-opacity-50 items-start justify-center overflow-y-auto p-4">
            <div class="esf-admin-modal-card w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 sm:p-8 my-8">
                <h3 class="text-xl font-bold mb-4">
                    Nueva pregunta de ejercicio: {{ $ejercicio->titulo }}
                </h3>

                <form method="POST" action="{{ route('ejercicios.preguntas.store', $ejercicio->id_ejercicio) }}" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="volver_modulo" value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">
                    <input type="hidden" name="id_capacitacion_modulo_seccion" value="{{ request('id_capacitacion_modulo_seccion', $ejercicio->id_capacitacion_modulo_seccion) }}">

                    <div class="esf-admin-modal-grid">
                        <div class="md:col-span-2 campo-enunciado-normal-ejercicio" data-scope="crear-pregunta-ejercicio-{{ $ejercicio->id_ejercicio }}">
                            <label class="block text-sm font-medium mb-1">Enunciado</label>
                            <textarea name="enunciado" rows="3" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('enunciado') }}</textarea>
                        </div>

                        <div class="esf-admin-modal-full campo-completar-amigable-ejercicio hidden" data-scope="crear-pregunta-ejercicio-{{ $ejercicio->id_ejercicio }}">
                            <label class="block text-sm font-semibold mb-1">Texto antes del espacio en blanco</label>
                            <input type="text"
                                name="completar_texto_antes"
                                value="{{ old('completar_texto_antes') }}"
                                class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                            <label class="block text-sm font-semibold mb-1 mt-3">Respuestas correctas posibles</label>
                            <textarea name="respuesta_correcta_texto"
                                    rows="4"
                                    class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    placeholder="Escribí una respuesta válida por línea. Ejemplo:
                        blanco
                        Blanco
                        color blanco">{{ old('respuesta_correcta_texto') }}</textarea>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                El usuario escribirá una sola respuesta. El sistema la tomará como correcta si coincide con cualquiera de las respuestas escritas aquí.
                            </p>

                            <label class="block text-sm font-semibold mb-1 mt-3">Texto después del espacio en blanco</label>
                            <input type="text"
                                name="completar_texto_despues"
                                value="{{ old('completar_texto_despues') }}"
                                class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Tipo de pregunta</label>
                            <select name="tipo_pregunta"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100 tipo-pregunta-ejercicio"
                                    data-scope="crear-pregunta-ejercicio-{{ $ejercicio->id_ejercicio }}"
                                    required>
                                <option value="opcion_unica">Opción única</option>
                                <option value="verdadero_falso">Verdadero / Falso</option>
                                <option value="checklist_guiado">Opción múltiple</option>
                                <option value="seleccionar_posicion_imagen">Seleccionar orden</option>
                                <option value="relacionar">Relacionar</option>
                                <option value="completar">Completar en frase</option>
                                <option value="caso_practico">Caso de estudio</option>
                                <option value="actividad_visual_identificacion">Actividad visual de identificación</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 campo-imagen-pregunta-opciones-ejercicio hidden"
                            data-scope="crear-pregunta-ejercicio-{{ $ejercicio->id_ejercicio }}">
                            <label class="block text-sm font-medium mb-1">
                                Imagen de la pregunta
                            </label>

                            <input type="file"
                                name="imagen_pregunta"
                                accept="image/*"
                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Cargar imagen (Opcional).
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Puntaje</label>
                            <input type="number"
                                name="puntaje"
                                min="0.01"
                                step="0.01"
                                value="{{ old('puntaje', '1') }}"
                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                required>
                        </div>

                        <div class="md:col-span-2 campo-caso-estudio-ejercicio hidden" data-scope="crear-pregunta-ejercicio-{{ $ejercicio->id_ejercicio }}">
                            <label class="block text-sm font-medium mb-1">Placeholder para el usuario</label>
                            <input type="text" name="caso_placeholder" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Mínimo de caracteres</label>
                                    <input type="number" name="caso_min" min="0" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Máximo de caracteres</label>
                                    <input type="number" name="caso_max" min="1" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                </div>
                            </div>

                            <label class="block text-sm font-medium mb-1 mt-3">Criterios de revisión para el administrador</label>
                            <textarea name="caso_criterios_revision" rows="3" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"></textarea>
                        </div>

                        <div class="md:col-span-2 campo-visual-identificacion-ejercicio hidden" data-scope="crear-pregunta-ejercicio-{{ $ejercicio->id_ejercicio }}">
                            <label class="block text-sm font-medium mb-1">Imagen de referencia</label>
                            <input type="file" name="visual_imagen" accept="image/*" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                            <label class="block text-sm font-medium mb-1 mt-3">Texto de apoyo</label>
                            <input type="text" name="visual_texto_apoyo" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                        </div>

                       <div class="md:col-span-2 campo-posicion-imagen-ejercicio hidden"
                            data-scope="crear-pregunta-ejercicio-{{ $ejercicio->id_ejercicio }}">
                            <div class="rounded-2xl border border-sky-200 bg-sky-50/70 dark:bg-gray-900 p-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold mb-1">
                                        Imagen de apoyo opcional
                                    </label>

                                    <input type="file"
                                        name="posicion_imagen"
                                        accept="image/*"
                                        class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        Cargar imagen (Opcional)
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold mb-1">
                                        Cantidad de números de orden
                                    </label>

                                    <input type="number"
                                        name="posicion_cantidad"
                                        min="1"
                                        max="50"
                                        value="{{ old('posicion_cantidad') }}"
                                        class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                        placeholder="Ejemplo: 5">
                                </div>

                                <div class="rounded-xl border border-sky-200 bg-white dark:bg-gray-800 px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                                    Para este tipo de pregunta, en las opciones escribe cada campo, acción o elemento que el usuario deberá ordenar.
                                    En el campo <strong>Orden</strong> coloca el número correcto.
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Orden</label>
                            <input type="number" name="orden" min="1" value="{{ $ejercicio->preguntas->count() + 1 }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Activa</label>
                            <select name="activa" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>

<input type="hidden" name="requiere_revision_manual" value="0">

                    <div class="md:col-span-2 bloque-opciones-iniciales-ejercicio hidden"
                        data-scope="crear-pregunta-ejercicio-{{ $ejercicio->id_ejercicio }}">

                        <div class="esf-options-admin-box esf-admin-modal-full">
                            <div class="esf-admin-options-box flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                                <div>
                                    <h4 class="font-bold text-sm text-gray-900 dark:text-gray-100">
                                        Opciones de la pregunta
                                    </h4>

                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Agrega únicamente las opciones necesarias. Los campos cambian según el tipo de pregunta.
                                    </p>
                                </div>

                                <button type="button"
                                        class="btn-agregar-opcion-inicial esf-btn esf-btn-primary"
                                        data-scope="crear-pregunta-ejercicio-{{ $ejercicio->id_ejercicio }}"
                                        data-next-index="0">
                                    + Opción
                                </button>
                            </div>

                            <div class="bloque-verdadero-falso-inicial hidden rounded border bg-white dark:bg-gray-800 p-3 mb-4"
                                data-scope="crear-pregunta-ejercicio-{{ $ejercicio->id_ejercicio }}">

                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">
                                    Opciones fijas para Verdadero / Falso
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="rounded border p-3">
                                        <p class="text-sm font-semibold mb-2">Verdadero</p>

                                        <input type="hidden"
                                            class="campo-vf-inicial"
                                            name="opciones_iniciales[0][opcion]"
                                            value="Verdadero"
                                            disabled>

                                        <input type="hidden"
                                            class="campo-vf-inicial"
                                            name="opciones_iniciales[0][lado]"
                                            value=""
                                            disabled>

                                        <input type="hidden"
                                            class="campo-vf-inicial"
                                            name="opciones_iniciales[0][clave_relacion]"
                                            value=""
                                            disabled>

                                        <input type="hidden"
                                            class="campo-vf-inicial"
                                            name="opciones_iniciales[0][orden]"
                                            value="1"
                                            disabled>

                                        <label class="block text-sm font-medium mb-1">
                                            ¿Es correcta?
                                        </label>

                                        <select name="opciones_iniciales[0][es_correcta]"
                                                class="campo-vf-inicial w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                                disabled>
                                            <option value="1">Sí</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>

                                    <div class="rounded border p-3">
                                        <p class="text-sm font-semibold mb-2">Falso</p>

                                        <input type="hidden"
                                            class="campo-vf-inicial"
                                            name="opciones_iniciales[1][opcion]"
                                            value="Falso"
                                            disabled>

                                        <input type="hidden"
                                            class="campo-vf-inicial"
                                            name="opciones_iniciales[1][lado]"
                                            value=""
                                            disabled>

                                        <input type="hidden"
                                            class="campo-vf-inicial"
                                            name="opciones_iniciales[1][clave_relacion]"
                                            value=""
                                            disabled>

                                        <input type="hidden"
                                            class="campo-vf-inicial"
                                            name="opciones_iniciales[1][orden]"
                                            value="2"
                                            disabled>

                                        <label class="block text-sm font-medium mb-1">
                                            ¿Es correcta?
                                        </label>

                                        <select name="opciones_iniciales[1][es_correcta]"
                                                class="campo-vf-inicial w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                                disabled>
                                            <option value="0">No</option>
                                            <option value="1">Sí</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="contenedor-opciones-iniciales-ejercicio space-y-4"
                                data-scope="crear-pregunta-ejercicio-{{ $ejercicio->id_ejercicio }}">
                            </div>
                        </div>
                    </div>

                    </div>

                    <div class="esf-admin-actions-footer esf-admin-modal-full">
                        <button type="button"
                                onclick="cerrarModal('modalCrearPreguntaEjercicio{{ $ejercicio->id_ejercicio }}')"
                                class="esf-btn esf-btn-soft">
                            Cancelar
                        </button>

                        <button type="submit"
                                class="esf-btn esf-btn-primary">
                            Guardar pregunta
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @foreach($ejercicio->preguntas as $pregunta)
            @php
                $configPregunta = json_decode($pregunta->configuracion_json ?? '{}', true);
                $partesCompletar = explode('[[blank]]', $pregunta->enunciado);
                $textoAntesCompletar = trim($partesCompletar[0] ?? '');
                $textoDespuesCompletar = trim($partesCompletar[1] ?? '');
            @endphp

            <div id="modalEditarPreguntaEjercicio{{ $pregunta->id_ejercicio_pregunta }}"
                class="fixed inset-0 z-50 hidden items-start justify-center overflow-y-auto bg-black/55 px-4 py-10">
                <div class="esf-admin-modal-card w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 sm:p-8 my-8">
                    <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">
                        Editar pregunta y opciones del ejercicio
                    </h3>

                    <form method="POST" action="{{ route('ejercicio_preguntas.update', $pregunta->id_ejercicio_pregunta) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="volver_modulo" value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">
                        <input type="hidden" name="id_capacitacion_modulo_seccion" value="{{ request('id_capacitacion_modulo_seccion', $pregunta->ejercicio->id_capacitacion_modulo_seccion) }}">

                        <div class="esf-admin-modal-grid">
                            <div class="esf-admin-modal-full campo-enunciado-normal-ejercicio" data-scope="editar-pregunta-ejercicio-{{ $pregunta->id_ejercicio_pregunta }}">
                                <label class="block text-sm font-medium mb-1">Enunciado</label>
                                <textarea name="enunciado" rows="3" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('enunciado', $pregunta->tipo_pregunta === 'completar' ? $textoAntesCompletar : $pregunta->enunciado) }}</textarea>
                            </div>

                            <div class="md:col-span-2 campo-completar-amigable-ejercicio hidden" data-scope="editar-pregunta-ejercicio-{{ $pregunta->id_ejercicio_pregunta }}">
                                <label class="block text-sm font-semibold mb-1">Texto antes del espacio en blanco</label>
                                <input type="text"
                                    name="completar_texto_antes"
                                    value="{{ old('completar_texto_antes', $textoAntesCompletar) }}"
                                    class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                                <label class="block text-sm font-semibold mb-1 mt-3">Respuestas correctas posibles</label>
                                <textarea name="respuesta_correcta_texto"
                                        rows="4"
                                        class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                        placeholder="Escribí una respuesta válida por línea.">{{ old('respuesta_correcta_texto', $pregunta->respuesta_correcta_texto) }}</textarea>

                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    El sistema aceptará cualquiera de estas respuestas como válida.
                                </p>

                                <label class="block text-sm font-semibold mb-1 mt-3">Texto después del espacio en blanco</label>
                                <input type="text"
                                    name="completar_texto_despues"
                                    value="{{ old('completar_texto_despues', $textoDespuesCompletar) }}"
                                    class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Tipo de pregunta</label>
                                <select name="tipo_pregunta"
                                        class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100 tipo-pregunta-ejercicio"
                                        data-scope="editar-pregunta-ejercicio-{{ $pregunta->id_ejercicio_pregunta }}"
                                        required>
                                    <option value="opcion_unica" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) === 'opcion_unica' ? 'selected' : '' }}>Opción única</option>
                                    <option value="verdadero_falso" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) === 'verdadero_falso' ? 'selected' : '' }}>Verdadero / Falso</option>
                                    <option value="checklist_guiado" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) === 'checklist_guiado' ? 'selected' : '' }}>Opción múltiple</option>
                                    <option value="seleccionar_posicion_imagen" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) === 'seleccionar_posicion_imagen' ? 'selected' : '' }}>Seleccionar orden</option>
                                    <option value="relacionar" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) === 'relacionar' ? 'selected' : '' }}>Relacionar</option>
                                    <option value="completar" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) === 'completar' ? 'selected' : '' }}>Completar en frase</option>
                                    <option value="caso_practico" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) === 'caso_practico' ? 'selected' : '' }}>Caso de estudio</option>
                                    <option value="actividad_visual_identificacion" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) === 'actividad_visual_identificacion' ? 'selected' : '' }}>Actividad visual de identificación</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 campo-imagen-pregunta-opciones-ejercicio hidden"
                                data-scope="editar-pregunta-ejercicio-{{ $pregunta->id_ejercicio_pregunta }}">
                                <label class="block text-sm font-medium mb-1">
                                    Imagen de la pregunta
                                </label>

                                <input type="file"
                                    name="imagen_pregunta"
                                    accept="image/*"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                                @if(!empty($configPregunta['imagen_pregunta']))
                                    <div class="mt-3">
                                        <img src="{{ asset('storage/' . $configPregunta['imagen_pregunta']) }}"
                                            alt="Imagen actual de la pregunta"
                                            class="max-h-64 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">

                                        <label class="mt-2 inline-flex items-center gap-2 text-xs font-bold text-red-700">
                                            <input type="checkbox"
                                                name="quitar_imagen_pregunta"
                                                value="1">
                                            Quitar imagen actual de la pregunta
                                        </label>
                                    </div>
                                @endif

                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    Esta imagen aparecerá arriba de las opciones de la pregunta.
                                </p>
                            </div>


                             <div>
                                <label class="block text-sm font-medium">Puntaje</label>
                                <input type="number"
                                    name="puntaje"
                                    min="0.01"
                                    step="0.01"
                                    value="{{ old('puntaje', $pregunta->puntaje) }}"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    required>
                            </div>

                            <div class="md:col-span-2 campo-respuesta-breve-ejercicio hidden" data-scope="editar-pregunta-ejercicio-{{ $pregunta->id_ejercicio_pregunta }}">
                                <label class="block text-sm font-medium mb-1">Placeholder para el usuario</label>
                                <input type="text" name="respuesta_breve_placeholder" value="{{ old('respuesta_breve_placeholder', $configPregunta['placeholder'] ?? '') }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                                <div class="esf-admin-modal-grid">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Mínimo de caracteres</label>
                                        <input type="number" name="respuesta_breve_min" value="{{ old('respuesta_breve_min', $configPregunta['min_caracteres'] ?? '') }}" min="0" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Máximo de caracteres</label>
                                        <input type="number" name="respuesta_breve_max" value="{{ old('respuesta_breve_max', $configPregunta['max_caracteres'] ?? '') }}" min="1" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-2 campo-caso-estudio-ejercicio hidden" data-scope="editar-pregunta-ejercicio-{{ $pregunta->id_ejercicio_pregunta }}">
                                <label class="block text-sm font-medium mb-1">Placeholder para el usuario</label>
                                <input type="text" name="caso_placeholder" value="{{ old('caso_placeholder', $configPregunta['placeholder'] ?? '') }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Mínimo de caracteres</label>
                                        <input type="number" name="caso_min" value="{{ old('caso_min', $configPregunta['min_caracteres'] ?? '') }}" min="0" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Máximo de caracteres</label>
                                        <input type="number" name="caso_max" value="{{ old('caso_max', $configPregunta['max_caracteres'] ?? '') }}" min="1" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                    </div>
                                </div>

                                <label class="block text-sm font-medium mb-1 mt-3">Criterios de revisión</label>
                                <textarea name="caso_criterios_revision" rows="3" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('caso_criterios_revision', $configPregunta['criterios_revision'] ?? '') }}</textarea>
                            </div>

                            <div class="md:col-span-2 campo-visual-identificacion-ejercicio hidden" data-scope="editar-pregunta-ejercicio-{{ $pregunta->id_ejercicio_pregunta }}">
                                <label class="block text-sm font-medium mb-1">Reemplazar imagen de referencia</label>
                                <input type="file" name="visual_imagen" accept="image/*" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                                @if(!empty($configPregunta['imagen']))
                                    <div class="mt-3">
                                        <p class="text-sm font-medium mb-2">Imagen actual</p>
                                        <img src="{{ asset('storage/' . $configPregunta['imagen']) }}" class="max-h-48 rounded border">
                                    </div>
                                @endif

                                <label class="block text-sm font-medium mb-1 mt-3">Texto de apoyo</label>
                                <input type="text" name="visual_texto_apoyo" value="{{ old('visual_texto_apoyo', $configPregunta['texto_apoyo'] ?? '') }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                            </div>

                            <div class="md:col-span-2 campo-posicion-imagen-ejercicio hidden"
                                data-scope="editar-pregunta-ejercicio-{{ $pregunta->id_ejercicio_pregunta }}">
                                <div class="rounded-2xl border border-sky-200 bg-sky-50/70 dark:bg-gray-900 p-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold mb-1">
                                            Reemplazar imagen de apoyo opcional
                                        </label>

                                        <input type="file"
                                            name="posicion_imagen"
                                            accept="image/*"
                                            class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            Si no seleccionás una nueva imagen, se conserva la imagen actual. Si esta pregunta no necesita imagen, podés dejar este campo vacío.
                                        </p>
                                    </div>

                                    @if(!empty($configPregunta['imagen']))
                                        <div>
                                            <p class="text-sm font-semibold mb-2">Imagen actual</p>
                                            <img src="{{ asset('storage/' . $configPregunta['imagen']) }}"
                                                class="max-h-56 rounded-xl border bg-white p-2">
                                        </div>
                                    @endif

                                    <div>
                                        <label class="block text-sm font-semibold mb-1">
                                            Cantidad de números de orden
                                        </label>

                                        <input type="number"
                                            name="posicion_cantidad"
                                            min="1"
                                            max="50"
                                            value="{{ old('posicion_cantidad', $configPregunta['cantidad_posiciones'] ?? $pregunta->opciones->count()) }}"
                                            class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                            placeholder="Ejemplo: 5">
                                    </div>

                                    <div class="rounded-xl border border-sky-200 bg-white dark:bg-gray-800 px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                                        Para este tipo de pregunta, cada opción representa un campo, acción o elemento que el usuario deberá ordenar.
                                        El campo <strong>Orden</strong> debe ser el número correcto.
                                        La imagen es opcional.
                                    </div>
                                </div>
                            </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Orden</label>
                                <input type="number" name="orden" min="1" value="{{ old('orden', $pregunta->orden) }}" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium">Activa</label>
                                <select name="activa" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" required>
                                    <option value="1" {{ old('activa', $pregunta->activa) == 1 ? 'selected' : '' }}>Sí</option>
                                    <option value="0" {{ old('activa', $pregunta->activa) == 0 ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
<input type="hidden" name="requiere_revision_manual" value="{{ in_array($pregunta->tipo_pregunta, ['respuesta_corta', 'caso_practico', 'actividad_visual_identificacion'], true) ? 1 : 0 }}">


                            <div class="md:col-span-2 bloque-opciones-edicion-ejercicio"
                                data-scope="editar-pregunta-ejercicio-{{ $pregunta->id_ejercicio_pregunta }}">

                                <div class="esf-options-admin-box">
                                    <div class="esf-admin-options-box esf-admin-modal-full flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                                        <div>
                                            <h4 class="font-bold text-sm text-gray-900 dark:text-gray-100">
                                                Opciones de la pregunta
                                            </h4>

                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Edita las opciones actuales o agrega nuevas opciones según el tipo de pregunta.
                                            </p>
                                        </div>

                                        <button type="button"
                                                class="btn-agregar-opcion-edicion esf-btn esf-btn-primary"
                                                data-scope="editar-pregunta-ejercicio-{{ $pregunta->id_ejercicio_pregunta }}"
                                                data-next-index="{{ $pregunta->opciones->count() }}">
                                            + Opción
                                        </button>
                                    </div>

                                    <div class="bloque-verdadero-falso-edicion hidden rounded-2xl border border-slate-200 bg-white/90 p-4 mb-4 shadow-sm"
                                        data-scope="editar-pregunta-ejercicio-{{ $pregunta->id_ejercicio_pregunta }}">

                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">
                                            Opciones fijas para Verdadero / Falso
                                        </p>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            @php
                                                $opcionVerdadero = $pregunta->opciones->firstWhere('opcion', 'Verdadero');
                                                $opcionFalso = $pregunta->opciones->firstWhere('opcion', 'Falso');
                                            @endphp

                                            <div class="rounded border p-3">
                                                <p class="text-sm font-semibold mb-2">Verdadero</p>

                                                <input type="hidden"
                                                    class="campo-vf-edicion"
                                                    name="opciones_existentes[{{ $opcionVerdadero?->id_ejercicio_opcion ?? 'nuevo_verdadero' }}][id_ejercicio_opcion]"
                                                    value="{{ $opcionVerdadero?->id_ejercicio_opcion }}"
                                                    disabled>

                                                <input type="hidden"
                                                    class="campo-vf-edicion"
                                                    name="opciones_existentes[{{ $opcionVerdadero?->id_ejercicio_opcion ?? 'nuevo_verdadero' }}][opcion]"
                                                    value="Verdadero"
                                                    disabled>

                                                <input type="hidden"
                                                    class="campo-vf-edicion"
                                                    name="opciones_existentes[{{ $opcionVerdadero?->id_ejercicio_opcion ?? 'nuevo_verdadero' }}][orden]"
                                                    value="1"
                                                    disabled>

                                                <label class="block text-sm font-medium mb-1">
                                                    ¿Es correcta?
                                                </label>

                                                <select name="opciones_existentes[{{ $opcionVerdadero?->id_ejercicio_opcion ?? 'nuevo_verdadero' }}][es_correcta]"
                                                        class="campo-vf-edicion w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                                        disabled>
                                                    <option value="1" {{ (int) ($opcionVerdadero?->es_correcta ?? 0) === 1 ? 'selected' : '' }}>Sí</option>
                                                    <option value="0" {{ (int) ($opcionVerdadero?->es_correcta ?? 0) === 0 ? 'selected' : '' }}>No</option>
                                                </select>
                                            </div>

                                            <div class="rounded border p-3">
                                                <p class="text-sm font-semibold mb-2">Falso</p>

                                                <input type="hidden"
                                                    class="campo-vf-edicion"
                                                    name="opciones_existentes[{{ $opcionFalso?->id_ejercicio_opcion ?? 'nuevo_falso' }}][id_ejercicio_opcion]"
                                                    value="{{ $opcionFalso?->id_ejercicio_opcion }}"
                                                    disabled>

                                                <input type="hidden"
                                                    class="campo-vf-edicion"
                                                    name="opciones_existentes[{{ $opcionFalso?->id_ejercicio_opcion ?? 'nuevo_falso' }}][opcion]"
                                                    value="Falso"
                                                    disabled>

                                                <input type="hidden"
                                                    class="campo-vf-edicion"
                                                    name="opciones_existentes[{{ $opcionFalso?->id_ejercicio_opcion ?? 'nuevo_falso' }}][orden]"
                                                    value="2"
                                                    disabled>

                                                <label class="block text-sm font-medium mb-1">
                                                    ¿Es correcta?
                                                </label>

                                                <select name="opciones_existentes[{{ $opcionFalso?->id_ejercicio_opcion ?? 'nuevo_falso' }}][es_correcta]"
                                                        class="campo-vf-edicion w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                                        disabled>
                                                    <option value="0" {{ (int) ($opcionFalso?->es_correcta ?? 0) === 0 ? 'selected' : '' }}>No</option>
                                                    <option value="1" {{ (int) ($opcionFalso?->es_correcta ?? 0) === 1 ? 'selected' : '' }}>Sí</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="contenedor-opciones-edicion-ejercicio space-y-4"
                                        data-scope="editar-pregunta-ejercicio-{{ $pregunta->id_ejercicio_pregunta }}">

                                        @foreach($pregunta->opciones as $opcionExistente)
                                            <div class="fila-opcion-edicion rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm"
                                                data-scope="editar-pregunta-ejercicio-{{ $pregunta->id_ejercicio_pregunta }}">

                                                <div class="flex items-center justify-between mb-2">
                                                    <p class="font-semibold text-xs text-gray-500">
                                                        Opción {{ $loop->iteration }}
                                                    </p>

                                                    <label class="inline-flex items-center gap-2 text-xs text-red-700">
                                                        <input type="checkbox"
                                                            name="opciones_existentes[{{ $opcionExistente->id_ejercicio_opcion }}][eliminar]"
                                                            value="1">
                                                        Eliminar
                                                    </label>
                                                </div>

                                                <input type="hidden"
                                                    name="opciones_existentes[{{ $opcionExistente->id_ejercicio_opcion }}][id_ejercicio_opcion]"
                                                    value="{{ $opcionExistente->id_ejercicio_opcion }}">

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    <div class="md:col-span-2">
                                                        <label class="block text-sm font-medium mb-1">
                                                            Texto de la opción/campo a ordenar
                                                        </label>

                                                        <textarea name="opciones_existentes[{{ $opcionExistente->id_ejercicio_opcion }}][opcion]"
                                                                rows="2"
                                                                required
                                                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('opciones_existentes.' . $opcionExistente->id_ejercicio_opcion . '.opcion', $opcionExistente->opcion) }}</textarea>
                                                    </div>

                                                    <div class="campo-opcion-relacion-edicion hidden">
                                                        <label class="block text-sm font-medium mb-1">
                                                            Lado
                                                        </label>

                                                        <select name="opciones_existentes[{{ $opcionExistente->id_ejercicio_opcion }}][lado]"
                                                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                                            <option value="">Seleccione...</option>
                                                            <option value="izquierda" {{ old('opciones_existentes.' . $opcionExistente->id_ejercicio_opcion . '.lado', $opcionExistente->lado) === 'izquierda' ? 'selected' : '' }}>Izquierda</option>
                                                            <option value="derecha" {{ old('opciones_existentes.' . $opcionExistente->id_ejercicio_opcion . '.lado', $opcionExistente->lado) === 'derecha' ? 'selected' : '' }}>Derecha</option>
                                                        </select>
                                                    </div>

                                                    <div class="campo-opcion-relacion-edicion hidden">
                                                        <label class="block text-sm font-medium mb-1">
                                                            Pareja número
                                                        </label>

                                                        <input type="text"
                                                            name="opciones_existentes[{{ $opcionExistente->id_ejercicio_opcion }}][clave_relacion]"
                                                            value="{{ old('opciones_existentes.' . $opcionExistente->id_ejercicio_opcion . '.clave_relacion', $opcionExistente->clave_relacion) }}"
                                                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                                    </div>

                                                    <div class="campo-opcion-correcta-edicion hidden">
                                                        <label class="block text-sm font-medium mb-1">
                                                            ¿Es correcta?
                                                        </label>

                                                        <select name="opciones_existentes[{{ $opcionExistente->id_ejercicio_opcion }}][es_correcta]"
                                                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                                            <option value="0" {{ old('opciones_existentes.' . $opcionExistente->id_ejercicio_opcion . '.es_correcta', $opcionExistente->es_correcta) == 0 ? 'selected' : '' }}>No</option>
                                                            <option value="1" {{ old('opciones_existentes.' . $opcionExistente->id_ejercicio_opcion . '.es_correcta', $opcionExistente->es_correcta) == 1 ? 'selected' : '' }}>Sí</option>
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label class="block text-sm font-medium mb-1">
                                                            Orden
                                                        </label>

                                                        <input type="number"
                                                            name="opciones_existentes[{{ $opcionExistente->id_ejercicio_opcion }}][orden]"
                                                            min="1"
                                                            value="{{ old('opciones_existentes.' . $opcionExistente->id_ejercicio_opcion . '.orden', $opcionExistente->orden) }}"
                                                            required
                                                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="esf-admin-actions-footer">
                            <button type="button"
                                    onclick="cerrarModal('modalEditarPreguntaEjercicio{{ $pregunta->id_ejercicio_pregunta }}')"
                                    class="esf-btn esf-btn-soft">
                                Cancelar
                            </button>

                            <button type="submit"
                                    class="esf-btn esf-btn-primary">
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>



        @endforeach
    @endforeach

    <script>
        function abrirModal(id) {
            const modal = document.getElementById(id);

            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function cerrarModal(id) {
            const modal = document.getElementById(id);

            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {

            const crearEjercicioDesdeSubseccion = new URLSearchParams(window.location.search).get('crear');

            if (crearEjercicioDesdeSubseccion === '1') {
                abrirModal('modalCrearEjercicio');
            }

            const detalleAbierto = new URLSearchParams(window.location.search).get('open');

            if (detalleAbierto) {
                const detalle = document.getElementById(detalleAbierto);

                if (detalle && detalle.tagName.toLowerCase() === 'details') {
                    detalle.open = true;
                    detalle.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
            const input = document.getElementById('buscarEjercicio');
            const tarjetas = document.querySelectorAll('.ejercicio-card');

            if (input) {
                input.addEventListener('input', function () {
                    const texto = this.value.toLowerCase().trim();

                    tarjetas.forEach(function (tarjeta) {
                        const titulo = tarjeta.querySelector('.ejercicio-titulo')?.textContent.toLowerCase() || '';
                        tarjeta.style.display = titulo.includes(texto) ? '' : 'none';
                    });
                });
            }

            @if((int) request('crear') === 1)
                abrirModal('modalCrearEjercicio');
            @endif

            function habilitarCampos(contenedor, habilitar) {
                if (!contenedor) return;

                contenedor.querySelectorAll('input, textarea, select').forEach(function (campo) {
                    campo.disabled = !habilitar;
                });
            }

            function configurarFilaOpcionInicial(fila, tipo) {
                const camposCorrecta = fila.querySelectorAll('.campo-opcion-correcta-inicial');
                const camposRelacion = fila.querySelectorAll('.campo-opcion-relacion-inicial');

                camposCorrecta.forEach(function (campo) {
                    campo.classList.add('hidden');
                    campo.querySelectorAll('select, input').forEach(function (input) {
                        input.disabled = true;
                    });
                });

                camposRelacion.forEach(function (campo) {
                    campo.classList.add('hidden');
                    campo.querySelectorAll('select, input').forEach(function (input) {
                        input.disabled = true;
                    });
                });

                if (['opcion_unica', 'checklist_guiado'].includes(tipo)) {
                    camposCorrecta.forEach(function (campo) {
                        campo.classList.remove('hidden');
                        campo.querySelectorAll('select, input').forEach(function (input) {
                            input.disabled = false;
                        });
                    });
                }

                if (tipo === 'relacionar') {
                    camposRelacion.forEach(function (campo) {
                        campo.classList.remove('hidden');
                        campo.querySelectorAll('select, input').forEach(function (input) {
                            input.disabled = false;
                        });
                    });
                }
            }

            function crearFilaOpcionInicial(scope, tipo, indice) {
                const fila = document.createElement('div');
                fila.dataset.scope = scope;

                if (tipo === 'relacionar') {
                    const pareja = Math.floor(indice / 2) + 1;
                    const indiceIzquierda = indice;
                    const indiceDerecha = indice + 1;

                    fila.className = 'fila-opcion-inicial rounded-2xl border border-sky-200 bg-white/95 p-4 shadow-sm';

                    fila.innerHTML =
                        '<div class="flex items-center justify-between gap-3 mb-4">' +
                            '<div>' +
                                '<p class="text-xs font-black uppercase tracking-[0.18em] text-sky-500">Pareja ' + pareja + '</p>' +
                                '<p class="text-sm font-bold text-slate-800">Relacionar izquierda con derecha</p>' +
                            '</div>' +
                            '<button type="button" class="btn-quitar-opcion-inicial px-3 py-1.5 rounded-full bg-red-100 text-red-700 border border-red-200 text-xs font-black hover:bg-red-200 transition">Quitar</button>' +
                        '</div>' +

                        '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">' +
                            '<div class="rounded-2xl border border-blue-200 bg-blue-50/70 p-4">' +
                                '<label class="block text-sm font-bold mb-1 text-slate-700">Elemento del lado izquierdo</label>' +
                                '<textarea name="opciones_iniciales[' + indiceIzquierda + '][opcion]" rows="3" required class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100" placeholder="Ejemplo: blanco"></textarea>' +

                                '<input type="hidden" name="opciones_iniciales[' + indiceIzquierda + '][lado]" value="izquierda">' +
                                '<input type="hidden" name="opciones_iniciales[' + indiceIzquierda + '][clave_relacion]" value="' + pareja + '">' +
                                '<input type="hidden" name="opciones_iniciales[' + indiceIzquierda + '][orden]" value="' + pareja + '">' +
                            '</div>' +

                            '<div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4">' +
                                '<label class="block text-sm font-bold mb-1 text-slate-700">Elemento del lado derecho</label>' +
                                '<textarea name="opciones_iniciales[' + indiceDerecha + '][opcion]" rows="3" required class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100" placeholder="Ejemplo: negro"></textarea>' +

                                '<input type="hidden" name="opciones_iniciales[' + indiceDerecha + '][lado]" value="derecha">' +
                                '<input type="hidden" name="opciones_iniciales[' + indiceDerecha + '][clave_relacion]" value="' + pareja + '">' +
                                '<input type="hidden" name="opciones_iniciales[' + indiceDerecha + '][orden]" value="' + pareja + '">' +
                            '</div>' +
                        '</div>' +

                        '<p class="mt-3 text-xs font-semibold text-slate-500">Estos dos textos quedan vinculados automáticamente como pareja ' + pareja + '.</p>';

                    fila.querySelector('.btn-quitar-opcion-inicial').addEventListener('click', function () {
                        fila.remove();
                    });

                    return fila;
                }

                fila.className = 'fila-opcion-inicial rounded border bg-white dark:bg-gray-800 p-3';

                fila.innerHTML =
                    '<div class="flex items-center justify-between mb-2">' +
                        '<p class="font-semibold text-xs text-gray-500">Opción ' + (indice + 1) + '</p>' +
                        '<button type="button" class="btn-quitar-opcion-inicial px-2 py-1 bg-red-600 text-white rounded text-xs">Quitar</button>' +
                    '</div>' +

                    '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">' +
                        '<div class="md:col-span-2">' +
                            '<label class="block text-sm font-medium mb-1">/campo a ordenar</label>' +
                            '<textarea name="opciones_iniciales[' + indice + '][opcion]" rows="2" required class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"></textarea>' +
                        '</div>' +

                        '<div class="campo-opcion-relacion-inicial hidden">' +
                            '<label class="block text-sm font-medium mb-1">Columna a la que pertenece</label>' +
                            '<select name="opciones_iniciales[' + indice + '][lado]" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" disabled>' +
                                '<option value="">Seleccione...</option>' +
                                '<option value="izquierda">Izquierda</option>' +
                                '<option value="derecha">Derecha</option>' +
                            '</select>' +
                        '</div>' +

                        '<div class="campo-opcion-relacion-inicial hidden">' +
                            '<label class="block text-sm font-medium mb-1">Pareja número</label>' +
                            '<input type="number" name="opciones_iniciales[' + indice + '][clave_relacion]" min="1" value="' + (indice + 1) + '" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" disabled>' +
                            '<p class="mt-1 text-xs text-slate-500">Usá el mismo número para la opción izquierda y su opción derecha.</p>' +
                        '</div>' +

                        '<div class="campo-opcion-correcta-inicial hidden">' +
                            '<label class="block text-sm font-medium mb-1">¿Es correcta?</label>' +
                            '<select name="opciones_iniciales[' + indice + '][es_correcta]" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" disabled>' +
                                '<option value="0">No</option>' +
                                '<option value="1">Sí</option>' +
                            '</select>' +
                        '</div>' +

                        '<div>' +
                            '<label class="block text-sm font-medium mb-1">Orden</label>' +
                            '<input type="number" name="opciones_iniciales[' + indice + '][orden]" min="1" value="' + (indice + 1) + '" required class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">' +
                        '</div>' +
                    '</div>';

                configurarFilaOpcionInicial(fila, tipo);

                fila.querySelector('.btn-quitar-opcion-inicial').addEventListener('click', function () {
                    fila.remove();
                });

                return fila;
            }

            function configurarFilaOpcionEdicion(fila, tipo) {
                const camposCorrecta = fila.querySelectorAll('.campo-opcion-correcta-edicion');
                const camposRelacion = fila.querySelectorAll('.campo-opcion-relacion-edicion');

                camposCorrecta.forEach(function (campo) {
                    campo.classList.add('hidden');
                    campo.querySelectorAll('select, input').forEach(function (input) {
                        input.disabled = true;
                    });
                });

                camposRelacion.forEach(function (campo) {
                    campo.classList.add('hidden');
                    campo.querySelectorAll('select, input').forEach(function (input) {
                        input.disabled = true;
                    });
                });

                if (['opcion_unica', 'checklist_guiado'].includes(tipo)) {
                    camposCorrecta.forEach(function (campo) {
                        campo.classList.remove('hidden');
                        campo.querySelectorAll('select, input').forEach(function (input) {
                            input.disabled = false;
                        });
                    });
                }

                if (tipo === 'relacionar') {
                    camposRelacion.forEach(function (campo) {
                        campo.classList.remove('hidden');
                        campo.querySelectorAll('select, input').forEach(function (input) {
                            input.disabled = false;
                        });
                    });
                }
            }

            function crearFilaOpcionEdicion(scope, tipo, indice) {
                const fila = document.createElement('div');
                fila.dataset.scope = scope;

                if (tipo === 'relacionar') {
                    const pareja = Math.floor(indice / 2) + 1;
                    const indiceIzquierda = indice;
                    const indiceDerecha = indice + 1;

                    fila.className = 'fila-opcion-edicion rounded-2xl border border-sky-200 bg-white/95 p-4 shadow-sm';

                    fila.innerHTML =
                        '<div class="flex items-center justify-between gap-3 mb-4">' +
                            '<div>' +
                                '<p class="text-xs font-black uppercase tracking-[0.18em] text-sky-500">Nueva pareja ' + pareja + '</p>' +
                                '<p class="text-sm font-bold text-slate-800">Relacionar izquierda con derecha</p>' +
                            '</div>' +
                            '<button type="button" class="btn-quitar-opcion-edicion px-3 py-1.5 rounded-full bg-red-100 text-red-700 border border-red-200 text-xs font-black hover:bg-red-200 transition">Quitar</button>' +
                        '</div>' +

                        '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">' +
                            '<div class="rounded-2xl border border-blue-200 bg-blue-50/70 p-4">' +
                                '<label class="block text-sm font-bold mb-1 text-slate-700">Elemento del lado izquierdo</label>' +
                                '<textarea name="opciones_nuevas[' + indiceIzquierda + '][opcion]" rows="3" required class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100" placeholder="Ejemplo: blanco"></textarea>' +

                                '<input type="hidden" name="opciones_nuevas[' + indiceIzquierda + '][lado]" value="izquierda">' +
                                '<input type="hidden" name="opciones_nuevas[' + indiceIzquierda + '][clave_relacion]" value="' + pareja + '">' +
                                '<input type="hidden" name="opciones_nuevas[' + indiceIzquierda + '][orden]" value="' + pareja + '">' +
                            '</div>' +

                            '<div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4">' +
                                '<label class="block text-sm font-bold mb-1 text-slate-700">Elemento del lado derecho</label>' +
                                '<textarea name="opciones_nuevas[' + indiceDerecha + '][opcion]" rows="3" required class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100" placeholder="Ejemplo: negro"></textarea>' +

                                '<input type="hidden" name="opciones_nuevas[' + indiceDerecha + '][lado]" value="derecha">' +
                                '<input type="hidden" name="opciones_nuevas[' + indiceDerecha + '][clave_relacion]" value="' + pareja + '">' +
                                '<input type="hidden" name="opciones_nuevas[' + indiceDerecha + '][orden]" value="' + pareja + '">' +
                            '</div>' +
                        '</div>' +

                        '<p class="mt-3 text-xs font-semibold text-slate-500">Estos dos textos quedan vinculados automáticamente como pareja ' + pareja + '.</p>';

                    fila.querySelector('.btn-quitar-opcion-edicion').addEventListener('click', function () {
                        fila.remove();
                    });

                    return fila;
                }

                fila.className = 'fila-opcion-edicion rounded border bg-white dark:bg-gray-800 p-3';

                fila.innerHTML =
                    '<div class="flex items-center justify-between mb-2">' +
                        '<p class="font-semibold text-xs text-gray-500">Nueva opción</p>' +
                        '<button type="button" class="btn-quitar-opcion-edicion px-2 py-1 bg-red-600 text-white rounded text-xs">Quitar</button>' +
                    '</div>' +

                    '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">' +
                        '<div class="md:col-span-2">' +
                            '<label class="block text-sm font-medium mb-1">Texto de la opción/campo a ordenar</label>' +
                            '<textarea name="opciones_nuevas[' + indice + '][opcion]" rows="2" required class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"></textarea>' +
                        '</div>' +

                        '<div class="campo-opcion-relacion-edicion hidden">' +
                            '<label class="block text-sm font-medium mb-1">Columna a la que pertenece</label>' +
                            '<select name="opciones_nuevas[' + indice + '][lado]" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" disabled>' +
                                '<option value="">Seleccione...</option>' +
                                '<option value="izquierda">Izquierda</option>' +
                                '<option value="derecha">Derecha</option>' +
                            '</select>' +
                        '</div>' +

                        '<div class="campo-opcion-relacion-edicion hidden">' +
                            '<label class="block text-sm font-medium mb-1">Pareja número</label>' +
                            '<input type="number" name="opciones_nuevas[' + indice + '][clave_relacion]" min="1" value="' + (indice + 1) + '" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" disabled>' +
                            '<p class="mt-1 text-xs text-slate-500">Usá el mismo número para la opción izquierda y su opción derecha.</p>' +
                        '</div>' +

                        '<div class="campo-opcion-correcta-edicion hidden">' +
                            '<label class="block text-sm font-medium mb-1">¿Es correcta?</label>' +
                            '<select name="opciones_nuevas[' + indice + '][es_correcta]" class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100" disabled>' +
                                '<option value="0">No</option>' +
                                '<option value="1">Sí</option>' +
                            '</select>' +
                        '</div>' +

                        '<div>' +
                            '<label class="block text-sm font-medium mb-1">Orden</label>' +
                            '<input type="number" name="opciones_nuevas[' + indice + '][orden]" min="1" value="' + (indice + 1) + '" required class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">' +
                        '</div>' +
                    '</div>';

                configurarFilaOpcionEdicion(fila, tipo);

                fila.querySelector('.btn-quitar-opcion-edicion').addEventListener('click', function () {
                    fila.remove();
                });

                return fila;
            }

            function actualizarFormularioPreguntaEjercicio(select) {
                const scope = select.dataset.scope;
                const tipo = select.value;

                const ayuda = document.querySelector('.ayuda-tipo-ejercicio[data-scope="' + scope + '"]');
                const campoRevision = document.querySelector('.campo-revision-manual-ejercicio[data-scope="' + scope + '"]');
                const campoEnunciadoNormal = document.querySelector('.campo-enunciado-normal-ejercicio[data-scope="' + scope + '"]');
                const campoCompletarAmigable = document.querySelector('.campo-completar-amigable-ejercicio[data-scope="' + scope + '"]');
                const campoCasoEstudio = document.querySelector('.campo-caso-estudio-ejercicio[data-scope="' + scope + '"]');
                const campoVisualIdentificacion = document.querySelector('.campo-visual-identificacion-ejercicio[data-scope="' + scope + '"]');
                const campoPosicionImagen = document.querySelector('.campo-posicion-imagen-ejercicio[data-scope="' + scope + '"]');
                const campoImagenPreguntaOpciones = document.querySelector('.campo-imagen-pregunta-opciones-ejercicio[data-scope="' + scope + '"]');

                const bloqueOpcionesIniciales = document.querySelector('.bloque-opciones-iniciales-ejercicio[data-scope="' + scope + '"]');
                const contenedorOpcionesIniciales = document.querySelector('.contenedor-opciones-iniciales-ejercicio[data-scope="' + scope + '"]');
                const botonAgregarOpcionInicial = document.querySelector('.btn-agregar-opcion-inicial[data-scope="' + scope + '"]');
                const bloqueVerdaderoFalso = document.querySelector('.bloque-verdadero-falso-inicial[data-scope="' + scope + '"]');

                const bloqueOpcionesEdicion = document.querySelector('.bloque-opciones-edicion-ejercicio[data-scope="' + scope + '"]');
                const contenedorOpcionesEdicion = document.querySelector('.contenedor-opciones-edicion-ejercicio[data-scope="' + scope + '"]');
                const botonAgregarOpcionEdicion = document.querySelector('.btn-agregar-opcion-edicion[data-scope="' + scope + '"]');
                const bloqueVerdaderoFalsoEdicion = document.querySelector('.bloque-verdadero-falso-edicion[data-scope="' + scope + '"]');

                const tiposConOpcionesGenericas = [
                    'opcion_unica',
                    'ordenar',
                    'relacionar',
                    'checklist_guiado',
                    'seleccionar_posicion_imagen'
                ];

                if (campoRevision) campoRevision.style.display = '';
                if (campoEnunciadoNormal) campoEnunciadoNormal.classList.remove('hidden');
                if (campoCompletarAmigable) campoCompletarAmigable.classList.add('hidden');
                if (campoCasoEstudio) campoCasoEstudio.classList.add('hidden');
                if (campoVisualIdentificacion) campoVisualIdentificacion.classList.add('hidden');
                if (campoPosicionImagen) campoPosicionImagen.classList.add('hidden');

                if (campoImagenPreguntaOpciones) campoImagenPreguntaOpciones.classList.add('hidden');
                habilitarCampos(campoImagenPreguntaOpciones, false);

                if (bloqueOpcionesIniciales) bloqueOpcionesIniciales.classList.add('hidden');
                if (bloqueVerdaderoFalso) bloqueVerdaderoFalso.classList.add('hidden');
                if (botonAgregarOpcionInicial) botonAgregarOpcionInicial.classList.add('hidden');

                habilitarCampos(contenedorOpcionesIniciales, false);
                habilitarCampos(bloqueVerdaderoFalso, false);

                if (bloqueOpcionesEdicion) bloqueOpcionesEdicion.classList.add('hidden');
                if (bloqueVerdaderoFalsoEdicion) bloqueVerdaderoFalsoEdicion.classList.add('hidden');
                if (botonAgregarOpcionEdicion) botonAgregarOpcionEdicion.classList.add('hidden');

                habilitarCampos(contenedorOpcionesEdicion, false);
                habilitarCampos(bloqueVerdaderoFalsoEdicion, false);

                if (tiposConOpcionesGenericas.includes(tipo)) {
                    if (bloqueOpcionesIniciales) bloqueOpcionesIniciales.classList.remove('hidden');
                    if (botonAgregarOpcionInicial) botonAgregarOpcionInicial.classList.remove('hidden');

                    habilitarCampos(contenedorOpcionesIniciales, true);

                    if (contenedorOpcionesIniciales) {
                        contenedorOpcionesIniciales.querySelectorAll('.fila-opcion-inicial').forEach(function (fila) {
                            configurarFilaOpcionInicial(fila, tipo);
                        });
                    }
                }

                if (tiposConOpcionesGenericas.includes(tipo)) {
                    if (bloqueOpcionesEdicion) bloqueOpcionesEdicion.classList.remove('hidden');
                    if (botonAgregarOpcionEdicion) botonAgregarOpcionEdicion.classList.remove('hidden');

                    habilitarCampos(contenedorOpcionesEdicion, true);

                    if (contenedorOpcionesEdicion) {
                        contenedorOpcionesEdicion.querySelectorAll('.fila-opcion-edicion').forEach(function (fila) {
                            configurarFilaOpcionEdicion(fila, tipo);
                        });
                    }
                }

                if (tipo === 'verdadero_falso') {
                    if (bloqueOpcionesIniciales) bloqueOpcionesIniciales.classList.remove('hidden');
                    if (bloqueVerdaderoFalso) bloqueVerdaderoFalso.classList.remove('hidden');

                    habilitarCampos(bloqueVerdaderoFalso, true);
                    habilitarCampos(contenedorOpcionesIniciales, false);
                }

                if (tipo === 'verdadero_falso') {
                    if (bloqueOpcionesEdicion) bloqueOpcionesEdicion.classList.remove('hidden');
                    if (bloqueVerdaderoFalsoEdicion) bloqueVerdaderoFalsoEdicion.classList.remove('hidden');

                    habilitarCampos(bloqueVerdaderoFalsoEdicion, true);
                    habilitarCampos(contenedorOpcionesEdicion, false);
                }

                if (tipo === 'completar') {
                    if (ayuda) ayuda.innerHTML = 'Completar en frase: primero escribí el enunciado de la pregunta; luego completá el texto antes, la respuesta correcta y el texto después del espacio en blanco.';
                    if (campoEnunciadoNormal) campoEnunciadoNormal.classList.remove('hidden');
                    if (campoCompletarAmigable) campoCompletarAmigable.classList.remove('hidden');
                } else if (tipo === 'caso_practico') {
                    if (ayuda) ayuda.innerHTML = 'Caso de estudio: permite una respuesta amplia y requiere revisión manual.';
                    if (campoCasoEstudio) campoCasoEstudio.classList.remove('hidden');
                } else if (tipo === 'checklist_guiado') {
                    if (ayuda) ayuda.innerHTML = 'Opción múltiple: agrega varias opciones y marca todas las respuestas correctas.';
                    if (campoImagenPreguntaOpciones) campoImagenPreguntaOpciones.classList.remove('hidden');
                    habilitarCampos(campoImagenPreguntaOpciones, true);
                } else if (tipo === 'seleccionar_posicion_imagen') {
                    if (ayuda) ayuda.innerHTML = 'Seleccionar orden: agrega cada campo, acción o elemento como opción. El campo Orden será el número correcto. La imagen de apoyo es opcional.';
                    if (campoPosicionImagen) campoPosicionImagen.classList.remove('hidden');
                } else if (tipo === 'actividad_visual_identificacion') {
                    if (ayuda) ayuda.innerHTML = 'Actividad visual de identificación: sube una imagen y agrega instrucciones para que el usuario responda.';
                    if (campoVisualIdentificacion) campoVisualIdentificacion.classList.remove('hidden');
                } else if (tipo === 'relacionar') {
                    if (ayuda) ayuda.innerHTML = 'Relacionar: usa lado izquierdo/derecho y la misma clave para cada pareja.';
                } else if (tipo === 'verdadero_falso') {
                    if (ayuda) ayuda.innerHTML = 'Verdadero/Falso: escribe el enunciado y marca si la respuesta correcta es Verdadero o Falso.';
                } else {
                    if (ayuda) ayuda.innerHTML = 'Pregunta de selección: la corrección se define desde las opciones. Puedes cargar una imagen para que aparezca arriba de las opciones.';
                    if (tipo === 'opcion_unica') {
                        if (campoImagenPreguntaOpciones) campoImagenPreguntaOpciones.classList.remove('hidden');
                        habilitarCampos(campoImagenPreguntaOpciones, true);
                    }
                }
            }

            document.querySelectorAll('.btn-agregar-opcion-inicial').forEach(function (boton) {
                boton.addEventListener('click', function () {
                    const scope = this.dataset.scope;
                    const selectTipo = document.querySelector('.tipo-pregunta-ejercicio[data-scope="' + scope + '"]');
                    const contenedor = document.querySelector('.contenedor-opciones-iniciales-ejercicio[data-scope="' + scope + '"]');

                    if (!selectTipo || !contenedor) return;

                    const tipo = selectTipo.value;
                    const indice = parseInt(this.dataset.nextIndex || '0', 10);

                    const fila = crearFilaOpcionInicial(scope, tipo, indice);
                    contenedor.appendChild(fila);

                    this.dataset.nextIndex = indice + (tipo === 'relacionar' ? 2 : 1);
                });
            });

            document.querySelectorAll('.btn-agregar-opcion-edicion').forEach(function (boton) {
                boton.addEventListener('click', function () {
                    const scope = this.dataset.scope;
                    const selectTipo = document.querySelector('.tipo-pregunta-ejercicio[data-scope="' + scope + '"]');
                    const contenedor = document.querySelector('.contenedor-opciones-edicion-ejercicio[data-scope="' + scope + '"]');

                    if (!selectTipo || !contenedor) return;

                    const tipo = selectTipo.value;
                    const indice = parseInt(this.dataset.nextIndex || '0', 10);

                    const fila = crearFilaOpcionEdicion(scope, tipo, indice);
                    contenedor.appendChild(fila);

                    this.dataset.nextIndex = indice + (tipo === 'relacionar' ? 2 : 1);
                });
            });

            document.querySelectorAll('.tipo-pregunta-ejercicio').forEach(function (select) {
                select.addEventListener('change', function () {
                    actualizarFormularioPreguntaEjercicio(this);
                });

                actualizarFormularioPreguntaEjercicio(select);
            });
        });
    </script>
</x-app-layout>