<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Revisión administrativa
            </p>

            <h2 class="esf-seguimiento-title">
                Intento de evaluación
            </h2>

            <p class="esf-seguimiento-subtitle">
                Revisión de un intento específico: resumen, respuestas por pregunta y ajuste administrativo de calificación.
            </p>
        </div>
    </x-slot>

    <div class="py-8 esf-seguimiento-page esf-history-page esf-admin-detail-page">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @php
                $resultadoClase = (int) $intento->aprobado === 1
                    ? 'bg-green-100 text-green-800 border-green-300'
                    : 'bg-red-100 text-red-800 border-red-300';
            @endphp

            <div class="esf-history-card">
                <div class="esf-history-body">
                    <p class="esf-history-kicker">Resumen del intento</p>
                    <h3 class="esf-history-heading mb-4 text-2xl">{{ $intento->evaluacion?->titulo }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Empleado</strong>
                            <div class="mt-2 text-lg font-bold">{{ $seguimiento->empleado?->nombre_completo ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Módulo</strong>
                            <div class="mt-2 text-lg font-bold">{{ $intento->evaluacion?->capacitacionModulo?->titulo ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Intento</strong>
                            <div class="mt-2 text-lg font-bold">#{{ $intento->numero_intento }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Nota obtenida</strong>
                            <div class="mt-2 text-lg font-bold">{{ number_format((float) $intento->nota, 2) }}%</div>
                        </div>

                        <div class="rounded border {{ $resultadoClase }} px-4 py-3">
                            <strong>Resultado</strong>
                            <div class="mt-2 text-lg font-bold">
                                {{ (int) $intento->aprobado === 1 ? 'Aprobado' : 'Reprobado' }}
                            </div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Fecha fin</strong>
                            <div class="mt-2 text-lg font-bold">{{ $intento->fecha_fin?->format('d/m/Y H:i') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                        <div class="rounded border border-blue-300 bg-blue-100 px-4 py-3 text-blue-800">
                            <strong>Total preguntas</strong>
                            <div class="mt-2 text-lg font-bold">{{ $totalPreguntas }}</div>
                        </div>

                        <div class="rounded border border-green-300 bg-green-100 px-4 py-3 text-green-800">
                            <strong>Correctas</strong>
                            <div class="mt-2 text-lg font-bold">{{ $totalCorrectas }}</div>
                        </div>

                        <div class="rounded border border-red-300 bg-red-100 px-4 py-3 text-red-800">
                            <strong>Incorrectas</strong>
                            <div class="mt-2 text-lg font-bold">{{ $totalIncorrectas }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="esf-history-card">
                <div class="esf-history-body">
                    <p class="esf-history-kicker">Respuestas del empleado</p>
                    <h4 class="esf-history-heading mb-4">Detalle por pregunta</h4>

                    @forelse($respuestas as $index => $respuesta)
                        @php
                            $pregunta = $respuesta->pregunta;
                            $opcionUsuario = $respuesta->opcion;
                            $opcionCorrecta = $pregunta?->opciones?->firstWhere('es_correcta', 1);
                            $esCorrecta = (int) $respuesta->es_correcta === 1;
                        @endphp

                        <div class="mb-6 esf-attempt-question-card">
                            <div class="mb-3">
                                <h5 class="font-semibold text-lg">
                                    {{ $index + 1 }}. {{ $pregunta?->pregunta }}
                                </h5>
                                <p class="text-sm mt-1">
                                    <strong>Puntaje de la pregunta:</strong> {{ number_format((float) ($pregunta?->puntaje ?? 0), 2) }}
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="rounded border border-slate-300 bg-white px-4 py-3">
                                    <strong>Respuesta del empleado</strong>
                                    <div class="mt-2">
                                        {{ $opcionUsuario?->opcion ?? 'Sin respuesta registrada' }}
                                    </div>
                                </div>

                                <div class="rounded border border-slate-300 bg-white px-4 py-3">
                                    <strong>Respuesta correcta</strong>
                                    <div class="mt-2">
                                        {{ $opcionCorrecta?->opcion ?? 'No definida' }}
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div class="rounded px-4 py-3 {{ $esCorrecta ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <strong>Resultado:</strong>
                                    {{ $esCorrecta ? 'Correcta' : 'Incorrecta' }}
                                </div>

                                <div class="rounded px-4 py-3 bg-slate-100 text-slate-800">
                                    <strong>Puntaje obtenido:</strong>
                                    {{ number_format((float) $respuesta->puntaje_obtenido, 2) }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded border border-gray-300 bg-gray-100 px-4 py-3 text-gray-800">
                            No hay respuestas registradas para este intento.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="esf-history-card">
                <div class="esf-history-body">
                    <p class="esf-history-kicker">Revisión administrativa</p>
                    <h4 class="esf-history-heading mb-4">Editar calificación por pregunta</h4>

                    <div class="mb-4 rounded border border-blue-300 bg-blue-100 px-4 py-3 text-blue-800">
                        La nota inicial viene de la autocalificación. Esta edición sirve para corregir una nota si hubo un error o si se necesita ajustar manualmente una pregunta.
                    </div>

                    <form method="POST" action="{{ route('seguimiento_capacitaciones.intentos.revisar', [$seguimiento->id_empleado_capacitacion, $intento->id_evaluacion_intento]) }}" class="space-y-6">
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
                                        @endphp

                                        <tr>
                                            <td class="px-4 py-2 border align-top">
                                                <div class="font-semibold">
                                                    {{ $preguntaRevision?->pregunta }}
                                                </div>

                                                <div class="text-xs text-gray-500 mt-1">
                                                    Tipo: {{ $preguntaRevision?->tipo_pregunta }}
                                                </div>
                                            </td>

                                            <td class="px-4 py-2 border text-center align-top">
                                                {{ number_format($puntajeMaximo, 2) }}
                                            </td>

                                            <td class="px-4 py-2 border align-top">
                                                <input type="number"
                                                    name="respuestas[{{ $respuestaRevision->id_evaluacion_intento_respuesta }}][puntaje_obtenido]"
                                                    min="0"
                                                    max="{{ $puntajeMaximo }}"
                                                    step="0.01"
                                                    value="{{ old('respuestas.' . $respuestaRevision->id_evaluacion_intento_respuesta . '.puntaje_obtenido', $respuestaRevision->puntaje_obtenido) }}"
                                                    class="w-full border rounded px-3 py-2 text-black"
                                                    required>
                                            </td>

                                            <td class="px-4 py-2 border align-top">
                                                <textarea name="respuestas[{{ $respuestaRevision->id_evaluacion_intento_respuesta }}][comentario_revision]"
                                                        rows="3"
                                                        class="w-full border rounded px-3 py-2 text-black"
                                                        placeholder="Comentario opcional para esta pregunta">{{ old('respuestas.' . $respuestaRevision->id_evaluacion_intento_respuesta . '.comentario_revision', $respuestaRevision->comentario_revision) }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="esf-history-btn-green">
                                Guardar cambios de calificación
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('seguimiento_capacitaciones.show', $seguimiento->id_empleado_capacitacion) }}"
                   class="esf-history-btn-primary">
                    Volver al detalle de seguimiento
                </a>
            </div>

        </div>
    </div>
</x-app-layout>