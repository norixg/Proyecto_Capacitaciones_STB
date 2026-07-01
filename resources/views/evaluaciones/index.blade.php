<x-app-layout>

    @php
        $volverAlModuloDesdeEvaluaciones = (int) request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) === 1;
        $idSeccionRetornoEvaluaciones = request('id_capacitacion_modulo_seccion');

        $urlRegresoEvaluaciones = $volverAlModuloDesdeEvaluaciones
            ? route('capacitacion_modulos.edit', [
                'id' => $modulo->id_capacitacion_modulo,
                'origen' => 'builder',
            ]) . ($idSeccionRetornoEvaluaciones ? '#seccion-modulo-' . $idSeccionRetornoEvaluaciones : '')
            : route('capacitaciones.builder', $modulo->capacitacion?->id_capacitacion);

        $parametrosCrearEvaluacion = [
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
        ];

        if ($volverAlModuloDesdeEvaluaciones && $idSeccionRetornoEvaluaciones) {
            $parametrosCrearEvaluacion['volver_modulo'] = 1;
            $parametrosCrearEvaluacion['id_capacitacion_modulo_seccion'] = $idSeccionRetornoEvaluaciones;
        }
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                    Evaluaciones del módulo
                </p>

                <h2 class="esf-seguimiento-title">
                    Evaluaciones
                </h2>

                <p class="esf-seguimiento-subtitle">
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
                            Evaluaciones registradas
                        </p>

                        <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">
                            Evaluaciones del módulo
                        </h3>

                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Administra las evaluaciones de este módulo: preguntas, opciones, intentos y reglas de aprobación.
                        </p>

                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">
                            <strong>Capacitación:</strong> {{ $modulo->capacitacion?->capacitacion }}
                        </p>
                    </div>

                    <div class="flex flex-col md:flex-row gap-2">
                        <input type="text"
                               id="buscarEvaluacion"
                               placeholder="Buscar evaluación..."
                               class="min-w-[220px] rounded-full border border-slate-200 bg-white/90 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">

                        <button type="button"
                                onclick="abrirModal('modalCrearEvaluacion')"
                                class="esf-btn esf-btn-primary">
                            Crear evaluación
                        </button>

                        <a href="{{ $urlRegresoEvaluaciones }}"
                        class="esf-btn esf-btn-soft">
                            Volver
                        </a>
                    </div>
                </div>
            </div>

            <style>
                #modalCrearEvaluacion {
                    background: rgba(15, 23, 42, 0.58);
                }

                #modalCrearEvaluacion .modal-evaluacion-card {
                    width: 100%;
                    max-width: 64rem;
                    max-height: 90vh;
                    overflow-y: auto;
                    margin-top: 2rem;
                    margin-bottom: 2rem;
                    border-radius: 32px;
                    border: 1px solid rgba(226, 232, 240, 0.96);
                    background: linear-gradient(135deg, rgba(255,255,255,0.98), rgba(239,246,255,0.90));
                    box-shadow: 0 28px 80px rgba(15, 23, 42, 0.26);
                    padding: 1.75rem;
                    color: #0f172a;
                }

                #modalCrearEvaluacion .modal-evaluacion-titulo {
                    font-size: 1.25rem;
                    font-weight: 900;
                    color: #0f172a;
                    margin-bottom: 0.45rem;
                }

                #modalCrearEvaluacion .modal-evaluacion-descripcion {
                    font-size: 0.82rem;
                    font-weight: 600;
                    color: #64748b;
                    margin-bottom: 1.25rem;
                }

                #modalCrearEvaluacion .modal-evaluacion-grid {
                    display: grid;
                    grid-template-columns: 1fr;
                    gap: 1rem;
                }

                @media (min-width: 768px) {
                    #modalCrearEvaluacion .modal-evaluacion-grid {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                }

                #modalCrearEvaluacion .modal-evaluacion-full {
                    grid-column: 1 / -1;
                }

                #modalCrearEvaluacion label {
                    display: block;
                    margin-bottom: 0.35rem;
                    font-size: 0.78rem;
                    font-weight: 900;
                    color: #334155;
                }

                #modalCrearEvaluacion input:not([type="hidden"]),
                #modalCrearEvaluacion select,
                #modalCrearEvaluacion textarea {
                    width: 100%;
                    border-radius: 1rem;
                    border: 1px solid rgba(203, 213, 225, 0.95);
                    background: rgba(255, 255, 255, 0.96);
                    color: #0f172a;
                    padding: 0.78rem 1rem;
                    font-size: 0.9rem;
                    font-weight: 650;
                    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.035);
                }

                #modalCrearEvaluacion textarea {
                    min-height: 110px;
                    resize: vertical;
                }

                #modalCrearEvaluacion input:focus,
                #modalCrearEvaluacion select:focus,
                #modalCrearEvaluacion textarea:focus {
                    border-color: rgba(96, 165, 250, 0.9);
                    box-shadow: 0 0 0 4px rgba(191, 219, 254, 0.65);
                    outline: none;
                }

                #modalCrearEvaluacion .modal-evaluacion-help {
                    margin-top: 0.35rem;
                    font-size: 0.75rem;
                    font-weight: 650;
                    color: #64748b;
                }

                #modalCrearEvaluacion .modal-evaluacion-footer {
                    margin-top: 1.25rem;
                    padding-top: 1rem;
                    border-top: 1px solid rgba(226, 232, 240, 0.9);
                    display: flex;
                    justify-content: flex-end;
                    align-items: center;
                    gap: 0.75rem;
                    flex-wrap: wrap;
                }

                #modalCrearEvaluacion .modal-evaluacion-btn-primary {
                    min-width: 140px;
                    border-radius: 999px;
                    background: #071225;
                    color: white;
                    padding: 0.78rem 1.15rem;
                    font-size: 0.82rem;
                    font-weight: 900;
                    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.22);
                    transition: all 160ms ease;
                }

                #modalCrearEvaluacion .modal-evaluacion-btn-primary:hover {
                    transform: translateY(-1px);
                    background: #0f172a;
                }

                #modalCrearEvaluacion .modal-evaluacion-btn-soft {
                    min-width: 130px;
                    border-radius: 999px;
                    background: #dbeafe;
                    color: #1e3a8a;
                    padding: 0.78rem 1.15rem;
                    font-size: 0.82rem;
                    font-weight: 900;
                    transition: all 160ms ease;
                }

                #modalCrearEvaluacion .modal-evaluacion-btn-soft:hover {
                    transform: translateY(-1px);
                    background: #bfdbfe;
                }
            </style>

            <div id="modalCrearEvaluacion"
                class="fixed inset-0 z-50 hidden items-start justify-center overflow-y-auto px-4 py-10">

                <div class="modal-evaluacion-card">
                    <h3 class="modal-evaluacion-titulo">
                        Crear evaluación
                    </h3>

                    <p class="modal-evaluacion-descripcion">
                        Completa los datos generales de la evaluación. Después podrás agregar preguntas y opciones.
                    </p>

                    <form method="POST"
                        action="{{ route('capacitacion_modulos.evaluaciones.store', $modulo->id_capacitacion_modulo) }}">
                        @csrf

                        <input type="hidden" name="volver_modulo" value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">
                        <input type="hidden" name="id_capacitacion_modulo_seccion" value="{{ request('id_capacitacion_modulo_seccion') }}">
                        <input type="hidden" name="requiere_revision_manual" value="0">

                        <div class="modal-evaluacion-grid">
                            <div class="modal-evaluacion-full">
                                <label>Título</label>
                                <input type="text"
                                    name="titulo"
                                    value="{{ old('titulo') }}"
                                    required>
                            </div>

                            <div class="modal-evaluacion-full">
                                <label>Descripción</label>
                                <textarea name="descripcion" rows="4">{{ old('descripcion') }}</textarea>
                            </div>

                            <div class="modal-evaluacion-full">
                                <label>Instrucciones</label>
                                <textarea name="instrucciones" rows="4">{{ old('instrucciones') }}</textarea>

                                <p class="modal-evaluacion-help">
                                    Indicaciones que verá el usuario antes de presentar la evaluación.
                                </p>
                            </div>

                            <div>
                                <label>Intentos máximos</label>
                                <input type="number"
                                    name="intentos_maximos"
                                    value="{{ old('intentos_maximos') }}"
                                    min="1">

                                <p class="modal-evaluacion-help">
                                    Si lo dejas vacío, los intentos serán ilimitados.
                                </p>
                            </div>

                            <div>
                                <label>Tiempo límite en minutos</label>
                                <input type="number"
                                    name="tiempo_limite_minutos"
                                    value="{{ old('tiempo_limite_minutos') }}"
                                    min="1">

                                <p class="modal-evaluacion-help">
                                    Si lo dejas vacío, no tendrá temporizador.
                                </p>
                            </div>

                            <div>
                                <label>% aprobación</label>
                                <input type="number"
                                    name="porcentaje_aprobacion"
                                    value="{{ old('porcentaje_aprobacion', 70) }}"
                                    min="1"
                                    max="100"
                                    step="0.01">
                            </div>

                            <div>
                                <label>Ubicar en sección/subsección</label>
                                <select name="id_capacitacion_modulo_seccion">
                                    <option value="">Contenido general del módulo</option>

                                    @foreach($modulo->secciones->where('estado', 1) as $seccion)
                                        <option value="{{ $seccion->id_capacitacion_modulo_seccion }}"
                                                {{ (string) old('id_capacitacion_modulo_seccion', request('id_capacitacion_modulo_seccion')) === (string) $seccion->id_capacitacion_modulo_seccion ? 'selected' : '' }}>
                                            {{ $seccion->nivel == 2 ? '— ' : '' }}{{ $seccion->titulo }}
                                        </option>
                                    @endforeach
                                </select>

                                <p class="modal-evaluacion-help">
                                    Aquí decides en qué parte del módulo aparecerá esta evaluación.
                                </p>
                            </div>

                            <div>
                                <label>Orden</label>
                                <input type="number"
                                    name="orden"
                                    value="{{ old('orden', 1) }}"
                                    min="1"
                                    required>
                            </div>

                            <div>
                                <label>Obligatorio</label>
                                <select name="obligatorio">
                                    <option value="1" {{ old('obligatorio', 1) == 1 ? 'selected' : '' }}>Sí</option>
                                    <option value="0" {{ old('obligatorio') == 0 ? 'selected' : '' }}>No</option>
                                </select>
                            </div>

                            <div>
                                <label>Estado</label>
                                <select name="activa">
                                    <option value="1" {{ old('activa', 1) == 1 ? 'selected' : '' }}>Activa</option>
                                    <option value="0" {{ old('activa') == 0 ? 'selected' : '' }}>Inactiva</option>
                                </select>
                            </div>

                            <div>
                                <label>Mostrar resultado inmediato</label>
                                <select name="mostrar_resultado_inmediato">
                                    <option value="1" {{ old('mostrar_resultado_inmediato', 1) == 1 ? 'selected' : '' }}>Sí</option>
                                    <option value="0" {{ old('mostrar_resultado_inmediato') == 0 ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <div class="modal-evaluacion-footer">
                            <button type="submit" class="modal-evaluacion-btn-primary">
                                Guardar evaluación
                            </button>

                            <button type="button"
                                    onclick="cerrarModal('modalCrearEvaluacion')"
                                    class="modal-evaluacion-btn-soft">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="contenedorEvaluaciones" class="space-y-4">
                @forelse($modulo->evaluaciones as $evaluacion)
                    <details id="evaluacion-{{ $evaluacion->id_evaluacion }}"
                        class="evaluacion-card esf-learning-admin-card transition hover:-translate-y-1 hover:shadow-xl">
                        <summary class="esf-learning-admin-summary">
                            <div class="inline-flex w-full flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <p class="evaluacion-titulo font-bold text-gray-900 dark:text-gray-100">
                                        {{ $evaluacion->orden ?? $loop->iteration }}. {{ $evaluacion->titulo }}
                                    </p>

                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $evaluacion->preguntas->count() }} pregunta(s)
                                        · Intentos: {{ $evaluacion->intentos_maximos ?: 'Sin límite' }}
                                        · Ubicación: {{ $evaluacion->seccion?->titulo ?? 'Contenido general del módulo' }}
                                        · Aprobación: {{ $evaluacion->porcentaje_aprobacion ?? 70 }}%
                                        · {{ (int) $evaluacion->activa === 1 ? 'Activa' : 'Inactiva' }}
                                    </p>
                                </div>

                                <span class="px-3 py-1 text-xs rounded-full {{ (int) $evaluacion->activa === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ (int) $evaluacion->activa === 1 ? 'Activa' : 'Inactiva' }}
                                </span>
                            </div>
                        </summary>

                        <div class="esf-learning-admin-body space-y-4">
                            <div class="esf-learning-info-panel">
                                <div class="esf-learning-info-layout">
                                    <div>
                                        <h4 class="text-lg font-black text-slate-900 dark:text-slate-100">
                                            Información de la evaluación
                                        </h4>

                                        <div class="esf-learning-info-grid mt-4">
                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Descripción:</span>
                                                {{ $evaluacion->descripcion ?: 'Sin descripción.' }}
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Orden:</span>
                                                {{ $evaluacion->orden }}
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Instrucciones:</span>
                                                {{ $evaluacion->instrucciones ?: 'Sin instrucciones.' }}
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Porcentaje aprobación:</span>
                                                {{ number_format((float) ($evaluacion->porcentaje_aprobacion ?? 70), 2) }}%
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Tiempo límite:</span>
                                                {{ $evaluacion->tiempo_limite_minutos ? $evaluacion->tiempo_limite_minutos . ' minuto(s)' : 'Sin temporizador' }}
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Intentos máximos:</span>
                                                {{ $evaluacion->intentos_maximos ?: 'Sin límite' }}
                                            </p>

                                            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-slate-900 dark:text-slate-100">Mostrar resultado inmediato:</span>
                                                {{ (int) ($evaluacion->mostrar_resultado_inmediato ?? 0) === 1 ? 'Sí' : 'No' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="esf-learning-inline-actions">
                                        <button type="button"
                                                onclick="abrirModal('modalEditarEvaluacion{{ $evaluacion->id_evaluacion }}')"
                                                class="esf-action-btn esf-action-edit justify-center text-center">
                                            Editar evaluación
                                        </button>

                                        <form method="POST"
                                            action="{{ route('evaluaciones.destroy', $evaluacion->id_evaluacion) }}"
                                            onsubmit="return confirm('¿Eliminar esta evaluación?');">
                                            @csrf
                                            @method('DELETE')

                                            <input type="hidden" name="volver_modulo" value="{{ request('volver_modulo', $evaluacion->id_capacitacion_modulo_seccion ? 1 : 0) }}">
                                            <input type="hidden" name="id_capacitacion_modulo_seccion" value="{{ request('id_capacitacion_modulo_seccion', $evaluacion->id_capacitacion_modulo_seccion) }}">

                                            <button type="submit"
                                                    class="esf-action-btn esf-action-delete w-full justify-center text-center">
                                                Eliminar evaluación
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="esf-learning-question-panel">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h4 class="text-lg font-black text-slate-900 dark:text-slate-100">
                                            Preguntas de la evaluación
                                        </h4>

                                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                            Preguntas, opciones, puntajes y configuración de esta evaluación.
                                        </p>
                                    </div>

                                    <button type="button"
                                            onclick="abrirModal('modalCrearPreguntaEvaluacion{{ $evaluacion->id_evaluacion }}')"
                                            class="esf-btn esf-btn-primary">
                                        + Pregunta
                                    </button>
                                </div>

                                <div class="mt-4 space-y-3">
                                    @forelse($evaluacion->preguntas as $pregunta)
                                        @php
                                            $partesCompletar = explode('[[blank]]', $pregunta->pregunta);
                                            $textoAntesCompletar = trim($partesCompletar[0] ?? '');
                                            $textoDespuesCompletar = trim($partesCompletar[1] ?? '');
                                        @endphp

                                        <div class="esf-question-admin-card">
                                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                                <div class="flex-1">
                                                    <p class="esf-question-title">
                                                        Pregunta {{ $loop->iteration }}
                                                    </p>

                                                    <p class="esf-question-text">
                                                        {{ $pregunta->pregunta ?? $pregunta->enunciado ?? 'Sin pregunta.' }}
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

                                                    @if($pregunta->respuesta_correcta_texto ?? false)
                                                        <p class="mt-2 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-2 text-xs font-semibold text-blue-800">
                                                            Respuesta correcta texto: {{ $pregunta->respuesta_correcta_texto }}
                                                        </p>
                                                    @endif
                                                </div>

                                                <div class="esf-question-actions">
                                                    <button type="button"
                                                            onclick="abrirModal('modalEditarPreguntaEvaluacion{{ $pregunta->id_evaluacion_pregunta }}')"
                                                            class="esf-action-btn esf-action-edit">
                                                        Editar pregunta
                                                    </button>

                                                    <form method="POST"
                                                        action="{{ route('evaluacion_preguntas.destroy', $pregunta->id_evaluacion_pregunta) }}"
                                                        onsubmit="return confirm('¿Eliminar esta pregunta de evaluación?');">
                                                        @csrf
                                                        @method('DELETE')

                                                        <input type="hidden" name="volver_modulo" value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">
                                                        <input type="hidden" name="id_capacitacion_modulo_seccion" value="{{ request('id_capacitacion_modulo_seccion', $pregunta->evaluacion->id_capacitacion_modulo_seccion) }}">

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

                                            @if($pregunta->tipo_pregunta === 'completar')
                                                <div class="mt-3 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-800">
                                                    <strong>Completar en frase:</strong> se usa una respuesta textual correcta.
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
                                                                        Correcta: {{ (int) $opcion->es_correcta === 1 ? 'Sí' : 'No' }}
                                                                    </p>
                                                                </div>

                                                                <form method="POST"
                                                                    action="{{ route('evaluacion_opciones.destroy', $opcion->id_evaluacion_opcion) }}"
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
                                            Esta evaluación todavía no tiene preguntas.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </details>

                    <div id="modalEditarEvaluacion{{ $evaluacion->id_evaluacion }}"
                        class="fixed inset-0 z-50 hidden items-start justify-center overflow-y-auto bg-black/55 px-4 py-10">

                        <div class="esf-admin-modal-card w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 sm:p-8 my-8">
                            <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">
                                Editar evaluación
                            </h3>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                Modifica los datos generales de la evaluación.
                            </p>

                            <form method="POST"
                                action="{{ route('evaluaciones.update', $evaluacion->id_evaluacion) }}">
                                @csrf
                                @method('PUT')

                                <input type="hidden" name="origen" value="builder">
                                <input type="hidden" name="volver_modulo" value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">
                                <input type="hidden" name="requiere_revision_manual" value="{{ old('requiere_revision_manual', $evaluacion->requiere_revision_manual ?? 0) }}">

                                <div class="esf-admin-modal-grid">
                                    <div class="esf-admin-modal-full">
                                        <label>Título</label>
                                        <input type="text"
                                            name="titulo"
                                            value="{{ old('titulo', $evaluacion->titulo) }}"
                                            required>
                                    </div>

                                    <div class="esf-admin-modal-full">
                                        <label>Descripción</label>
                                        <textarea name="descripcion" rows="4">{{ old('descripcion', $evaluacion->descripcion) }}</textarea>
                                    </div>

                                    <div class="esf-admin-modal-full">
                                        <label>Instrucciones</label>
                                        <textarea name="instrucciones" rows="4">{{ old('instrucciones', $evaluacion->instrucciones) }}</textarea>

                                        <p class="esf-help-text">
                                            Indicaciones que verá el usuario antes de presentar la evaluación.
                                        </p>
                                    </div>

                                    <div>
                                        <label>Intentos máximos</label>
                                        <input type="number"
                                            name="intentos_maximos"
                                            value="{{ old('intentos_maximos', $evaluacion->intentos_maximos) }}"
                                            min="1">

                                        <p class="esf-help-text">
                                            Si lo dejas vacío, los intentos serán ilimitados.
                                        </p>
                                    </div>

                                    <div>
                                        <label>Tiempo límite en minutos</label>
                                        <input type="number"
                                            name="tiempo_limite_minutos"
                                            value="{{ old('tiempo_limite_minutos', $evaluacion->tiempo_limite_minutos) }}"
                                            min="1">

                                        <p class="esf-help-text">
                                            Si lo dejas vacío, no tendrá temporizador.
                                        </p>
                                    </div>

                                    <div>
                                        <label>% aprobación</label>
                                        <input type="number"
                                            name="porcentaje_aprobacion"
                                            value="{{ old('porcentaje_aprobacion', $evaluacion->porcentaje_aprobacion ?? 70) }}"
                                            min="1"
                                            max="100"
                                            step="0.01"
                                            required>
                                    </div>

                                    <div>
                                        <label>Ubicar en sección/subsección</label>
                                        <select name="id_capacitacion_modulo_seccion">
                                            <option value="">Contenido general del módulo</option>

                                            @foreach($modulo->secciones->where('estado', 1) as $seccion)
                                                <option value="{{ $seccion->id_capacitacion_modulo_seccion }}"
                                                        {{ (string) old('id_capacitacion_modulo_seccion', $evaluacion->id_capacitacion_modulo_seccion) === (string) $seccion->id_capacitacion_modulo_seccion ? 'selected' : '' }}>
                                                    {{ $seccion->nivel == 2 ? '— ' : '' }}{{ $seccion->titulo }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <p class="esf-help-text">
                                            Aquí decides en qué parte del módulo aparecerá esta evaluación.
                                        </p>
                                    </div>

                                    <div>
                                        <label>Orden</label>
                                        <input type="number"
                                            name="orden"
                                            value="{{ old('orden', $evaluacion->orden ?? 1) }}"
                                            min="1"
                                            required>
                                    </div>

                                    <div>
                                        <label>Obligatorio</label>
                                        <select name="obligatorio" required>
                                            <option value="1" {{ old('obligatorio', $evaluacion->obligatorio ?? 1) == 1 ? 'selected' : '' }}>Sí</option>
                                            <option value="0" {{ old('obligatorio', $evaluacion->obligatorio ?? 1) == 0 ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label>Estado</label>
                                        <select name="activa" required>
                                            <option value="1" {{ old('activa', $evaluacion->activa) == 1 ? 'selected' : '' }}>Activa</option>
                                            <option value="0" {{ old('activa', $evaluacion->activa) == 0 ? 'selected' : '' }}>Inactiva</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label>Mostrar resultado inmediato</label>
                                        <select name="mostrar_resultado_inmediato" required>
                                            <option value="1" {{ old('mostrar_resultado_inmediato', $evaluacion->mostrar_resultado_inmediato ?? 1) == 1 ? 'selected' : '' }}>Sí</option>
                                            <option value="0" {{ old('mostrar_resultado_inmediato', $evaluacion->mostrar_resultado_inmediato ?? 1) == 0 ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="esf-admin-actions-footer">
                                    <button type="submit" class="esf-btn esf-btn-primary">
                                        Guardar cambios
                                    </button>

                                    <button type="button"
                                            onclick="cerrarModal('modalEditarEvaluacion{{ $evaluacion->id_evaluacion }}')"
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
                            Este módulo todavía no tiene evaluaciones registradas.
                        </p>

                        <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            Cuando crees evaluaciones, aparecerán aquí ordenados para administrarlos.
                        </p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    @foreach($modulo->evaluaciones as $evaluacion)
    <div id="modalCrearPreguntaEvaluacion{{ $evaluacion->id_evaluacion }}" class="modal-builder hidden fixed inset-0 z-50 bg-black bg-opacity-50 items-start justify-center overflow-y-auto p-4">
        <div class="esf-admin-modal-card w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 sm:p-8 my-8">
            <h3 class="text-xl font-bold mb-4">
                Nueva pregunta de evaluación: {{ $evaluacion->titulo }}
            </h3>

            <form method="POST" action="{{ route('evaluaciones.preguntas.store', $evaluacion->id_evaluacion) }}" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="origen" value="builder">
                <input type="hidden" name="volver_modulo" value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">
                <input type="hidden" name="id_capacitacion_modulo_seccion" value="{{ request('id_capacitacion_modulo_seccion', $evaluacion->id_capacitacion_modulo_seccion) }}">
                <input type="hidden" name="requiere_revision_manual" value="0">

                <div class="esf-admin-modal-grid">
                    <div class="md:col-span-2 campo-pregunta-normal-evaluacion"
                         data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}">
                        <label class="block text-sm font-medium mb-1">Enunciado</label>
                        <textarea name="pregunta"
                                  rows="3"
                                  class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('pregunta') }}</textarea>
                    </div>

                    <div class="md:col-span-2 campo-completar-evaluacion hidden"
                         data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}">
                        <label class="block text-sm font-medium mb-1">Texto antes del espacio en blanco</label>
                        <input type="text"
                               name="completar_texto_antes"
                               value="{{ old('completar_texto_antes') }}"
                               class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                        <label class="block text-sm font-medium mb-1 mt-3">Respuesta correcta</label>
                        <textarea name="respuesta_correcta_texto"
                                    rows="4"
                                    class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    placeholder="Escribí una respuesta válida por línea. Ejemplo:
                            blanco
                            Blanco
                            color blanco">{{ old('respuesta_correcta_texto') }}</textarea>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                El usuario escribirá una sola respuesta. El sistema la tomará como correcta si coincide con cualquiera de estas respuestas.
                            </p>

                        <label class="block text-sm font-medium mb-1 mt-3">Texto después del espacio en blanco</label>
                        <input type="text"
                               name="completar_texto_despues"
                               value="{{ old('completar_texto_despues') }}"
                               class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Tipo de pregunta</label>
                        <select name="tipo_pregunta"
                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100 tipo-pregunta-evaluacion"
                                data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}"
                                required>
                            <option value="opcion_unica">Opción única</option>
                            <option value="verdadero_falso">Verdadero / Falso</option>
                            <option value="checklist_guiado">Opción múltiple</option>
                            <option value="seleccionar_posicion_imagen">Seleccionar orden</option>
                            <option value="completar">Completar en frase</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 campo-imagen-pregunta-opciones-evaluacion hidden"
                        data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}">
                        <label class="block text-sm font-medium mb-1">
                            Imagen de la pregunta
                        </label>

                        <input type="file"
                            name="imagen_pregunta"
                            accept="image/*"
                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">

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
                               value="10"
                               class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                               required>
                    </div>

                    <div class="esf-admin-info-note esf-admin-modal-full ayuda-tipo-evaluacion"
                         data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}">
                        Selecciona el tipo de pregunta. El formulario ajustará automáticamente los campos necesarios.
                    </div>

                    <div class="md:col-span-2 campo-respuesta-breve-evaluacion hidden"
                         data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}">
                        <label class="block text-sm font-medium mb-1">Respuesta esperada / guía de revisión</label>
                        <textarea name="respuesta_correcta_texto"
                                  rows="3"
                                  class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('respuesta_correcta_texto') }}</textarea>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Mínimo de caracteres</label>
                                <input type="number"
                                       name="respuesta_breve_min"
                                       min="0"
                                       value="{{ old('respuesta_breve_min') }}"
                                       class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Máximo de caracteres</label>
                                <input type="number"
                                       name="respuesta_breve_max"
                                       min="1"
                                       value="{{ old('respuesta_breve_max') }}"
                                       class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                            </div>
                        </div>

                        <label class="block text-sm font-medium mb-1 mt-3">Placeholder para el usuario</label>
                        <input type="text"
                               name="respuesta_breve_placeholder"
                               value="{{ old('respuesta_breve_placeholder') }}"
                               class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                    </div>

                    <div class="md:col-span-2 campo-posicion-imagen-evaluacion hidden"
                        data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}">
                        <label class="block text-sm font-medium mb-1">Imagen de apoyo opcional</label>
                        <input type="file"
                            name="posicion_imagen"
                            accept="image/*"
                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Cargar imagen (Opcional)
                        </p>

                        <label class="block text-sm font-medium mb-1 mt-3">Texto de apoyo</label>
                        <input type="text"
                            name="posicion_texto_apoyo"
                            value="{{ old('posicion_texto_apoyo') }}"
                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                            placeholder="Ejemplo: Indique usando los números del 1 al 5 el orden correcto.">

                        <label class="block text-sm font-medium mb-1 mt-3">Cantidad de números de orden</label>
                        <input type="number"
                            name="posicion_cantidad"
                            min="1"
                            max="50"
                            value="{{ old('posicion_cantidad') }}"
                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                            placeholder="Ejemplo: 5">

                        <p class="mt-2 text-xs text-slate-500">
                            En las opciones, escribí cada campo, acción o elemento que el usuario deberá ordenar.
                            En el campo Orden colocá el número correcto. La imagen es opcional.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Orden</label>
                        <input type="number"
                               name="orden"
                               min="1"
                               value="{{ $evaluacion->preguntas->count() + 1 }}"
                               class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Estado</label>
                        <select name="activa"
                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                required>
                            <option value="1">Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 bloque-opciones-iniciales-evaluacion hidden"
                         data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}">

                        <div class="esf-options-admin-box">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                                <div>
                                    <h4 class="font-bold text-sm text-gray-900 dark:text-gray-100">
                                        Opciones de la pregunta
                                    </h4>

                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Agrega las opciones desde aquí. En opción única, verdadero/falso, opción múltiple y checklist marcar cuáles son correctas.
                                    </p>
                                </div>

                                <button type="button"
                                        class="btn-agregar-opcion-inicial-evaluacion esf-btn esf-btn-primary"
                                        data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}"
                                        data-next-index="0">
                                    + Opción
                                </button>
                            </div>

                            <div class="bloque-verdadero-falso-inicial-evaluacion hidden rounded border bg-white dark:bg-gray-800 p-3 mb-4"
                                 data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}">

                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">
                                    Opciones fijas para Verdadero / Falso
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="rounded border p-3">
                                        <p class="text-sm font-semibold mb-2">Verdadero</p>

                                        <input type="hidden"
                                               class="campo-vf-inicial-evaluacion"
                                               name="opciones_iniciales[0][opcion]"
                                               value="Verdadero"
                                               disabled>

                                        <input type="hidden"
                                               class="campo-vf-inicial-evaluacion"
                                               name="opciones_iniciales[0][orden]"
                                               value="1"
                                               disabled>

                                        <label class="block text-sm font-medium mb-1">
                                            ¿Es correcta?
                                        </label>

                                        <select name="opciones_iniciales[0][es_correcta]"
                                                class="campo-vf-inicial-evaluacion w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                                disabled>
                                            <option value="1">Sí</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>

                                    <div class="rounded border p-3">
                                        <p class="text-sm font-semibold mb-2">Falso</p>

                                        <input type="hidden"
                                               class="campo-vf-inicial-evaluacion"
                                               name="opciones_iniciales[1][opcion]"
                                               value="Falso"
                                               disabled>

                                        <input type="hidden"
                                               class="campo-vf-inicial-evaluacion"
                                               name="opciones_iniciales[1][orden]"
                                               value="2"
                                               disabled>

                                        <label class="block text-sm font-medium mb-1">
                                            ¿Es correcta?
                                        </label>

                                        <select name="opciones_iniciales[1][es_correcta]"
                                                class="campo-vf-inicial-evaluacion w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                                disabled>
                                            <option value="0">No</option>
                                            <option value="1">Sí</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="contenedor-opciones-iniciales-evaluacion space-y-4"
                                 data-scope="crear-pregunta-evaluacion-{{ $evaluacion->id_evaluacion }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="esf-admin-actions-footer">
                    <button type="button"
                            onclick="cerrarModal('modalCrearPreguntaEvaluacion{{ $evaluacion->id_evaluacion }}')"
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

    @foreach($evaluacion->preguntas as $pregunta)
        @php
            $configPregunta = json_decode($pregunta->configuracion_json ?? '{}', true);
            $partesCompletar = explode('[[blank]]', $pregunta->pregunta);
            $textoAntesCompletar = trim($partesCompletar[0] ?? '');
            $textoDespuesCompletar = trim($partesCompletar[1] ?? '');
        @endphp

        <div id="modalEditarPreguntaEvaluacion{{ $pregunta->id_evaluacion_pregunta }}"
            class="fixed inset-0 z-50 hidden items-start justify-center overflow-y-auto bg-black/55 px-4 py-10">
            <div class="esf-admin-modal-card w-full max-w-4xl max-h-[90vh] overflow-y-auto p-6 sm:p-8 my-8">
                <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">
                    Editar pregunta y opciones de evaluación
                </h3>

                <form method="POST" action="{{ route('evaluacion_preguntas.update', $pregunta->id_evaluacion_pregunta) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="origen" value="builder">
                    <input type="hidden" name="volver_modulo" value="{{ request('volver_modulo', request('id_capacitacion_modulo_seccion') ? 1 : 0) }}">
                    <input type="hidden" name="id_capacitacion_modulo_seccion" value="{{ request('id_capacitacion_modulo_seccion', $pregunta->evaluacion->id_capacitacion_modulo_seccion) }}">
                    <input type="hidden" name="requiere_revision_manual" value="{{ (int) ($pregunta->requiere_revision_manual ?? 0) }}">

                    <div class="esf-admin-modal-grid">
                        <div class="md:col-span-2 campo-pregunta-normal-evaluacion"
                             data-scope="editar-pregunta-evaluacion-{{ $pregunta->id_evaluacion_pregunta }}">
                            <label class="block text-sm font-medium mb-1">Enunciado</label>
                            <textarea name="pregunta"
                                      rows="3"
                                      class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('pregunta', $pregunta->tipo_pregunta === 'completar' ? $textoAntesCompletar : $pregunta->pregunta) }}</textarea>
                        </div>

                        <div class="md:col-span-2 campo-completar-evaluacion hidden"
                             data-scope="editar-pregunta-evaluacion-{{ $pregunta->id_evaluacion_pregunta }}">
                            <label class="block text-sm font-medium mb-1">Texto antes del espacio en blanco</label>
                            <input type="text"
                                   name="completar_texto_antes"
                                   value="{{ old('completar_texto_antes', $textoAntesCompletar) }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                            <label class="block text-sm font-medium mb-1 mt-3">Respuesta correcta</label>
                            <textarea name="respuesta_correcta_texto"
                                    rows="4"
                                    class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    placeholder="Escribí una respuesta válida por línea.">{{ old('respuesta_correcta_texto', $pregunta->respuesta_correcta_texto) }}</textarea>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                El sistema aceptará cualquiera de estas respuestas como válida.
                            </p>

                            <label class="block text-sm font-medium mb-1 mt-3">Texto después del espacio en blanco</label>
                            <input type="text"
                                   name="completar_texto_despues"
                                   value="{{ old('completar_texto_despues', $textoDespuesCompletar) }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Tipo de pregunta</label>
                            <select name="tipo_pregunta"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100 tipo-pregunta-evaluacion"
                                    data-scope="editar-pregunta-evaluacion-{{ $pregunta->id_evaluacion_pregunta }}"
                                    required>
                                <option value="opcion_unica" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) === 'opcion_unica' ? 'selected' : '' }}>Opción única</option>
                                <option value="verdadero_falso" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) === 'verdadero_falso' ? 'selected' : '' }}>Verdadero / Falso</option>
                                <option value="checklist_guiado" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) === 'checklist_guiado' ? 'selected' : '' }}>Opción múltiple</option>
                                <option value="seleccionar_posicion_imagen" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) === 'seleccionar_posicion_imagen' ? 'selected' : '' }}>Seleccionar orden</option>
                                <option value="completar" {{ old('tipo_pregunta', $pregunta->tipo_pregunta) === 'completar' ? 'selected' : '' }}>Completar en frase</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 campo-imagen-pregunta-opciones-evaluacion hidden"
                            data-scope="editar-pregunta-evaluacion-{{ $pregunta->id_evaluacion_pregunta }}">
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

                        <div class="esf-admin-modal-full esf-admin-info-note ayuda-tipo-evaluacion"
                            data-scope="editar-pregunta-evaluacion-{{ $pregunta->id_evaluacion_pregunta }}">
                            El formulario ajustará automáticamente los campos necesarios.
                        </div>

                        <div class="md:col-span-2 campo-respuesta-breve-evaluacion hidden"
                             data-scope="editar-pregunta-evaluacion-{{ $pregunta->id_evaluacion_pregunta }}">
                            <label class="block text-sm font-medium mb-1">Respuesta esperada / guía de revisión</label>
                            <textarea name="respuesta_correcta_texto"
                                      rows="3"
                                      class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('respuesta_correcta_texto', $pregunta->respuesta_correcta_texto ?? '') }}</textarea>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Mínimo de caracteres</label>
                                    <input type="number"
                                           name="respuesta_breve_min"
                                           min="0"
                                           value="{{ old('respuesta_breve_min', $configPregunta['min_caracteres'] ?? '') }}"
                                           class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Máximo de caracteres</label>
                                    <input type="number"
                                           name="respuesta_breve_max"
                                           min="1"
                                           value="{{ old('respuesta_breve_max', $configPregunta['max_caracteres'] ?? '') }}"
                                           class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                </div>
                            </div>

                            <label class="block text-sm font-medium mb-1 mt-3">Placeholder para el usuario</label>
                            <input type="text"
                                   name="respuesta_breve_placeholder"
                                   value="{{ old('respuesta_breve_placeholder', $configPregunta['placeholder'] ?? '') }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                        </div>

                        <div class="md:col-span-2 campo-posicion-imagen-evaluacion hidden"
                            data-scope="editar-pregunta-evaluacion-{{ $pregunta->id_evaluacion_pregunta }}">
                            <label class="block text-sm font-medium mb-1">Reemplazar imagen de apoyo opcional</label>
                            <input type="file"
                                name="posicion_imagen"
                                accept="image/*"
                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Si no seleccionás una nueva imagen, se conserva la imagen actual. Si esta pregunta no necesita imagen, podés dejar este campo vacío.
                            </p>

                            @if(!empty($configPregunta['imagen']))
                                <div class="mt-3">
                                    <p class="text-sm font-medium mb-2">Imagen actual</p>
                                    <img src="{{ asset('storage/' . $configPregunta['imagen']) }}"
                                        class="max-h-48 rounded border">
                                </div>
                            @endif

                            <label class="block text-sm font-medium mb-1 mt-3">Texto de apoyo</label>
                            <input type="text"
                                name="posicion_texto_apoyo"
                                value="{{ old('posicion_texto_apoyo', $configPregunta['texto_apoyo'] ?? '') }}"
                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                placeholder="Ejemplo: Indique usando los números del 1 al 5 el orden correcto.">

                            <label class="block text-sm font-medium mb-1 mt-3">Cantidad de números de orden</label>
                            <input type="number"
                                name="posicion_cantidad"
                                min="1"
                                max="50"
                                value="{{ old('posicion_cantidad', $configPregunta['cantidad_posiciones'] ?? $pregunta->opciones->count()) }}"
                                class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                placeholder="Ejemplo: 5">

                            <p class="mt-2 text-xs text-slate-500">
                                En las opciones, escribí cada campo, acción o elemento que el usuario deberá ordenar.
                                En el campo Orden colocá el número correcto. La imagen es opcional.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Orden</label>
                            <input type="number"
                                   name="orden"
                                   min="1"
                                   value="{{ old('orden', $pregunta->orden) }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Estado</label>
                            <select name="activa"
                                    class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                    required>
                                <option value="1" {{ old('activa', $pregunta->activa) == 1 ? 'selected' : '' }}>Activa</option>
                                <option value="0" {{ old('activa', $pregunta->activa) == 0 ? 'selected' : '' }}>Inactiva</option>
                            </select>
                        </div>

                        <div class="esf-admin-modal-full bloque-opciones-edicion-evaluacion"
                             data-scope="editar-pregunta-evaluacion-{{ $pregunta->id_evaluacion_pregunta }}">

                            <div class="esf-options-admin-box">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                                    <div>
                                        <h4 class="font-bold text-sm text-gray-900 dark:text-gray-100">
                                            Opciones de la pregunta
                                        </h4>

                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Edita opciones actuales, marca opciones para eliminar o agrega nuevas opciones.
                                        </p>
                                    </div>

                                    <button type="button"
                                            class="btn-agregar-opcion-edicion-evaluacion esf-btn esf-btn-primary"
                                            data-scope="editar-pregunta-evaluacion-{{ $pregunta->id_evaluacion_pregunta }}"
                                            data-next-index="{{ $pregunta->opciones->count() }}">
                                        + Opción
                                    </button>
                                </div>

                                <div class="bloque-verdadero-falso-edicion-evaluacion hidden rounded-2xl border border-slate-200 bg-white/90 p-4 mb-4 shadow-sm"
                                     data-scope="editar-pregunta-evaluacion-{{ $pregunta->id_evaluacion_pregunta }}">

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
                                                   class="campo-vf-edicion-evaluacion"
                                                   name="opciones_existentes[{{ $opcionVerdadero?->id_evaluacion_opcion ?? 'nuevo_verdadero' }}][id_evaluacion_opcion]"
                                                   value="{{ $opcionVerdadero?->id_evaluacion_opcion }}"
                                                   disabled>

                                            <input type="hidden"
                                                   class="campo-vf-edicion-evaluacion"
                                                   name="opciones_existentes[{{ $opcionVerdadero?->id_evaluacion_opcion ?? 'nuevo_verdadero' }}][opcion]"
                                                   value="Verdadero"
                                                   disabled>

                                            <input type="hidden"
                                                   class="campo-vf-edicion-evaluacion"
                                                   name="opciones_existentes[{{ $opcionVerdadero?->id_evaluacion_opcion ?? 'nuevo_verdadero' }}][orden]"
                                                   value="1"
                                                   disabled>

                                            <label class="block text-sm font-medium mb-1">
                                                ¿Es correcta?
                                            </label>

                                            <select name="opciones_existentes[{{ $opcionVerdadero?->id_evaluacion_opcion ?? 'nuevo_verdadero' }}][es_correcta]"
                                                    class="campo-vf-edicion-evaluacion w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                                    disabled>
                                                <option value="1" {{ (int) ($opcionVerdadero?->es_correcta ?? 0) === 1 ? 'selected' : '' }}>Sí</option>
                                                <option value="0" {{ (int) ($opcionVerdadero?->es_correcta ?? 0) === 0 ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>

                                        <div class="rounded border p-3">
                                            <p class="text-sm font-semibold mb-2">Falso</p>

                                            <input type="hidden"
                                                   class="campo-vf-edicion-evaluacion"
                                                   name="opciones_existentes[{{ $opcionFalso?->id_evaluacion_opcion ?? 'nuevo_falso' }}][id_evaluacion_opcion]"
                                                   value="{{ $opcionFalso?->id_evaluacion_opcion }}"
                                                   disabled>

                                            <input type="hidden"
                                                   class="campo-vf-edicion-evaluacion"
                                                   name="opciones_existentes[{{ $opcionFalso?->id_evaluacion_opcion ?? 'nuevo_falso' }}][opcion]"
                                                   value="Falso"
                                                   disabled>

                                            <input type="hidden"
                                                   class="campo-vf-edicion-evaluacion"
                                                   name="opciones_existentes[{{ $opcionFalso?->id_evaluacion_opcion ?? 'nuevo_falso' }}][orden]"
                                                   value="2"
                                                   disabled>

                                            <label class="block text-sm font-medium mb-1">
                                                ¿Es correcta?
                                            </label>

                                            <select name="opciones_existentes[{{ $opcionFalso?->id_evaluacion_opcion ?? 'nuevo_falso' }}][es_correcta]"
                                                    class="campo-vf-edicion-evaluacion w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                                    disabled>
                                                <option value="0" {{ (int) ($opcionFalso?->es_correcta ?? 0) === 0 ? 'selected' : '' }}>No</option>
                                                <option value="1" {{ (int) ($opcionFalso?->es_correcta ?? 0) === 1 ? 'selected' : '' }}>Sí</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="contenedor-opciones-edicion-evaluacion space-y-4"
                                     data-scope="editar-pregunta-evaluacion-{{ $pregunta->id_evaluacion_pregunta }}">

                                    @foreach($pregunta->opciones as $opcionExistente)
                                        <div class="fila-opcion-edicion-evaluacion rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm"
                                             data-scope="editar-pregunta-evaluacion-{{ $pregunta->id_evaluacion_pregunta }}">

                                            <div class="flex items-center justify-between mb-2">
                                                <p class="font-semibold text-xs text-gray-500">
                                                    Opción {{ $loop->iteration }}
                                                </p>

                                                <label class="inline-flex items-center gap-2 text-xs text-red-700">
                                                    <input type="checkbox"
                                                           name="opciones_existentes[{{ $opcionExistente->id_evaluacion_opcion }}][eliminar]"
                                                           value="1">
                                                    Eliminar
                                                </label>
                                            </div>

                                            <input type="hidden"
                                                   name="opciones_existentes[{{ $opcionExistente->id_evaluacion_opcion }}][id_evaluacion_opcion]"
                                                   value="{{ $opcionExistente->id_evaluacion_opcion }}">

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium mb-1">
                                                        Texto de la opción/campo a ordenar
                                                    </label>

                                                    <textarea name="opciones_existentes[{{ $opcionExistente->id_evaluacion_opcion }}][opcion]"
                                                              rows="2"
                                                              required
                                                              class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">{{ old('opciones_existentes.' . $opcionExistente->id_evaluacion_opcion . '.opcion', $opcionExistente->opcion) }}</textarea>
                                                </div>

                                                <div class="campo-opcion-correcta-edicion-evaluacion hidden">
                                                    <label class="block text-sm font-medium mb-1">
                                                        ¿Es correcta?
                                                    </label>

                                                    <select name="opciones_existentes[{{ $opcionExistente->id_evaluacion_opcion }}][es_correcta]"
                                                            class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100">
                                                        <option value="0" {{ old('opciones_existentes.' . $opcionExistente->id_evaluacion_opcion . '.es_correcta', $opcionExistente->es_correcta) == 0 ? 'selected' : '' }}>No</option>
                                                        <option value="1" {{ old('opciones_existentes.' . $opcionExistente->id_evaluacion_opcion . '.es_correcta', $opcionExistente->es_correcta) == 1 ? 'selected' : '' }}>Sí</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium mb-1">
                                                        Orden
                                                    </label>

                                                    <input type="number"
                                                           name="opciones_existentes[{{ $opcionExistente->id_evaluacion_opcion }}][orden]"
                                                           min="1"
                                                           value="{{ old('opciones_existentes.' . $opcionExistente->id_evaluacion_opcion . '.orden', $opcionExistente->orden) }}"
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
                                onclick="cerrarModal('modalEditarPreguntaEvaluacion{{ $pregunta->id_evaluacion_pregunta }}')"
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
            const detalleAbierto = new URLSearchParams(window.location.search).get('open');

            if (detalleAbierto) {
                const detalle = document.getElementById(detalleAbierto);

                if (detalle && detalle.tagName.toLowerCase() === 'details') {
                    detalle.open = true;
                    detalle.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }

            const input = document.getElementById('buscarEvaluacion');
            const tarjetas = document.querySelectorAll('.evaluacion-card');

            if (input) {
                input.addEventListener('input', function () {
                    const texto = this.value.toLowerCase().trim();

                    tarjetas.forEach(function (tarjeta) {
                        const titulo = tarjeta.querySelector('.evaluacion-titulo')?.textContent.toLowerCase() || '';
                        tarjeta.style.display = titulo.includes(texto) ? '' : 'none';
                    });
                });
            }

            @if((int) request('crear') === 1)
                abrirModal('modalCrearEvaluacion');
            @endif

            function habilitarCamposEvaluacion(contenedor, habilitar) {
                if (!contenedor) return;

                contenedor.querySelectorAll('input, textarea, select').forEach(function (campo) {
                    campo.disabled = !habilitar;
                });
            }

            function configurarFilaOpcionInicialEvaluacion(fila, tipo) {
                const camposCorrecta = fila.querySelectorAll('.campo-opcion-correcta-inicial-evaluacion');

                camposCorrecta.forEach(function (campo) {
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
            }

            function crearFilaOpcionInicialEvaluacion(scope, tipo, indice) {
                const fila = document.createElement('div');

                fila.className = 'fila-opcion-inicial-evaluacion rounded border bg-white dark:bg-gray-800 p-3';
                fila.dataset.scope = scope;

                fila.innerHTML =
                    '<div class="flex items-center justify-between mb-2">' +
                        '<p class="font-semibold text-xs text-gray-500">Opción ' + (indice + 1) + '</p>' +
                        '<button type="button" class="btn-quitar-opcion-inicial-evaluacion px-2 py-1 bg-red-600 text-white rounded text-xs">Quitar</button>' +
                    '</div>' +

                    '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">' +
                        '<div class="md:col-span-2">' +
                            '<label class="block text-sm font-medium mb-1">Texto de la opción/campo a ordenar</label>' +
                            '<textarea name="opciones_iniciales[' + indice + '][opcion]" rows="2" required class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"></textarea>' +
                        '</div>' +

                        '<div class="campo-opcion-correcta-inicial-evaluacion hidden">' +
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

                configurarFilaOpcionInicialEvaluacion(fila, tipo);

                fila.querySelector('.btn-quitar-opcion-inicial-evaluacion').addEventListener('click', function () {
                    fila.remove();
                });

                return fila;
            }

            function configurarFilaOpcionEdicionEvaluacion(fila, tipo) {
                const camposCorrecta = fila.querySelectorAll('.campo-opcion-correcta-edicion-evaluacion');

                camposCorrecta.forEach(function (campo) {
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
            }

            function crearFilaOpcionEdicionEvaluacion(scope, tipo, indice) {
                const fila = document.createElement('div');

                fila.className = 'fila-opcion-edicion-evaluacion rounded border bg-white dark:bg-gray-800 p-3';
                fila.dataset.scope = scope;

                fila.innerHTML =
                    '<div class="flex items-center justify-between mb-2">' +
                        '<p class="font-semibold text-xs text-gray-500">Nueva opción</p>' +
                        '<button type="button" class="btn-quitar-opcion-edicion-evaluacion px-2 py-1 bg-red-600 text-white rounded text-xs">Quitar</button>' +
                    '</div>' +

                    '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">' +
                        '<div class="md:col-span-2">' +
                            '<label class="block text-sm font-medium mb-1">Texto de la opción/campo a ordenar</label>' +
                            '<textarea name="opciones_nuevas[' + indice + '][opcion]" rows="2" required class="w-full rounded border-gray-300 dark:bg-gray-900 dark:text-gray-100"></textarea>' +
                        '</div>' +

                        '<div class="campo-opcion-correcta-edicion-evaluacion hidden">' +
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

                configurarFilaOpcionEdicionEvaluacion(fila, tipo);

                fila.querySelector('.btn-quitar-opcion-edicion-evaluacion').addEventListener('click', function () {
                    fila.remove();
                });

                return fila;
            }

            function actualizarFormularioPreguntaEvaluacion(select) {
                const scope = select.dataset.scope;
                const tipo = select.value;

                const ayuda = document.querySelector('.ayuda-tipo-evaluacion[data-scope="' + scope + '"]');
                const campoPreguntaNormal = document.querySelector('.campo-pregunta-normal-evaluacion[data-scope="' + scope + '"]');
                const campoCompletar = document.querySelector('.campo-completar-evaluacion[data-scope="' + scope + '"]');
                const campoRespuestaBreve = document.querySelector('.campo-respuesta-breve-evaluacion[data-scope="' + scope + '"]');
                const campoPosicionImagen = document.querySelector('.campo-posicion-imagen-evaluacion[data-scope="' + scope + '"]');
                const campoImagenPreguntaOpciones = document.querySelector('.campo-imagen-pregunta-opciones-evaluacion[data-scope="' + scope + '"]');

                const bloqueOpcionesIniciales = document.querySelector('.bloque-opciones-iniciales-evaluacion[data-scope="' + scope + '"]');
                const contenedorOpcionesIniciales = document.querySelector('.contenedor-opciones-iniciales-evaluacion[data-scope="' + scope + '"]');
                const botonAgregarOpcionInicial = document.querySelector('.btn-agregar-opcion-inicial-evaluacion[data-scope="' + scope + '"]');
                const bloqueVerdaderoFalsoInicial = document.querySelector('.bloque-verdadero-falso-inicial-evaluacion[data-scope="' + scope + '"]');

                const bloqueOpcionesEdicion = document.querySelector('.bloque-opciones-edicion-evaluacion[data-scope="' + scope + '"]');
                const contenedorOpcionesEdicion = document.querySelector('.contenedor-opciones-edicion-evaluacion[data-scope="' + scope + '"]');
                const botonAgregarOpcionEdicion = document.querySelector('.btn-agregar-opcion-edicion-evaluacion[data-scope="' + scope + '"]');
                const bloqueVerdaderoFalsoEdicion = document.querySelector('.bloque-verdadero-falso-edicion-evaluacion[data-scope="' + scope + '"]');

                const tiposConOpcionesGenericas = [
                    'opcion_unica',
                    'checklist_guiado',
                    'seleccionar_posicion_imagen'
                ];

                if (campoPreguntaNormal) campoPreguntaNormal.classList.remove('hidden');
                if (campoCompletar) campoCompletar.classList.add('hidden');
                if (campoRespuestaBreve) campoRespuestaBreve.classList.add('hidden');
                if (campoPosicionImagen) campoPosicionImagen.classList.add('hidden');

                if (campoImagenPreguntaOpciones) campoImagenPreguntaOpciones.classList.add('hidden');
                habilitarCamposEvaluacion(campoImagenPreguntaOpciones, false);

                if (bloqueOpcionesIniciales) bloqueOpcionesIniciales.classList.add('hidden');
                if (bloqueVerdaderoFalsoInicial) bloqueVerdaderoFalsoInicial.classList.add('hidden');
                if (botonAgregarOpcionInicial) botonAgregarOpcionInicial.classList.add('hidden');

                habilitarCamposEvaluacion(contenedorOpcionesIniciales, false);
                habilitarCamposEvaluacion(bloqueVerdaderoFalsoInicial, false);

                if (bloqueOpcionesEdicion) bloqueOpcionesEdicion.classList.add('hidden');
                if (bloqueVerdaderoFalsoEdicion) bloqueVerdaderoFalsoEdicion.classList.add('hidden');
                if (botonAgregarOpcionEdicion) botonAgregarOpcionEdicion.classList.add('hidden');

                habilitarCamposEvaluacion(contenedorOpcionesEdicion, false);
                habilitarCamposEvaluacion(bloqueVerdaderoFalsoEdicion, false);

                if (tiposConOpcionesGenericas.includes(tipo)) {
                    if (bloqueOpcionesIniciales) bloqueOpcionesIniciales.classList.remove('hidden');
                    if (botonAgregarOpcionInicial) botonAgregarOpcionInicial.classList.remove('hidden');

                    habilitarCamposEvaluacion(contenedorOpcionesIniciales, true);

                    if (contenedorOpcionesIniciales) {
                        contenedorOpcionesIniciales.querySelectorAll('.fila-opcion-inicial-evaluacion').forEach(function (fila) {
                            configurarFilaOpcionInicialEvaluacion(fila, tipo);
                        });
                    }

                    if (bloqueOpcionesEdicion) bloqueOpcionesEdicion.classList.remove('hidden');
                    if (botonAgregarOpcionEdicion) botonAgregarOpcionEdicion.classList.remove('hidden');

                    habilitarCamposEvaluacion(contenedorOpcionesEdicion, true);

                    if (contenedorOpcionesEdicion) {
                        contenedorOpcionesEdicion.querySelectorAll('.fila-opcion-edicion-evaluacion').forEach(function (fila) {
                            configurarFilaOpcionEdicionEvaluacion(fila, tipo);
                        });
                    }
                }

                if (tipo === 'verdadero_falso') {
                    if (bloqueOpcionesIniciales) bloqueOpcionesIniciales.classList.remove('hidden');
                    if (bloqueVerdaderoFalsoInicial) bloqueVerdaderoFalsoInicial.classList.remove('hidden');

                    habilitarCamposEvaluacion(bloqueVerdaderoFalsoInicial, true);
                    habilitarCamposEvaluacion(contenedorOpcionesIniciales, false);

                    if (bloqueOpcionesEdicion) bloqueOpcionesEdicion.classList.remove('hidden');
                    if (bloqueVerdaderoFalsoEdicion) bloqueVerdaderoFalsoEdicion.classList.remove('hidden');

                    habilitarCamposEvaluacion(bloqueVerdaderoFalsoEdicion, true);
                    habilitarCamposEvaluacion(contenedorOpcionesEdicion, false);
                }

                if (tipo === 'completar') {
                    if (ayuda) ayuda.innerHTML = 'Completar en frase: primero escribí el enunciado de la pregunta; luego completá el texto antes, la respuesta correcta y el texto después del espacio en blanco.';
                    if (campoPreguntaNormal) campoPreguntaNormal.classList.remove('hidden');
                    if (campoCompletar) campoCompletar.classList.remove('hidden');
                } else if (tipo === 'checklist_guiado') {
                    if (ayuda) ayuda.innerHTML = 'Opción múltiple: agrega varias opciones y marca todas las respuestas correctas.';
                    if (campoImagenPreguntaOpciones) campoImagenPreguntaOpciones.classList.remove('hidden');
                    habilitarCamposEvaluacion(campoImagenPreguntaOpciones, true);
                } else if (tipo === 'seleccionar_posicion_imagen') {
                    if (ayuda) ayuda.innerHTML = 'Seleccionar orden: agrega cada campo, acción o elemento como opción. El campo Orden será el número correcto. La imagen de apoyo es opcional.';
                    if (campoPosicionImagen) campoPosicionImagen.classList.remove('hidden');
                } else if (tipo === 'verdadero_falso') {
                    if (ayuda) ayuda.innerHTML = 'Verdadero/Falso: usa las opciones fijas y marca cuál es correcta.';
                } else {
                    if (ayuda) ayuda.innerHTML = 'Pregunta de selección: agrega opciones y marca las respuestas correctas. Puedes cargar una imagen para que aparezca arriba de las opciones.';
                    if (tipo === 'opcion_unica') {
                        if (campoImagenPreguntaOpciones) campoImagenPreguntaOpciones.classList.remove('hidden');
                        habilitarCamposEvaluacion(campoImagenPreguntaOpciones, true);
                    }
                }
            }

            document.querySelectorAll('.btn-agregar-opcion-inicial-evaluacion').forEach(function (boton) {
                boton.addEventListener('click', function () {
                    const scope = this.dataset.scope;
                    const selectTipo = document.querySelector('.tipo-pregunta-evaluacion[data-scope="' + scope + '"]');
                    const contenedor = document.querySelector('.contenedor-opciones-iniciales-evaluacion[data-scope="' + scope + '"]');

                    if (!selectTipo || !contenedor) return;

                    const tipo = selectTipo.value;
                    const indice = parseInt(this.dataset.nextIndex || '0', 10);

                    const fila = crearFilaOpcionInicialEvaluacion(scope, tipo, indice);
                    contenedor.appendChild(fila);

                    this.dataset.nextIndex = indice + 1;
                });
            });

            document.querySelectorAll('.btn-agregar-opcion-edicion-evaluacion').forEach(function (boton) {
                boton.addEventListener('click', function () {
                    const scope = this.dataset.scope;
                    const selectTipo = document.querySelector('.tipo-pregunta-evaluacion[data-scope="' + scope + '"]');
                    const contenedor = document.querySelector('.contenedor-opciones-edicion-evaluacion[data-scope="' + scope + '"]');

                    if (!selectTipo || !contenedor) return;

                    const tipo = selectTipo.value;
                    const indice = parseInt(this.dataset.nextIndex || '0', 10);

                    const fila = crearFilaOpcionEdicionEvaluacion(scope, tipo, indice);
                    contenedor.appendChild(fila);

                    this.dataset.nextIndex = indice + 1;
                });
            });

            document.querySelectorAll('.tipo-pregunta-evaluacion').forEach(function (select) {
                select.addEventListener('change', function () {
                    actualizarFormularioPreguntaEvaluacion(this);
                });

                actualizarFormularioPreguntaEvaluacion(select);
            });
        });
    </script>

</x-app-layout>