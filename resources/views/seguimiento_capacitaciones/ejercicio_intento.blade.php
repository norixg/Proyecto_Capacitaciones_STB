<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Revisión administrativa
            </p>

            <h2 class="esf-seguimiento-title">
                Intento de ejercicio
            </h2>

            <p class="esf-seguimiento-subtitle">
                Revisión de un intento específico: resumen, respuestas, evidencias, puntajes y comentarios administrativos.
            </p>
        </div>
    </x-slot>

    <div class="py-8 esf-seguimiento-page esf-history-page esf-admin-detail-page">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="esf-history-card">
                <div class="esf-history-body">
                    <p class="esf-history-kicker">Resumen del intento</p>
                    <h3 class="esf-history-heading mb-4 text-2xl">{{ $intento->ejercicio?->titulo }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p><strong>Empleado:</strong> {{ $seguimiento->empleado?->nombre_completo }}</p>
                            <p><strong>Capacitación:</strong> {{ $seguimiento->capacitacion?->capacitacion }}</p>
                            <p><strong>Módulo:</strong> {{ $intento->ejercicio?->modulo?->titulo ?? '-' }}</p>
                        </div>

                        <div>
                            <p><strong>Intento:</strong> #{{ $intento->numero_intento }}</p>
                            <p><strong>Estado:</strong> {{ ucfirst(str_replace('_', ' ', $intento->estado)) }}</p>
                            <p><strong>Fecha fin:</strong> {{ $intento->fecha_fin ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @foreach($respuestas as $index => $respuesta)
                <div class="esf-history-card">
                    <div class="esf-history-body">
                        <h4 class="text-lg font-semibold mb-2">
                            {{ $index + 1 }}. {{ $respuesta->pregunta?->enunciado }}
                        </h4>

                        <p class="text-sm mb-4">
                            <strong>Tipo:</strong> {{ $respuesta->pregunta?->tipo_pregunta }}
                        </p>

                        @php
                            $pregunta = $respuesta->pregunta;
                            $tipo = $pregunta?->tipo_pregunta;
                            $datos = $respuesta->respuesta_json ? json_decode($respuesta->respuesta_json, true) : [];
                        @endphp

                        @if(optional($respuesta->pregunta)->tipo_pregunta === 'actividad_visual_identificacion')
                            @php
                                $configVisualSeguimiento = json_decode(optional($respuesta->pregunta)->configuracion_json ?? '{}', true);
                            @endphp

                            @if(!empty($configVisualSeguimiento['imagen']))
                                <div class="mb-4">
                                    <img src="{{ asset('storage/' . $configVisualSeguimiento['imagen']) }}" class="max-h-72 rounded border">
                                </div>
                            @endif
                        @endif

                        @if($tipo === 'opcion_unica' || $tipo === 'verdadero_falso')
                            @php
                                $idOpcion = (int) ($datos['opcion'] ?? 0);
                                $opcionSeleccionada = $pregunta?->opciones?->firstWhere('id_ejercicio_opcion', $idOpcion);
                                $opcionCorrecta = $pregunta?->opciones?->firstWhere('es_correcta', 1);
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="rounded border border-slate-300 bg-white px-4 py-3 text-black">
                                    <strong>Respuesta del usuario</strong>
                                    <div class="mt-2">
                                        {{ $opcionSeleccionada?->opcion ?? 'Sin selección' }}
                                    </div>
                                </div>

                                <div class="rounded border border-slate-300 bg-white px-4 py-3 text-black">
                                    <strong>Respuesta correcta</strong>
                                    <div class="mt-2">
                                        {{ $opcionCorrecta?->opcion ?? 'No definida' }}
                                    </div>
                                </div>
                            </div>

                        @elseif($tipo === 'opcion_multiple')
                            @php
                                $idsSeleccionadas = collect($datos['opciones'] ?? []);
                                $opcionesSeleccionadas = $pregunta?->opciones?->whereIn('id_ejercicio_opcion', $idsSeleccionadas)->values() ?? collect();
                                $opcionesCorrectas = $pregunta?->opciones?->where('es_correcta', 1)->values() ?? collect();
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="rounded border border-slate-300 bg-white px-4 py-3 text-black">
                                    <strong>Respuesta del usuario</strong>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @forelse($opcionesSeleccionadas as $opcion)
                                            <span class="px-3 py-1 rounded-full bg-slate-200 text-slate-800 text-sm">
                                                {{ $opcion->opcion }}
                                            </span>
                                        @empty
                                            <span>Sin selección</span>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="rounded border border-slate-300 bg-white px-4 py-3 text-black">
                                    <strong>Respuesta correcta</strong>

                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @forelse($opcionesCorrectas as $opcion)
                                            <span class="px-3 py-1 rounded-full bg-green-200 text-green-800 text-sm">
                                                {{ $opcion->opcion }}
                                            </span>
                                        @empty
                                            <span>No definida</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                        @elseif($tipo === 'ordenar')
                            @php
                                $ordenesUsuario = collect($datos['ordenes'] ?? []);
                                $opciones = $pregunta?->opciones?->values() ?? collect();

                                $tuOrden = $opciones->sortBy(function ($opcion) use ($ordenesUsuario) {
                                    return (int) ($ordenesUsuario[$opcion->id_ejercicio_opcion] ?? 999);
                                })->values();

                                $ordenCorrecto = $opciones->sortBy('orden')->values();
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="rounded border border-slate-300 bg-white px-4 py-3 text-black">
                                    <strong>Orden enviado por el usuario</strong>

                                    <div class="mt-3 space-y-2">
                                        @foreach($tuOrden as $idx => $opcion)
                                            <div class="flex items-center gap-3 rounded border border-slate-200 bg-slate-50 px-3 py-2">
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-700 text-white font-bold">
                                                    {{ $idx + 1 }}
                                                </span>
                                                <span>{{ $opcion->opcion }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded border border-slate-300 bg-white px-4 py-3 text-black">
                                    <strong>Orden correcto</strong>

                                    <div class="mt-3 space-y-2">
                                        @foreach($ordenCorrecto as $idx => $opcion)
                                            <div class="flex items-center gap-3 rounded border border-green-200 bg-green-50 px-3 py-2">
                                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-green-600 text-white font-bold">
                                                    {{ $idx + 1 }}
                                                </span>
                                                <span>{{ $opcion->opcion }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                        @elseif($tipo === 'relacionar')
                            @php
                                $relacionesUsuario = collect($datos['relaciones'] ?? []);
                                $izquierdas = $pregunta?->opciones?->where('lado', 'izquierda')->values() ?? collect();
                                $derechas = $pregunta?->opciones?->where('lado', 'derecha')->keyBy('id_ejercicio_opcion') ?? collect();
                            @endphp

                            <div class="rounded border border-slate-300 bg-white px-4 py-3 text-black">
                                <strong>Relaciones enviadas</strong>

                                <div class="mt-4 overflow-x-auto">
                                    <table class="esf-history-table">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-4 py-2 border text-left">Elemento</th>
                                                <th class="px-4 py-2 border text-left">Respuesta del usuario</th>
                                                <th class="px-4 py-2 border text-left">Relación correcta</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($izquierdas as $izquierda)
                                                @php
                                                    $idDerechaUsuario = (int) ($relacionesUsuario[$izquierda->id_ejercicio_opcion] ?? 0);
                                                    $opcionUsuario = $derechas->get($idDerechaUsuario);

                                                    $opcionCorrecta = $pregunta->opciones
                                                        ->where('lado', 'derecha')
                                                        ->firstWhere('clave_relacion', $izquierda->clave_relacion);
                                                @endphp

                                                <tr>
                                                    <td class="px-4 py-2 border">{{ $izquierda->opcion }}</td>
                                                    <td class="px-4 py-2 border">{{ $opcionUsuario?->opcion ?? 'Sin asignar' }}</td>
                                                    <td class="px-4 py-2 border">{{ $opcionCorrecta?->opcion ?? 'No definida' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        @elseif(in_array($tipo, ['respuesta_corta', 'caso_practico']))
                            @php
                                $configTextoAdmin = json_decode($pregunta?->configuracion_json ?? '{}', true);
                            @endphp

                            <div class="rounded border border-slate-300 bg-slate-50 p-4 mt-3">
                                <h6 class="font-semibold text-slate-800 mb-2">
                                    {{ $tipo === 'caso_practico' ? 'Caso de estudio' : 'Respuesta breve' }}
                                </h6>

                                @if(!empty($configTextoAdmin['criterios_revision']))
                                    <div class="mb-3 rounded border border-amber-300 bg-amber-100 px-3 py-2 text-amber-800 text-sm">
                                        <strong>Criterios de revisión:</strong> {{ $configTextoAdmin['criterios_revision'] }}
                                    </div>
                                @endif

                                <div class="rounded border bg-white px-4 py-3 text-slate-900 whitespace-pre-line">
                                    {{ $respuesta->respuesta_texto ?: 'Sin respuesta' }}
                                </div>
                            </div>

                        @elseif($tipo === 'completar')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="rounded border border-slate-300 bg-white px-4 py-3 text-black">
                                    <strong>Respuesta del usuario</strong>
                                    <div class="mt-2 whitespace-pre-line">
                                        {{ $respuesta->respuesta_texto ?: 'Sin respuesta' }}
                                    </div>
                                </div>

                                <div class="rounded border border-slate-300 bg-white px-4 py-3 text-black">
                                    <strong>Respuesta correcta</strong>
                                    <div class="mt-2 whitespace-pre-line">
                                        {{ $pregunta?->respuesta_correcta_texto ?: 'No definida' }}
                                    </div>
                                </div>
                            </div>

                        @else
                            <div class="rounded border border-slate-300 bg-white px-4 py-3 text-black">
                                <strong>Respuesta visible</strong>
                                <div class="mt-2 whitespace-pre-line">
                                    {{ $respuesta->respuesta_texto ?: 'Sin respuesta visible.' }}
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            @endforeach

                        <div class="esf-history-card">
                            <div class="esf-history-body">
                                <p class="esf-history-kicker">Revisión administrativa</p>
                                <h4 class="esf-history-heading mb-4">Editar calificación por pregunta</h4>

                                <div class="mb-4 rounded border border-blue-300 bg-blue-100 px-4 py-3 text-blue-800">
                                    La nota inicial viene de la autocalificación. Para respuesta breve, caso de estudio e identificación visual, la revisión manual es obligatoria. Esta matriz permanece editable después de guardar.
                                </div>

                                <form method="POST" action="{{ route('seguimiento_capacitaciones.ejercicio_intento.revisar', [$seguimiento->id_empleado_capacitacion, $intento->id_ejercicio_intento]) }}" class="space-y-6">
                                    @csrf
                                    @method('PUT')

                                    <div class="overflow-x-auto">
                                        <table class="esf-history-table">
                                            <thead class="bg-gray-100 text-black">
                                                <tr>
                                                    <th class="px-4 py-2 border text-left">Pregunta</th>
                                                    <th class="px-4 py-2 border">Puntaje máximo</th>
                                                    <th class="px-4 py-2 border">Puntaje obtenido</th>
                                                    <th class="px-4 py-2 border text-left">Comentario por pregunta</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach($respuestas as $respuestaRevision)
                                                    @php
                                                        $preguntaRevision = $respuestaRevision->pregunta;
                                                        $puntajeMaximo = (float) ($preguntaRevision?->puntaje ?? 0);

                                                        $tipoRevision = $preguntaRevision?->tipo_pregunta;

                                                        $revisionObligatoria = in_array($tipoRevision, [
                                                            'respuesta_corta',
                                                            'caso_practico',
                                                            'actividad_visual_identificacion',
                                                        ], true)
                                                            || (int) ($preguntaRevision?->requiere_revision_manual ?? 0) === 1
                                                            || (int) ($intento->ejercicio?->requiere_revision_manual ?? 0) === 1
                                                            || is_null($respuestaRevision->es_correcta);
                                                    @endphp

                                                    <tr>
                                                        <td class="px-4 py-2 border align-top">
                                                            <div class="font-semibold">
                                                                {{ $preguntaRevision?->enunciado }}
                                                            </div>

                                                            <div class="text-xs text-gray-500 mt-1">
                                                                Tipo: {{ $tipoRevision }}
                                                            </div>

                                                            @if($revisionObligatoria)
                                                                <div class="mt-2 text-xs text-orange-700">
                                                                    Requiere revisión manual.
                                                                </div>
                                                            @else
                                                                <div class="mt-2 text-xs text-green-700">
                                                                    Autocalificada, pero editable por corrección.
                                                                </div>
                                                            @endif
                                                        </td>

                                                        <td class="px-4 py-2 border text-center align-top">
                                                            {{ number_format($puntajeMaximo, 2) }}
                                                        </td>

                                                        <td class="px-4 py-2 border align-top">
                                                            <input type="number"
                                                                name="respuestas[{{ $respuestaRevision->id_ejercicio_intento_respuesta }}][puntaje_obtenido]"
                                                                min="0"
                                                                max="{{ $puntajeMaximo }}"
                                                                step="0.01"
                                                                value="{{ old('respuestas.' . $respuestaRevision->id_ejercicio_intento_respuesta . '.puntaje_obtenido', $respuestaRevision->puntaje_obtenido) }}"
                                                                class="w-full border rounded px-3 py-2 text-black"
                                                                required>
                                                        </td>

                                                        <td class="px-4 py-2 border align-top">
                                                            <textarea name="respuestas[{{ $respuestaRevision->id_ejercicio_intento_respuesta }}][comentario_revision]"
                                                                    rows="3"
                                                                    class="w-full border rounded px-3 py-2 text-black"
                                                                    placeholder="{{ $revisionObligatoria ? 'Comentario recomendado para esta revisión' : 'Comentario opcional' }}">{{ old('respuestas.' . $respuestaRevision->id_ejercicio_intento_respuesta . '.comentario_revision', $respuestaRevision->comentario_revision) }}</textarea>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div>
                                        <label class="block mb-1 text-sm font-medium">Comentario general del intento</label>
                                        <textarea name="comentario_revision"
                                                rows="4"
                                                class="w-full border rounded px-3 py-2 text-black">{{ old('comentario_revision', $intento->comentario_revision) }}</textarea>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <button type="submit" class="esf-history-btn-green">
                                            Guardar cambios de calificación
                                        </button>

                                        <a href="{{ route('seguimiento_capacitaciones.show', $seguimiento->id_empleado_capacitacion) }}"
                                        class="esf-history-btn-primary">
                                            Volver al detalle de seguimiento
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

        </div>
    </div>
</x-app-layout>