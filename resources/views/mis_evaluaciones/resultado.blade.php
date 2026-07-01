<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Resultado de evaluación
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-emerald-50 py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @php
                $resultadoClase = (int) $intento->aprobado === 1
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                    : 'border-red-200 bg-red-50 text-red-700';
            @endphp

            <div class="rounded-[2rem] border border-sky-100 bg-white/90 shadow-xl shadow-sky-100/50 backdrop-blur overflow-hidden">
                <div class="p-7 text-slate-900">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-sky-500 mb-2">
                        Resultado de evaluación
                    </p>

                    <h3 class="text-3xl font-black tracking-tight text-slate-900 mb-4">
                        {{ $intento->evaluacion?->titulo }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="group rounded-3xl border border-sky-100 bg-gradient-to-br from-white to-sky-50 px-5 py-4 text-slate-800 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-sky-500">Intento</p>
                            <div class="mt-2 text-2xl font-black text-slate-900">#{{ $intento->numero_intento }}</div>
                        </div>

                        <div class="group rounded-3xl border border-sky-100 bg-gradient-to-br from-white to-blue-50 px-5 py-4 text-slate-800 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-sky-500">Nota obtenida</p>
                            <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format((float) $intento->nota, 2) }}%</div>
                        </div>

                        <div class="group rounded-3xl border px-5 py-4 shadow-sm hover:-translate-y-1 hover:shadow-xl transition {{ $resultadoClase }}">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em]">Resultado</p>
                            <div class="mt-2 text-2xl font-black">
                                {{ (int) $intento->aprobado === 1 ? 'Aprobado' : 'Reprobado' }}
                            </div>
                        </div>

                        <div class="group rounded-3xl border border-sky-100 bg-gradient-to-br from-white to-slate-50 px-5 py-4 text-slate-800 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-sky-500">Fecha fin</p>
                            <div class="mt-2 text-lg font-black text-slate-900">{{ $intento->fecha_fin ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                        <div class="rounded-3xl border border-sky-200 bg-gradient-to-br from-sky-50 to-white px-5 py-4 text-sky-800 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em]">Total preguntas</p>
                            <div class="mt-2 text-2xl font-black">{{ $totalPreguntas }}</div>
                        </div>

                        <div class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white px-5 py-4 text-emerald-800 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em]">Correctas</p>
                            <div class="mt-2 text-2xl font-black">{{ $totalCorrectas }}</div>
                        </div>

                        <div class="rounded-3xl border border-red-200 bg-gradient-to-br from-red-50 to-white px-5 py-4 text-red-700 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em]">Incorrectas</p>
                            <div class="mt-2 text-2xl font-black">{{ $totalIncorrectas }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-sky-100 bg-white/90 shadow-xl shadow-sky-100/50 backdrop-blur overflow-hidden">
                <div class="p-7 text-slate-900">
                    <div class="mb-5">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-sky-500">
                            Revisión
                        </p>
                        <h4 class="text-xl font-black text-slate-900">
                            Detalle por pregunta
                        </h4>
                    </div>

                    @forelse($respuestas as $index => $respuesta)
                        @php
                            $pregunta = $respuesta->pregunta;
                            $tipo = $pregunta?->tipo_pregunta;

                            $datosRespuesta = $respuesta->respuesta_texto
                                ? json_decode($respuesta->respuesta_texto, true)
                                : [];

                            if (!is_array($datosRespuesta)) {
                                $datosRespuesta = [];
                            }

                            $opcionUsuario = $respuesta->opcion;
                            $opcionCorrecta = $pregunta?->opciones?->firstWhere('es_correcta', 1);

                            $puntajeMaximoPregunta = (float) ($pregunta?->puntaje ?? 0);
                            $puntajeObtenidoPregunta = (float) ($respuesta->puntaje_obtenido ?? 0);

                            $esCorrecta = (int) $respuesta->es_correcta === 1;
                            $esParcial = !$esCorrecta
                                && $puntajeObtenidoPregunta > 0
                                && $puntajeObtenidoPregunta < $puntajeMaximoPregunta;

                            if ($esCorrecta) {
                                $resultadoTextoPregunta = 'Correcta';
                                $resultadoClasePregunta = 'bg-emerald-50 text-emerald-800 border border-emerald-200';
                                $resultadoBordePregunta = 'border-emerald-200 bg-emerald-50/70';
                            } elseif ($esParcial) {
                                $resultadoTextoPregunta = 'Parcialmente correcta';
                                $resultadoClasePregunta = 'bg-amber-50 text-amber-800 border border-amber-200';
                                $resultadoBordePregunta = 'border-amber-200 bg-amber-50/70';
                            } else {
                                $resultadoTextoPregunta = 'Incorrecta';
                                $resultadoClasePregunta = 'bg-red-50 text-red-700 border border-red-200';
                                $resultadoBordePregunta = 'border-red-200 bg-red-50/70';
                            }
                        @endphp

                        <div class="mb-6 rounded-[1.6rem] border p-5 shadow-sm hover:shadow-md transition {{ $resultadoBordePregunta }}">
                            <div class="mb-3">
                                <h5 class="font-semibold text-lg">
                                    {{ $index + 1 }}. {{ $pregunta?->pregunta }}
                                </h5>
                                <p class="text-sm mt-1">
                                    <strong>Puntaje de la pregunta:</strong> {{ number_format((float) ($pregunta?->puntaje ?? 0), 2) }}
                                </p>
                            </div>

                            @if($tipo === 'opcion_unica' || $tipo === 'verdadero_falso')
                                <div class="rounded-2xl border border-sky-100 bg-white/90 px-4 py-4 text-slate-900 shadow-sm">
                                    <strong>Opciones de la pregunta</strong>

                                    <div class="mt-4 space-y-2">
                                        @foreach(($pregunta?->opciones?->sortBy('orden')->values() ?? collect()) as $opcion)
                                            @php
                                                $seleccionada = $opcionUsuario && (int) $opcionUsuario->id_evaluacion_opcion === (int) $opcion->id_evaluacion_opcion;
                                                $debeSerCorrecta = (int) $opcion->es_correcta === 1;

                                                if ($seleccionada && $debeSerCorrecta) {
                                                    $claseOpcion = 'border-green-300 bg-green-100 text-green-900';
                                                    $estadoOpcion = 'Correcta seleccionada';
                                                } elseif ($seleccionada && !$debeSerCorrecta) {
                                                    $claseOpcion = 'border-red-300 bg-red-100 text-red-900';
                                                    $estadoOpcion = 'Incorrecta seleccionada';
                                                } elseif (!$seleccionada && $debeSerCorrecta) {
                                                    $claseOpcion = 'border-green-300 bg-green-50 text-green-900';
                                                    $estadoOpcion = 'Respuesta correcta';
                                                } else {
                                                    $claseOpcion = 'border-slate-200 bg-slate-50 text-slate-700';
                                                    $estadoOpcion = 'No seleccionada';
                                                }
                                            @endphp

                                            <div class="rounded-xl border px-4 py-3 shadow-sm {{ $claseOpcion }}">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span>{{ $opcion->opcion }}</span>
                                                    <span class="text-xs font-bold">{{ $estadoOpcion }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            @elseif($tipo === 'checklist_guiado' || $tipo === 'opcion_multiple')
                                @php
                                    $idsUsuario = collect($datosRespuesta['opciones'] ?? $datosRespuesta['items'] ?? [])
                                        ->map(fn ($id) => (int) $id);

                                    $opciones = $pregunta?->opciones?->sortBy('orden')->values() ?? collect();
                                @endphp

                                <div class="rounded-2xl border border-sky-100 bg-white/90 px-4 py-4 text-slate-900 shadow-sm">
                                    <strong>Opciones de la pregunta</strong>

                                    <div class="mt-4 space-y-2">
                                        @foreach($opciones as $opcion)
                                            @php
                                                $seleccionada = $idsUsuario->contains((int) $opcion->id_evaluacion_opcion);
                                                $debeSerCorrecta = (int) $opcion->es_correcta === 1;

                                                    if ($seleccionada === $debeSerCorrecta) {
                                                        $claseOpcion = 'border-emerald-300 bg-emerald-100 text-emerald-900';
                                                        $estadoOpcion = $seleccionada ? 'Correcta seleccionada' : 'Correctamente no seleccionada';
                                                    } else {
                                                        $claseOpcion = 'border-red-300 bg-red-100 text-red-900';
                                                        $estadoOpcion = $seleccionada ? 'Incorrecta seleccionada' : 'Correcta omitida';
                                                    }
                                            @endphp

                                            <div class="rounded-xl border px-4 py-3 shadow-sm {{ $claseOpcion }}">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span>{{ $opcion->opcion }}</span>
                                                    <span class="text-xs font-bold">{{ $estadoOpcion }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            @elseif($tipo === 'seleccionar_posicion_imagen')
                                @php
                                    $configPosicionResultado = json_decode($pregunta?->configuracion_json ?? '{}', true);

                                    $cantidadConfig = (int) ($configPosicionResultado['cantidad_posiciones'] ?? 0);
                                    $cantidadOpciones = (int) ($pregunta?->opciones?->count() ?? 0);
                                    $maximoOrdenOpciones = (int) ($pregunta?->opciones?->max('orden') ?? 0);

                                    $cantidadPosiciones = max($cantidadConfig, $cantidadOpciones, $maximoOrdenOpciones, 1);

                                    $posicionesUsuario = collect($datosRespuesta['posiciones'] ?? []);
                                    $opciones = $pregunta?->opciones?->sortBy('orden')->values() ?? collect();
                                @endphp

                                <div class="rounded-2xl border border-sky-100 bg-white/90 px-4 py-4 text-slate-900 shadow-sm">
                                    @if(!empty($configPosicionResultado['imagen']))
                                        <div class="mb-4 text-center">
                                            <img src="{{ asset('storage/' . $configPosicionResultado['imagen']) }}"
                                                class="max-h-80 rounded border bg-white p-2 mx-auto">
                                        </div>
                                    @endif

                                    <strong>Orden seleccionado</strong>

                                    <div class="mt-4 overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead class="bg-gradient-to-r from-sky-50 to-emerald-50 text-slate-700">
                                                <tr>
                                                    <th class="px-3 py-2 border text-left">Parte ilustrada</th>
                                                    <th class="px-3 py-2 border text-center">Tu ordenados</th>
                                                    <th class="px-3 py-2 border text-center">Posición correcta</th>
                                                    <th class="px-3 py-2 border text-center">Estado</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach($opciones as $opcion)
                                                    @php
                                                        $posicionUsuario = (int) ($posicionesUsuario[$opcion->id_evaluacion_opcion] ?? 0);
                                                        $posicionCorrecta = (int) $opcion->orden;
                                                        $filaCorrecta = $posicionUsuario === $posicionCorrecta;
                                                    @endphp

                                                    <tr class="{{ $filaCorrecta ? 'bg-green-50' : 'bg-red-50' }}">
                                                        <td class="px-3 py-2 border font-semibold">{{ $opcion->opcion }}</td>
                                                        <td class="px-3 py-2 border text-center">{{ $posicionUsuario ?: 'Sin responder' }}</td>
                                                        <td class="px-3 py-2 border text-center">{{ $posicionCorrecta }}</td>
                                                        <td class="px-3 py-2 border text-center">
                                                            @if($filaCorrecta)
                                                                <span class="px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-bold">
                                                                    Correcta
                                                                </span>
                                                            @else
                                                                <span class="px-2 py-1 rounded bg-red-100 text-red-800 text-xs font-bold">
                                                                    Incorrecta
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            @else
                                @php
                                    $respuestaTextoLimpia = $opcionUsuario?->opcion ?? $respuesta->respuesta_texto ?? '';

                                    $respuestaJsonLimpia = $respuesta->respuesta_texto
                                        ? json_decode($respuesta->respuesta_texto, true)
                                        : null;

                                    if (is_array($respuestaJsonLimpia)) {
                                        if (isset($respuestaJsonLimpia['respuesta_usuario'])) {
                                            $respuestaTextoLimpia = $respuestaJsonLimpia['respuesta_usuario'];
                                        } elseif (isset($respuestaJsonLimpia['opciones']) || isset($respuestaJsonLimpia['items']) || isset($respuestaJsonLimpia['posiciones'])) {
                                            $respuestaTextoLimpia = 'Respuesta registrada en las opciones de la pregunta.';
                                        } else {
                                            $respuestaTextoLimpia = 'Respuesta registrada.';
                                        }
                                    }

                                    if (blank($respuestaTextoLimpia)) {
                                        $respuestaTextoLimpia = 'Sin respuesta registrada';
                                    }

                                    $respuestaCorrectaLimpia = $opcionCorrecta?->opcion ?? $pregunta?->respuesta_correcta_texto ?? '';

                                    $respuestaCorrectaJson = $pregunta?->respuesta_correcta_texto
                                        ? json_decode($pregunta->respuesta_correcta_texto, true)
                                        : null;

                                    if (is_array($respuestaCorrectaJson)) {
                                        $respuestaCorrectaLimpia = 'Respuesta esperada registrada en configuración.';
                                    }

                                    if (blank($respuestaCorrectaLimpia)) {
                                        $respuestaCorrectaLimpia = 'No definida';
                                    }
                                @endphp

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="rounded-2xl border border-sky-100 bg-white/90 px-4 py-4 text-slate-900 shadow-sm">
                                        <strong>Tu respuesta</strong>
                                        <div class="mt-2 whitespace-pre-line break-words">
                                            {{ $respuestaTextoLimpia }}
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-sky-100 bg-white/90 px-4 py-4 text-slate-900 shadow-sm">
                                        <strong>Respuesta correcta</strong>
                                        <div class="mt-2 whitespace-pre-line break-words">
                                            {{ $respuestaCorrectaLimpia }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div class="rounded-2xl px-4 py-3 font-bold shadow-sm {{ $resultadoClasePregunta }}">
                                    <strong>Resultado:</strong>
                                    {{ $resultadoTextoPregunta }}
                                </div>

                                <div class="rounded-2xl border border-sky-100 bg-white/90 px-4 py-3 font-bold text-slate-800 shadow-sm">
                                    <strong>Puntaje obtenido:</strong>
                                    {{ number_format($puntajeObtenidoPregunta, 2) }} / {{ number_format($puntajeMaximoPregunta, 2) }}
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

            <div class="flex gap-3">
                <a href="{{ route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $intento->evaluacion->id_capacitacion_modulo]) }}#examen-general-modulo"
                    target="{{ request()->boolean('integrado_modulo') ? '_top' : '_self' }}"
                    class="inline-flex items-center rounded-full bg-slate-600 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-slate-200 hover:-translate-y-0.5 hover:shadow-xl transition">
                        Volver al módulo
                </a>

                <a href="{{ route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $intento->evaluacion->id_capacitacion_modulo]) }}?evaluacion_integrada={{ $intento->id_evaluacion }}#examen-general-modulo"
                    target="{{ request()->boolean('integrado_modulo') ? '_top' : '_self' }}"
                    class="inline-flex items-center rounded-full bg-slate-900 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-slate-300 hover:-translate-y-0.5 hover:shadow-xl transition">
                        Volver a la evaluación
                </a>
            </div>

        </div>
    </div>
</x-app-layout>