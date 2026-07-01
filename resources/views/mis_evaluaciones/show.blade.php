<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Presentar evaluación
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-emerald-50 py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($errors->any())
                <div class="rounded border border-red-300 bg-red-100 px-4 py-3 text-red-800">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-[2rem] border border-sky-100 bg-white/90 shadow-xl shadow-sky-100/50 backdrop-blur overflow-hidden">
                <div class="p-7 text-slate-900">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-sky-500 mb-2">
                        Actividad en curso
                    </p>

                    <h3 class="text-3xl font-black tracking-tight text-slate-900 mb-2">
                        {{ $evaluacion->titulo }}
                    </h3>

                    @if($evaluacion->descripcion)
                        <p class="mb-4">{{ $evaluacion->descripcion }}</p>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                        <div class="rounded-3xl border border-sky-100 bg-gradient-to-br from-white to-sky-50 px-5 py-4 text-slate-800 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <strong>Porcentaje para aprobar</strong>
                            <div class="mt-2 text-lg font-bold">{{ $evaluacion->porcentaje_aprobacion }}%</div>
                        </div>

                        <div class="rounded-3xl border border-sky-100 bg-gradient-to-br from-white to-sky-50 px-5 py-4 text-slate-800 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <strong>Intentos realizados</strong>
                            <div class="mt-2 text-lg font-bold">{{ $intentosRealizados }}</div>
                        </div>

                        <div class="rounded-3xl border border-sky-100 bg-gradient-to-br from-white to-sky-50 px-5 py-4 text-slate-800 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <strong>Intentos restantes</strong>
                            <div class="mt-2 text-lg font-bold">
                                {{ is_null($intentosRestantes) ? 'Ilimitados' : $intentosRestantes }}
                            </div>
                        </div>

                        <div class="rounded-3xl border border-sky-100 bg-gradient-to-br from-white to-sky-50 px-5 py-4 text-slate-800 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <strong>Último resultado</strong>
                            <div class="mt-2 text-lg font-bold">
                                @if($ultimoIntento)
                                    {{ (int) $ultimoIntento->aprobado === 1 ? 'Aprobado' : 'Reprobado' }}
                                @else
                                    Sin intentos
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <p><strong>Tiempo límite:</strong> {{ $evaluacion->tiempo_limite_minutos ?? '-' }}</p>
                        <p><strong>Intentos máximos:</strong> {{ $intentosMaximos ?? '-' }}</p>

                        @if($aprobadoEvaluacion)
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-sm">
                                Ya aprobaste esta evaluación. No necesitas volver a presentarla.
                            </div>
                        @elseif($maximoIntentosAlcanzado)
                            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 shadow-sm">
                                Ya alcanzaste el máximo de intentos permitidos para esta evaluación. Esta evaluación quedó cerrada y el módulo debe reflejarse como reprobado.
                            </div>
                        @else
                            <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sky-800 shadow-sm">
                                Aún puedes presentar esta evaluación.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($intentos->count() > 0)
                <div class="rounded-[2rem] border border-sky-100 bg-white/90 shadow-xl shadow-sky-100/50 backdrop-blur overflow-hidden">
                    <div class="p-7 text-slate-900">
                        <div class="mb-5 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.22em] text-sky-500">
                                    Seguimiento
                                </p>
                                <h4 class="text-xl font-black text-slate-900">
                                    Historial de intentos
                                </h4>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-sky-100 bg-white shadow-sm">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gradient-to-r from-sky-50 to-emerald-50 text-slate-700">
                                    <tr>
                                        <th class="px-4 py-3 text-slate-700">Intento</th>
                                        <th class="px-4 py-3 text-slate-700">Nota</th>
                                        <th class="px-4 py-3 text-slate-700">Resultado</th>
                                        <th class="px-4 py-3 text-slate-700">Fecha fin</th>
                                        <th class="px-4 py-3 text-slate-700">Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($intentos as $intento)
                                        <tr class="text-center border-b border-slate-100 hover:bg-sky-50/60 transition">
                                            <td class="px-4 py-3 border-b border-sky-100 text-xs font-black uppercase tracking-[0.16em]">{{ $intento->numero_intento }}</td>
                                            <td class="px-4 py-3 border-b border-sky-100 text-xs font-black uppercase tracking-[0.16em]">{{ number_format((float) $intento->nota, 2) }}%</td>
                                            <td class="px-4 py-3 border-b border-sky-100 text-xs font-black uppercase tracking-[0.16em]">
                                                @if((int) $intento->aprobado === 1)
                                                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-800">Aprobado</span>
                                                @else
                                                    <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-black text-red-700">Reprobado</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 border-b border-sky-100 text-xs font-black uppercase tracking-[0.16em]">{{ $intento->fecha_fin ?? '-' }}</td>
                                            <td class="px-4 py-3 border-b border-sky-100 text-xs font-black uppercase tracking-[0.16em]">
                                                @if($puedeVerRevisionUsuario ?? false)
                                                    <a href="{{ route('mis_evaluaciones.resultado', [$miCapacitacion->id_empleado_capacitacion, $intento->id_evaluacion_intento]) }}"
                                                    class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-xs font-black text-white shadow hover:-translate-y-0.5 hover:shadow-lg transition">
                                                        Ver detalle
                                                    </a>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-600 border border-slate-200">
                                                        Disponible al aprobar o agotar intentos
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif


        @if(!$aprobadoEvaluacion && !$maximoIntentosAlcanzado)
            <form id="formEvaluacion"
                method="POST"
                target="{{ request()->boolean('integrado_modulo') ? '_top' : '_self' }}"
                action="{{ route('mis_evaluaciones.submit', [$miCapacitacion->id_empleado_capacitacion, $evaluacion->id_evaluacion]) }}"
                onsubmit="return confirm('¿Estás segura/o de finalizar y enviar esta evaluación? Las preguntas en blanco se guardarán con puntaje 0.');">
                    @csrf

                    @if(!is_null($segundosRestantes))
                        <div class="mb-6 rounded border border-orange-300 bg-orange-100 px-4 py-3 text-orange-800">
                            <strong>Tiempo restante:</strong>
                            <span id="timerEvaluacion" class="font-bold"></span>
                            <p class="text-sm mt-1">
                                Cuando el tiempo llegue a cero, la evaluación se enviará automáticamente.
                            </p>
                        </div>
                    @endif

                    @foreach($evaluacion->preguntas as $index => $pregunta)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <h4 class="text-lg font-semibold mb-3">
                                    {{ $index + 1 }}. {{ $pregunta->pregunta }}
                                </h4>

                                <p class="text-sm mb-4"><strong>Puntaje:</strong> {{ $pregunta->puntaje }}</p>

                                @php
                                    $configImagenPreguntaEvaluacion = json_decode($pregunta->configuracion_json ?? '{}', true);
                                @endphp

                                @if(in_array($pregunta->tipo_pregunta, ['opcion_unica', 'checklist_guiado'], true) && !empty($configImagenPreguntaEvaluacion['imagen_pregunta']))
                                    <div class="mb-5 overflow-x-auto rounded-xl border border-slate-300 bg-white p-3">
                                        <img src="{{ asset('storage/' . $configImagenPreguntaEvaluacion['imagen_pregunta']) }}"
                                            alt="Imagen de la pregunta"
                                            class="max-w-full rounded-lg mx-auto">
                                    </div>
                                @endif

                                @if($pregunta->tipo_pregunta === 'opcion_unica' || $pregunta->tipo_pregunta === 'verdadero_falso')
                                    <div class="space-y-3">
                                        @foreach($pregunta->opciones as $opcion)
                                            <label class="flex items-center gap-3 rounded border border-gray-300 bg-gray-50 dark:bg-gray-900 px-4 py-3 cursor-pointer">
                                                <input
                                                    type="radio"
                                                    name="respuestas[{{ $pregunta->id_evaluacion_pregunta }}]"
                                                    value="{{ $opcion->id_evaluacion_opcion }}"
                                                    {{ old('respuestas.' . $pregunta->id_evaluacion_pregunta) == $opcion->id_evaluacion_opcion ? 'checked' : '' }}
                                                >
                                                <span>{{ $opcion->opcion }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                @elseif(in_array($pregunta->tipo_pregunta, ['checklist_guiado', 'opcion_multiple', 'multiple'], true))
                                    <div class="space-y-3">
                                        @foreach($pregunta->opciones as $opcion)
                                            <label class="flex items-center gap-3 rounded border border-gray-300 bg-gray-50 dark:bg-gray-900 px-4 py-3 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    name="respuestas[{{ $pregunta->id_evaluacion_pregunta }}][]"
                                                    value="{{ $opcion->id_evaluacion_opcion }}"
                                                    class="rounded"
                                                    {{ in_array($opcion->id_evaluacion_opcion, old('respuestas.' . $pregunta->id_evaluacion_pregunta, [])) ? 'checked' : '' }}
                                                >
                                                <span>{{ $opcion->opcion }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                @elseif($pregunta->tipo_pregunta === 'seleccionar_posicion_imagen')
                                    @php
                                        $configPosicionEvaluacion = json_decode($pregunta->configuracion_json ?? '{}', true);

                                        $cantidadConfig = (int) ($configPosicionEvaluacion['cantidad_posiciones'] ?? 0);
                                        $cantidadOpciones = (int) $pregunta->opciones->count();
                                        $maximoOrdenOpciones = (int) ($pregunta->opciones->max('orden') ?? 0);

                                        $cantidadPosiciones = max($cantidadConfig, $cantidadOpciones, $maximoOrdenOpciones, 1);

                                        $opcionesPosicion = $pregunta->opciones->shuffle()->values();
                                    @endphp

                                    <div class="rounded border border-sky-300 bg-sky-50 p-4">
                                        @if(!empty($configPosicionEvaluacion['imagen']))
                                            <div class="mb-4 text-center">
                                                <img src="{{ asset('storage/' . $configPosicionEvaluacion['imagen']) }}"
                                                    class="max-h-96 rounded border mx-auto">
                                            </div>
                                        @endif

                                        @if(!empty($configPosicionEvaluacion['texto_apoyo']))
                                            <div class="mb-4 rounded border border-sky-200 bg-white px-4 py-3 text-sky-900">
                                                {{ $configPosicionEvaluacion['texto_apoyo'] }}
                                            </div>
                                        @endif

                                        <div class="overflow-x-auto rounded border border-slate-300 bg-white">
                                            <table class="min-w-full text-sm text-black">
                                                <thead class="bg-slate-100">
                                                    <tr>
                                                        <th class="px-3 py-2 border text-left">Campo a ordenar</th>
                                                        @for($numero = 1; $numero <= $cantidadPosiciones; $numero++)
                                                            <th class="px-3 py-2 border text-center">{{ $numero }}</th>
                                                        @endfor
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @foreach($opcionesPosicion as $opcion)
                                                        <tr>
                                                            <td class="px-3 py-2 border font-semibold">
                                                                {{ $opcion->opcion }}
                                                            </td>

                                                            @for($numero = 1; $numero <= $cantidadPosiciones; $numero++)
                                                                <td class="px-3 py-2 border text-center">
                                                                    <input type="radio"
                                                                        name="respuestas[{{ $pregunta->id_evaluacion_pregunta }}][{{ $opcion->id_evaluacion_opcion }}]"
                                                                        value="{{ $numero }}"
                                                                        {{ old('respuestas.' . $pregunta->id_evaluacion_pregunta . '.' . $opcion->id_evaluacion_opcion) == $numero ? 'checked' : '' }}>
                                                                </td>
                                                            @endfor
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                @elseif($pregunta->tipo_pregunta === 'completar')
                                    @php
                                        $partesCompletar = explode('[[blank]]', $pregunta->pregunta);
                                        $textoAntesCompletar = $partesCompletar[0] ?? '';
                                        $textoDespuesCompletar = $partesCompletar[1] ?? '';
                                    @endphp

                                    <div class="rounded border border-gray-300 bg-gray-50 dark:bg-gray-900 px-4 py-4">
                                        <div class="flex flex-col md:flex-row md:items-center gap-3">
                                            <span>{{ $textoAntesCompletar }}</span>

                                            <input
                                                type="text"
                                                name="respuestas[{{ $pregunta->id_evaluacion_pregunta }}]"
                                                value="{{ old('respuestas.' . $pregunta->id_evaluacion_pregunta) }}"
                                                class="border rounded px-3 py-2 text-black min-w-[220px]"
                                                placeholder="Escribe la respuesta"
                                            >

                                            <span>{{ $textoDespuesCompletar }}</span>
                                        </div>
                                    </div>

                                @elseif($pregunta->tipo_pregunta === 'respuesta_corta')
                                    @php
                                        $configRespuestaBreve = json_decode($pregunta->configuracion_json ?? '{}', true);
                                    @endphp

                                    <div class="rounded border border-gray-300 bg-gray-50 dark:bg-gray-900 px-4 py-4">
                                        <textarea
                                            name="respuestas[{{ $pregunta->id_evaluacion_pregunta }}]"
                                            rows="3"
                                            class="w-full border rounded px-3 py-2 text-black"
                                            placeholder="{{ $configRespuestaBreve['placeholder'] ?? 'Escribe tu respuesta breve...' }}"
                                        >{{ old('respuestas.' . $pregunta->id_evaluacion_pregunta) }}</textarea>

                                        <p class="text-xs text-gray-500 mt-2">
                                            Esta respuesta puede requerir revisión del administrador.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="flex gap-3">
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded">
                            Finalizar evaluación
                        </button>

                        <a href="{{ route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $evaluacion->id_capacitacion_modulo]) }}#examen-general-modulo"
                            target="{{ request()->boolean('integrado_modulo') ? '_top' : '_self' }}"
                            class="px-4 py-2 bg-gray-600 text-white rounded">
                                Cancelar
                        </a>
                    </div>
                </form>
            @else
                <div class="flex gap-3">
                    <a href="{{ route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $evaluacion->id_capacitacion_modulo]) }}#examen-general-modulo"
                        target="{{ request()->boolean('integrado_modulo') ? '_top' : '_self' }}"
                        class="px-4 py-2 bg-gray-600 text-white rounded">
                            Volver al módulo
                    </a>
                </div>
            @endif

        </div>
    </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const segundosIniciales = @json($segundosRestantes);

                    if (segundosIniciales === null) {
                        return;
                    }

                    let segundosRestantes = parseInt(segundosIniciales, 10);
                    let intervalo = null;

                    const timer = document.getElementById('timerEvaluacion');
                    const form = document.getElementById('formEvaluacion');

                    function pintarTiempo() {
                        const minutos = Math.floor(segundosRestantes / 60);
                        const segundos = segundosRestantes % 60;

                        if (timer) {
                            timer.textContent = String(minutos).padStart(2, '0') + ':' + String(segundos).padStart(2, '0');
                        }

                        if (segundosRestantes <= 0) {
                            if (intervalo) {
                                clearInterval(intervalo);
                            }

                            if (form) {
                                form.submit();
                            }

                            return;
                        }

                        segundosRestantes--;
                    }

                    pintarTiempo();
                    intervalo = setInterval(pintarTiempo, 1000);
                });
            </script>

</x-app-layout>