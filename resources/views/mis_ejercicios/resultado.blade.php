<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Resultado de ejercicio
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-emerald-50 py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @php
                $estadoClase = match($intento->estado) {
                    'finalizado' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                    'pendiente_revision' => 'border-amber-200 bg-amber-50 text-amber-800',
                    default => 'border-slate-200 bg-slate-50 text-slate-700',
                };
            @endphp

            <div class="rounded-[2rem] border border-sky-100 bg-white/90 shadow-xl shadow-sky-100/50 backdrop-blur overflow-hidden">
                <div class="p-7 text-slate-900">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-sky-500 mb-2">
                        Resultado del ejercicio
                    </p>

                    <h3 class="text-3xl font-black tracking-tight text-slate-900 mb-4">
                        {{ $intento->ejercicio?->titulo }}
                    </h3>

                    @if($intento->ejercicio?->descripcion)
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ $intento->ejercicio->descripcion }}
                        </p>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="group rounded-3xl border border-sky-100 bg-gradient-to-br from-white to-sky-50 px-5 py-4 text-slate-800 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-sky-500">Intento</p>
                            <div class="mt-2 text-2xl font-black text-slate-900">#{{ $intento->numero_intento }}</div>
                        </div>

                        <div class="group rounded-3xl border px-5 py-4 shadow-sm hover:-translate-y-1 hover:shadow-xl transition {{ $estadoClase }}">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em]">Estado</p>
                            <div class="mt-2 text-2xl font-black">
                                {{ ucfirst(str_replace('_', ' ', $intento->estado)) }}
                            </div>
                        </div>

                        <div class="group rounded-3xl border border-sky-100 bg-gradient-to-br from-white to-blue-50 px-5 py-4 text-slate-800 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-sky-500">Resultado</p>
                            <div class="mt-2 text-2xl font-black text-slate-900">
                                @if(is_null($intento->porcentaje_obtenido))
                                    Pendiente
                                @else
                                    {{ number_format((float) $intento->porcentaje_obtenido, 2) }}%
                                @endif
                            </div>
                        </div>

                        <div class="group rounded-3xl border border-sky-100 bg-gradient-to-br from-white to-slate-50 px-5 py-4 text-slate-800 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-sky-500">Fecha fin</p>
                            <div class="mt-2 text-lg font-black text-slate-900">{{ $intento->fecha_fin ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
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

                        <div class="rounded-3xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white px-5 py-4 text-amber-800 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em]">Pendientes revisión</p>
                            <div class="mt-2 text-2xl font-black">{{ $totalPendientesRevision }}</div>
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
                            $datos = $respuesta->respuesta_json ? json_decode($respuesta->respuesta_json, true) : [];

                            $puntajeMaximoPregunta = (float) ($pregunta?->puntaje ?? 0);
                            $puntajeObtenidoPregunta = (float) ($respuesta->puntaje_obtenido ?? 0);

                            $esPendienteRevision = is_null($respuesta->es_correcta);
                            $esCorrectaCompleta = !$esPendienteRevision && (int) $respuesta->es_correcta === 1;
                            $esParcial = !$esPendienteRevision
                                && !$esCorrectaCompleta
                                && $puntajeObtenidoPregunta > 0
                                && $puntajeObtenidoPregunta < $puntajeMaximoPregunta;

                            if ($esPendienteRevision) {
                                $resultadoTexto = 'Pendiente de revisión';
                                $resultadoClase = 'bg-amber-50 text-amber-800 border-amber-200';
                                $resultadoBordeClase = 'border-amber-200 bg-amber-50/70';
                            } elseif ($esCorrectaCompleta) {
                                $resultadoTexto = 'Correcta';
                                $resultadoClase = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                                $resultadoBordeClase = 'border-emerald-200 bg-emerald-50/70';
                            } elseif ($esParcial) {
                                $resultadoTexto = 'Parcialmente correcta';
                                $resultadoClase = 'bg-amber-50 text-amber-800 border-amber-200';
                                $resultadoBordeClase = 'border-amber-200 bg-amber-50/70';
                            } else {
                                $resultadoTexto = 'Incorrecta';
                                $resultadoClase = 'bg-red-50 text-red-700 border-red-200';
                                $resultadoBordeClase = 'border-red-200 bg-red-50/70';
                            }
                        @endphp

                        <div class="mb-6 rounded-[1.6rem] border p-5 shadow-sm hover:shadow-md transition {{ $resultadoBordeClase }}">
                            <div class="mb-4">
                                <h5 class="font-semibold text-lg">
                                    {{ $index + 1 }}. {{ $pregunta?->enunciado }}
                                </h5>

                                <p class="text-sm mt-1">
                                    <strong>Tipo:</strong> {{ $tipo }}
                                    · <strong>Puntaje:</strong> {{ number_format((float) ($pregunta?->puntaje ?? 0), 2) }}
                                </p>
                            </div>

                            <div class="mb-4 rounded-2xl border px-4 py-3 text-sm font-bold shadow-sm {{ $resultadoClase }}">
                                <strong>Resultado:</strong> {{ $resultadoTexto }}
                                <span class="ml-3">
                                    · <strong>Puntaje obtenido:</strong>
                                    {{ number_format((float) ($respuesta->puntaje_obtenido ?? 0), 2) }}
                                    /
                                    {{ number_format((float) ($pregunta?->puntaje ?? 0), 2) }}
                                </span>

                                @if(!empty($respuesta->comentario_revision))
                                    <div class="mt-3 rounded border border-blue-300 bg-blue-100 px-3 py-2 text-blue-800">
                                        <strong>Comentario del revisor:</strong>
                                        <div class="mt-1 whitespace-pre-line">{{ $respuesta->comentario_revision }}</div>
                                    </div>
                                @endif
                            </div>

                            @if(optional($respuesta->pregunta)->tipo_pregunta === 'actividad_visual_identificacion')
                                @php
                                    $configVisualResultado = json_decode(optional($respuesta->pregunta)->configuracion_json ?? '{}', true);
                                @endphp

                                @if(!empty($configVisualResultado['imagen']))
                                    <div class="mb-4">
                                        <img src="{{ asset('storage/' . $configVisualResultado['imagen']) }}" class="max-h-72 rounded border">
                                    </div>
                                @endif
                            @endif

                            @if($tipo === 'opcion_unica' || $tipo === 'verdadero_falso')
                                @php
                                    $idOpcion = (int) ($datos['opcion'] ?? 0);
                                    $opciones = $pregunta?->opciones?->sortBy('orden')->values() ?? collect();
                                @endphp

                                <div class="rounded-2xl border border-slate-200 bg-white/90 px-4 py-4 text-slate-900 shadow-sm">
                                    <strong>Opciones de la pregunta</strong>

                                    <div class="mt-4 space-y-2">
                                        @foreach($opciones as $opcion)
                                            @php
                                                $seleccionada = $idOpcion === (int) $opcion->id_ejercicio_opcion;
                                                $debeSerCorrecta = (int) $opcion->es_correcta === 1;

                                                if ($seleccionada && $debeSerCorrecta) {
                                                    $claseOpcion = 'border-emerald-300 bg-emerald-100 text-emerald-900';
                                                    $estadoOpcion = 'Correcta seleccionada';
                                                } elseif ($seleccionada && !$debeSerCorrecta) {
                                                    $claseOpcion = 'border-red-300 bg-red-100 text-red-900';
                                                    $estadoOpcion = 'Incorrecta seleccionada';
                                                } elseif (!$seleccionada && $debeSerCorrecta) {
                                                    $claseOpcion = 'border-emerald-300 bg-emerald-50 text-emerald-900';
                                                    $estadoOpcion = 'Respuesta correcta';
                                                } else {
                                                    $claseOpcion = 'border-slate-200 bg-slate-50 text-slate-700';
                                                    $estadoOpcion = 'No seleccionada';
                                                }
                                            @endphp

                                            <div class="rounded-xl border px-4 py-3 shadow-sm {{ $claseOpcion }}">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="font-semibold">{{ $opcion->opcion }}</span>
                                                    <span class="text-xs font-black uppercase tracking-wide">{{ $estadoOpcion }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            {{-- OPCIÓN MÚLTIPLE --}}
                            @elseif($tipo === 'checklist_guiado' || $tipo === 'opcion_multiple')
                                @php
                                    $idsSeleccionadas = collect($datos['items'] ?? $datos['opciones'] ?? [])
                                        ->map(fn ($id) => (int) $id);

                                    $opciones = $pregunta?->opciones?->sortBy('orden')->values() ?? collect();
                                @endphp

                                <div class="rounded-2xl border border-slate-200 bg-white/90 px-4 py-4 text-slate-900 shadow-sm">
                                    <strong>Opciones de la pregunta</strong>

                                    <div class="mt-4 space-y-2">
                                        @foreach($opciones as $opcion)
                                            @php
                                                $seleccionada = $idsSeleccionadas->contains((int) $opcion->id_ejercicio_opcion);
                                                $debeSerCorrecta = (int) $opcion->es_correcta === 1;

                                                if ($seleccionada === $debeSerCorrecta) {
                                                    $claseOpcion = 'border-emerald-300 bg-emerald-100 text-emerald-900';
                                                    $estadoOpcion = $seleccionada ? 'Correcta seleccionada' : 'Correctamente no seleccionada';
                                                } else {
                                                    $claseOpcion = 'border-red-300 bg-red-100 text-red-900';
                                                    $estadoOpcion = $seleccionada ? 'Incorrecta seleccionada' : 'Correcta omitida';
                                                }
                                            @endphp

                                            <div class="rounded-xl border px-4 py-3 {{ $claseOpcion }}">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="font-semibold">{{ $opcion->opcion }}</span>
                                                    <span class="text-xs font-black uppercase tracking-wide">{{ $estadoOpcion }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            {{-- SELECCIONAR POSICIÓN SEGÚN IMAGEN --}}
                            @elseif($tipo === 'seleccionar_posicion_imagen')
                                @php
                                    $configPosicionResultado = json_decode($pregunta?->configuracion_json ?? '{}', true);

                                    $cantidadConfig = (int) ($configPosicionResultado['cantidad_posiciones'] ?? 0);
                                    $cantidadOpciones = (int) ($pregunta?->opciones?->count() ?? 0);
                                    $maximoOrdenOpciones = (int) ($pregunta?->opciones?->max('orden') ?? 0);

                                    $cantidadPosiciones = max($cantidadConfig, $cantidadOpciones, $maximoOrdenOpciones, 1);

                                    $posicionesUsuario = collect($datos['posiciones'] ?? []);
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
                                                    <th class="px-3 py-2 border text-center">Tu posición</th>
                                                    <th class="px-3 py-2 border text-center">Tu orden</th>
                                                    <th class="px-3 py-2 border text-center">Estado</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach($opciones as $opcion)
                                                    @php
                                                        $posicionUsuario = (int) ($posicionesUsuario[$opcion->id_ejercicio_opcion] ?? 0);
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

                            {{-- ORDENAR --}}
                            @elseif($tipo === 'ordenar')
                                @php
                                    $ordenesUsuario = collect($datos['ordenes'] ?? []);
                                    $opciones = $pregunta?->opciones?->sortBy('orden')->values() ?? collect();
                                @endphp

                                <div class="rounded-2xl border border-slate-200 bg-white/90 px-4 py-4 text-slate-900 shadow-sm">
                                    <strong>Orden / priorización</strong>

                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gradient-to-r from-sky-50 to-emerald-50 text-slate-700">
                                                <tr>
                                                    <th class="px-3 py-2 border text-left">Opción</th>
                                                    <th class="px-3 py-2 border text-center">Tu posición</th>
                                                    <th class="px-3 py-2 border text-center">Posición correcta</th>
                                                    <th class="px-3 py-2 border text-center">Estado</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach($opciones as $opcion)
                                                    @php
                                                        $posicionUsuario = (int) ($ordenesUsuario[$opcion->id_ejercicio_opcion] ?? 0);
                                                        $posicionCorrecta = (int) $opcion->orden;
                                                        $filaCorrecta = $posicionUsuario === $posicionCorrecta;
                                                    @endphp

                                                    <tr class="{{ $filaCorrecta ? 'bg-emerald-50' : 'bg-red-50' }}">
                                                        <td class="px-3 py-2 border font-semibold">{{ $opcion->opcion }}</td>
                                                        <td class="px-3 py-2 border text-center">{{ $posicionUsuario ?: 'Sin responder' }}</td>
                                                        <td class="px-3 py-2 border text-center">{{ $posicionCorrecta }}</td>
                                                        <td class="px-3 py-2 border text-center">
                                                            @if($filaCorrecta)
                                                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-800">
                                                                    Correcta
                                                                </span>
                                                            @else
                                                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-black text-red-800">
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

                            {{-- RELACIONAR --}}
                            @elseif($tipo === 'relacionar')
                                @php
                                    $relacionesUsuario = collect($datos['relaciones'] ?? []);
                                    $izquierdas = $pregunta?->opciones?->where('lado', 'izquierda')->values() ?? collect();
                                    $derechas = $pregunta?->opciones?->where('lado', 'derecha')->keyBy('id_ejercicio_opcion') ?? collect();
                                @endphp

                                <div class="rounded-2xl border border-sky-100 bg-white/90 px-4 py-4 text-slate-900 shadow-sm">
                                    <strong>Relaciones</strong>

                                    <div class="mt-4 overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead class="bg-gradient-to-r from-sky-50 to-emerald-50 text-slate-700">
                                                <tr>
                                                    <th class="px-4 py-2 border text-left">Elemento</th>
                                                    <th class="px-4 py-2 border text-left">Tu elección</th>
                                                    <th class="px-4 py-2 border text-left">Correcta</th>
                                                    <th class="px-4 py-2 border text-center">Estado</th>
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

                                                        $filaCorrecta = $opcionUsuario && $opcionCorrecta
                                                            ? ((string) $opcionUsuario->clave_relacion === (string) $opcionCorrecta->clave_relacion)
                                                            : false;
                                                    @endphp

                                                    <tr>
                                                        <td class="px-4 py-2 border">{{ $izquierda->opcion }}</td>
                                                        <td class="px-4 py-2 border">{{ $opcionUsuario?->opcion ?? 'Sin asignar' }}</td>
                                                        <td class="px-4 py-2 border">{{ $opcionCorrecta?->opcion ?? 'No definida' }}</td>
                                                        <td class="px-4 py-2 border text-center">
                                                            @if($opcionUsuario)
                                                                @if($filaCorrecta)
                                                                    <span class="px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-semibold">
                                                                        Correcta
                                                                    </span>
                                                                @else
                                                                    <span class="px-2 py-1 rounded bg-red-100 text-red-800 text-xs font-semibold">
                                                                        Incorrecta
                                                                    </span>
                                                                @endif
                                                            @else
                                                                <span class="px-2 py-1 rounded bg-slate-100 text-slate-700 text-xs font-semibold">
                                                                    Pendiente
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            {{-- RESPUESTA CORTA / CASO PRACTICO --}}
                            @elseif($tipo === 'respuesta_corta' || $tipo === 'caso_practico')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="rounded-2xl border border-sky-100 bg-white/90 px-4 py-4 text-slate-900 shadow-sm">
                                        <strong>Tu respuesta</strong>
                                        <div class="mt-2 whitespace-pre-line">
                                            {{ $respuesta->respuesta_texto ?: 'Sin respuesta' }}
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border border-sky-100 bg-white/90 px-4 py-4 text-slate-900 shadow-sm">
                                        <strong>Revisión</strong>
                                        <div class="mt-2 whitespace-pre-line">
                                            Este tipo de respuesta puede requerir revisión manual.
                                        </div>
                                    </div>
                                </div>

                            {{-- COMPLETAR Y OTROS --}}
                                @else
                                    @php
                                        $respuestaTextoLimpia = $respuesta->respuesta_texto ?? '';

                                        $respuestaJsonLimpia = $respuesta->respuesta_json
                                            ? json_decode($respuesta->respuesta_json, true)
                                            : null;

                                        if (is_array($respuestaJsonLimpia)) {
                                            if (isset($respuestaJsonLimpia['respuesta_usuario'])) {
                                                $respuestaTextoLimpia = $respuestaJsonLimpia['respuesta_usuario'];
                                            } elseif (isset($respuestaJsonLimpia['opciones']) || isset($respuestaJsonLimpia['items']) || isset($respuestaJsonLimpia['posiciones'])) {
                                                $respuestaTextoLimpia = 'Respuesta registrada en las opciones de la pregunta.';
                                            }
                                        }

                                        if (blank($respuestaTextoLimpia)) {
                                            $respuestaTextoLimpia = 'Sin respuesta';
                                        }

                                        $respuestaCorrectaLimpia = $pregunta?->respuesta_correcta_texto ?? '';

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
                                            <strong>Respuesta esperada</strong>
                                            <div class="mt-2 whitespace-pre-line break-words">
                                                {{ $respuestaCorrectaLimpia }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                        </div>
                    @empty
                        <div class="rounded-2xl border border-slate-200 bg-white/90 px-4 py-4 text-slate-700 shadow-sm">
                            No hay respuestas registradas para este intento.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $intento->ejercicio->id_capacitacion_modulo]) }}#contenido-ejercicio-{{ $intento->id_ejercicio }}"
                    target="{{ request()->boolean('integrado_modulo') ? '_top' : '_self' }}"
                    class="inline-flex items-center rounded-full bg-slate-600 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-slate-200 hover:-translate-y-0.5 hover:shadow-xl transition">
                        Volver al módulo
                </a>

                <a href="{{ route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $intento->ejercicio->id_capacitacion_modulo]) }}?ejercicio_integrado={{ $intento->id_ejercicio }}#contenido-ejercicio-{{ $intento->id_ejercicio }}"
                    target="{{ request()->boolean('integrado_modulo') ? '_top' : '_self' }}"
                    class="inline-flex items-center rounded-full bg-slate-900 px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-slate-300 hover:-translate-y-0.5 hover:shadow-xl transition">
                        Volver al ejercicio
                </a>
            </div>

        </div>
    </div>
</x-app-layout>