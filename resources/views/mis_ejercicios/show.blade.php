<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Resolver ejercicio
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
                        {{ $ejercicio->titulo }}
                    </h3>

                    @if($ejercicio->descripcion)
                        <p class="mb-3">{{ $ejercicio->descripcion }}</p>
                    @endif

                    @if($ejercicio->instrucciones)
                        <div class="rounded border border-blue-300 bg-blue-100 px-4 py-3 text-blue-800 mb-4">
                            <strong>Instrucciones:</strong>
                            <div class="mt-2 whitespace-pre-line">{{ $ejercicio->instrucciones }}</div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                            <strong>Obligatorio</strong>
                            <div class="mt-2 text-lg font-bold">
                                {{ (int) $ejercicio->obligatorio === 1 ? 'Sí' : 'No' }}
                            </div>
                        </div>

                        <div class="rounded-3xl border border-sky-100 bg-gradient-to-br from-white to-sky-50 px-5 py-4 text-slate-800 shadow-sm hover:-translate-y-1 hover:shadow-xl transition">
                            <strong>Preguntas</strong>
                            <div class="mt-2 text-lg font-bold">{{ $ejercicio->preguntas->count() }}</div>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        @if($tienePendienteRevision)
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 shadow-sm">
                                Ya tienes un intento pendiente de revisión manual para este ejercicio. No puedes abrir uno nuevo todavía.
                            </div>
                        @elseif($maximoIntentosAlcanzado)
                            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 shadow-sm">
                                Ya alcanzaste el máximo de intentos permitidos para este ejercicio.
                            </div>
                        @else
                            <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sky-800 shadow-sm">
                                Puedes resolver este ejercicio.
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
                                        <th class="px-4 py-3 border-b border-sky-100 text-xs font-black uppercase tracking-[0.16em]">Intento</th>
                                        <th class="px-4 py-3 border-b border-sky-100 text-xs font-black uppercase tracking-[0.16em]">Estado</th>
                                        <th class="px-4 py-3 border-b border-sky-100 text-xs font-black uppercase tracking-[0.16em]">Resultado</th>
                                        <th class="px-4 py-3 border-b border-sky-100 text-xs font-black uppercase tracking-[0.16em]">Fecha fin</th>
                                        <th class="px-4 py-3 border-b border-sky-100 text-xs font-black uppercase tracking-[0.16em]">Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($intentos as $intento)
                                        <tr class="text-center border-b border-slate-100 hover:bg-sky-50/60 transition">
                                            <td class="px-4 py-3 text-slate-700">#{{ $intento->numero_intento }}</td>
                                            <td class="px-4 py-3 text-slate-700">{{ ucfirst(str_replace('_', ' ', $intento->estado)) }}</td>
                                            <td class="px-4 py-3 text-slate-700">
                                                @if(is_null($intento->porcentaje_obtenido))
                                                    -
                                                @else
                                                    {{ number_format((float) $intento->porcentaje_obtenido, 2) }}%
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-slate-700">{{ $intento->fecha_fin ?? '-' }}</td>
                                            <td class="px-4 py-3 text-slate-700">
                                                @if($puedeVerRevisionUsuario ?? false)
                                                    <a href="{{ route('mis_ejercicios.resultado', [$miCapacitacion->id_empleado_capacitacion, $intento->id_ejercicio_intento]) }}"
                                                    class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-xs font-black text-white shadow hover:-translate-y-0.5 hover:shadow-lg transition">
                                                        Ver detalle
                                                    </a>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-600 border border-slate-200">
                                                        Disponible al agotar intentos
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


        @if(!$tienePendienteRevision && !$maximoIntentosAlcanzado)
            <form id="formEjercicio"
                method="POST"
                target="{{ request()->boolean('integrado_modulo') ? '_top' : '_self' }}"
                action="{{ route('mis_ejercicios.submit', [$miCapacitacion->id_empleado_capacitacion, $ejercicio->id_ejercicio]) }}"
                onsubmit="return confirm('¿Estás segura/o de finalizar y enviar este ejercicio? Las respuestas en blanco se guardarán con puntaje 0.');">
                    @csrf

                    @if(!is_null($segundosRestantes))
                        <div class="mb-6 rounded border border-orange-300 bg-orange-100 px-4 py-3 text-orange-800">
                            <strong>Tiempo restante:</strong>
                            <span id="timerEjercicio" class="font-bold"></span>
                            <p class="text-sm mt-1">
                                Cuando el tiempo llegue a cero, el ejercicio se enviará automáticamente.
                            </p>
                        </div>
                    @endif

                    @foreach($ejercicio->preguntas as $index => $pregunta)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <h4 class="text-lg font-semibold mb-3">
                                    {{ $index + 1 }}. {{ $pregunta->enunciado }}
                                </h4>

                                <p class="text-sm mb-4">
                                    <strong>Tipo:</strong> {{ $pregunta->tipo_pregunta }}
                                    · <strong>Puntaje:</strong> {{ $pregunta->puntaje }}
                                </p>

                                @php
                                    $configImagenPreguntaEjercicio = json_decode($pregunta->configuracion_json ?? '{}', true);
                                @endphp

                                @if(in_array($pregunta->tipo_pregunta, ['opcion_unica', 'checklist_guiado'], true) && !empty($configImagenPreguntaEjercicio['imagen_pregunta']))
                                    <div class="mb-5 overflow-x-auto rounded-xl border border-slate-300 bg-white p-3">
                                        <img src="{{ asset('storage/' . $configImagenPreguntaEjercicio['imagen_pregunta']) }}"
                                            alt="Imagen de la pregunta"
                                            class="max-w-full rounded-lg mx-auto">
                                    </div>
                                @endif

                                @if($pregunta->tipo_pregunta === 'opcion_unica' || $pregunta->tipo_pregunta === 'verdadero_falso')
                                    @foreach($pregunta->opciones as $opcion)
                                        <label class="flex items-center gap-2 mb-2">
                                            <input
                                                type="radio"
                                                name="respuestas[{{ $pregunta->id_ejercicio_pregunta }}][opcion]"
                                                value="{{ $opcion->id_ejercicio_opcion }}"
                                                {{ old('respuestas.' . $pregunta->id_ejercicio_pregunta . '.opcion') == $opcion->id_ejercicio_opcion ? 'checked' : '' }}
                                            >
                                            <span>{{ $opcion->opcion }}</span>
                                        </label>
                                    @endforeach

                                @elseif($pregunta->tipo_pregunta === 'seleccionar_posicion_imagen')
                                    @php
                                        $configPosicionUsuario = json_decode($pregunta->configuracion_json ?? '{}', true);

                                        $cantidadConfig = (int) ($configPosicionUsuario['cantidad_posiciones'] ?? 0);
                                        $cantidadOpciones = (int) $pregunta->opciones->count();
                                        $maximoOrdenOpciones = (int) ($pregunta->opciones->max('orden') ?? 0);

                                        $cantidadPosiciones = max($cantidadConfig, $cantidadOpciones, $maximoOrdenOpciones, 1);

                                        $opcionesPosicion = $pregunta->opciones->shuffle()->values();
                                    @endphp

                                    <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 space-y-4">
                                        @if(!empty($configPosicionUsuario['imagen']))
                                            <div class="text-center">
                                                <img src="{{ asset('storage/' . $configPosicionUsuario['imagen']) }}"
                                                    class="max-h-[420px] rounded-xl border bg-white p-2 mx-auto">
                                            </div>
                                        @endif

                                        @if(!empty($configPosicionUsuario['texto_apoyo']))
                                            <div class="rounded-xl border border-sky-200 bg-white px-4 py-3 text-sm text-sky-900">
                                                {{ $configPosicionUsuario['texto_apoyo'] }}
                                            </div>
                                        @endif

                                        <div class="overflow-x-auto rounded-xl border border-slate-300 bg-white">
                                            <table class="min-w-full text-sm text-black">
                                                <thead class="bg-slate-100">
                                                    <tr>
                                                        <th class="sticky left-0 z-10 bg-slate-100 px-3 py-2 border text-left min-w-[180px]">
                                                            Campo a ordenar
                                                        </th>

                                                        @for($numero = 1; $numero <= $cantidadPosiciones; $numero++)
                                                            <th class="px-4 py-2 border text-center">
                                                                {{ $numero }}
                                                            </th>
                                                        @endfor
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @foreach($opcionesPosicion as $opcion)
                                                        <tr>
                                                            <td class="sticky left-0 z-10 bg-white px-3 py-2 border font-semibold min-w-[180px]">
                                                                {{ $opcion->opcion }}
                                                            </td>

                                                            @for($numero = 1; $numero <= $cantidadPosiciones; $numero++)
                                                                <td class="px-4 py-2 border text-center">
                                                                    <input type="radio"
                                                                        name="respuestas[{{ $pregunta->id_ejercicio_pregunta }}][posiciones][{{ $opcion->id_ejercicio_opcion }}]"
                                                                        value="{{ $numero }}"
                                                                        {{ old('respuestas.' . $pregunta->id_ejercicio_pregunta . '.posiciones.' . $opcion->id_ejercicio_opcion) == $numero ? 'checked' : '' }}>
                                                                </td>
                                                            @endfor
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <p class="text-xs text-slate-500">
                                            Deslizá la tabla hacia los lados si no ves todas las posiciones.
                                        </p>
                                    </div>

                                @elseif($pregunta->tipo_pregunta === 'actividad_visual_identificacion')
                                    @php
                                        $configVisualUsuario = json_decode($pregunta->configuracion_json ?? '{}', true);
                                        $valorVisualOld = old('respuestas.' . $pregunta->id_ejercicio_pregunta . '.texto', '');
                                    @endphp

                                    <div class="rounded border border-amber-300 bg-amber-50 p-4">
                                        @if(!empty($configVisualUsuario['imagen']))
                                            <div class="mb-4 text-center">
                                                <img src="{{ asset('storage/' . $configVisualUsuario['imagen']) }}"
                                                    class="max-h-80 rounded border mx-auto">
                                            </div>
                                        @endif

                                        @if(!empty($configVisualUsuario['texto_apoyo']))
                                            <div class="mb-4 rounded border border-amber-200 bg-white px-4 py-3 text-amber-900">
                                                {{ $configVisualUsuario['texto_apoyo'] }}
                                            </div>
                                        @endif

                                        <div class="mb-3 rounded border border-amber-200 bg-white px-4 py-3 text-amber-900">
                                            <strong>Respuesta del usuario:</strong> observa la imagen y escribe tu identificación, análisis o explicación según las instrucciones del ejercicio.
                                        </div>

                                        <textarea
                                            name="respuestas[{{ $pregunta->id_ejercicio_pregunta }}][texto]"
                                            rows="6"
                                            class="w-full rounded border-gray-300 text-black actividad-visual-textarea"
                                            placeholder="Escribe aquí tu respuesta sobre la imagen..."
                                        >{{ $valorVisualOld }}</textarea>
                                    </div>

                                @elseif($pregunta->tipo_pregunta === 'opcion_multiple')
                                    @php
                                        $seleccionadasOld = old('respuestas.' . $pregunta->id_ejercicio_pregunta . '.opciones', []);
                                    @endphp

                                    <div class="space-y-3">
                                        @foreach($pregunta->opciones->sortBy('orden') as $opcion)
                                            <label class="flex items-start gap-3 rounded border border-slate-300 bg-white px-4 py-3 shadow-sm cursor-pointer">
                                                <input type="checkbox"
                                                    name="respuestas[{{ $pregunta->id_ejercicio_pregunta }}][opciones][]"
                                                    value="{{ $opcion->id_ejercicio_opcion }}"
                                                    class="mt-1 h-5 w-5 rounded border-gray-300 text-blue-600"
                                                    {{ in_array($opcion->id_ejercicio_opcion, $seleccionadasOld) ? 'checked' : '' }}>

                                                <span class="text-black">{{ $opcion->opcion }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                               @elseif($pregunta->tipo_pregunta === 'ordenar')
                                    @php
                                        $ordenesOld = old('respuestas.' . $pregunta->id_ejercicio_pregunta . '.ordenes');

                                        if (is_array($ordenesOld) && count($ordenesOld) > 0) {
                                            $opcionesOrdenar = $pregunta->opciones
                                                ->sortBy(function ($opcion) use ($ordenesOld) {
                                                    return (int) ($ordenesOld[$opcion->id_ejercicio_opcion] ?? 9999);
                                                })
                                                ->values();
                                        } else {
                                            $opcionesOrdenar = $pregunta->opciones
                                                ->shuffle()
                                                ->values();
                                        }
                                    @endphp

                                    <div class="rounded border border-slate-300 bg-slate-50 p-4 ordenar-wrapper">
                                        <p class="text-sm text-slate-700 mb-4">
                                            Arrastra los elementos para dejarlos en el orden correcto o de prioridad.
                                        </p>

                                        <div class="ordenar-container space-y-3" data-question-id="{{ $pregunta->id_ejercicio_pregunta }}">
                                            @foreach($opcionesOrdenar as $opcion)
                                                <div class="ordenar-item flex items-center gap-3 rounded-lg border border-slate-300 bg-white p-4 shadow-sm"
                                                    data-opcion-id="{{ $opcion->id_ejercicio_opcion }}"
                                                    draggable="true">
                                                    <input
                                                        type="hidden"
                                                        name="respuestas[{{ $pregunta->id_ejercicio_pregunta }}][ordenes][{{ $opcion->id_ejercicio_opcion }}]"
                                                        class="ordenar-input"
                                                        value=""
                                                    >

                                                    <span class="ordenar-posicion inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-white font-bold">
                                                        {{ $loop->iteration }}
                                                    </span>

                                                    <span class="ordenar-handle cursor-move text-2xl leading-none text-slate-500 select-none">⋮⋮</span>

                                                    <div class="flex-1">
                                                        <p class="font-medium text-slate-900">{{ $opcion->opcion }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                               @elseif($pregunta->tipo_pregunta === 'relacionar')
                                    @php
                                        $izquierdas = $pregunta->opcionesIzquierdaTemp ?? $pregunta->opciones->where('lado', 'izquierda')->values();
                                        $derechas = $pregunta->opcionesDerechaTemp ?? $pregunta->opciones->where('lado', 'derecha')->values();
                                    @endphp

                                    <div class="rounded border border-slate-300 bg-slate-50 p-5 relacionar-board">
                                        <p class="text-sm text-slate-700 mb-5 relacionar-ayuda">
                                            Paso 1: haz clic en un elemento de la izquierda. Paso 2: haz clic en su pareja correcta a la derecha.
                                        </p>

                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                                            <div>
                                                <h5 class="font-semibold text-slate-800 mb-4">Elementos a relacionar</h5>

                                                <div class="space-y-4">
                                                    @foreach($izquierdas as $izquierda)
                                                        @php
                                                            $valorRelacionado = old(
                                                                'respuestas.' . $pregunta->id_ejercicio_pregunta . '.relaciones.' . $izquierda->id_ejercicio_opcion
                                                            );

                                                            $textoRelacionado = optional(
                                                                $derechas->firstWhere('id_ejercicio_opcion', (int) $valorRelacionado)
                                                            )->opcion;
                                                        @endphp

                                                        <div class="relacionar-left-item rounded-xl border border-slate-300 bg-white p-4 shadow-sm cursor-pointer h-[220px] flex flex-col justify-between"
                                                            data-left-id="{{ $izquierda->id_ejercicio_opcion }}">
                                                            <input
                                                                type="hidden"
                                                                name="respuestas[{{ $pregunta->id_ejercicio_pregunta }}][relaciones][{{ $izquierda->id_ejercicio_opcion }}]"
                                                                class="relacionar-input"
                                                                value="{{ $valorRelacionado }}"
                                                            >

                                                            <div class="flex items-start justify-between gap-3">
                                                                <div class="flex-1">
                                                                    <p class="font-semibold text-slate-900 text-lg">{{ $izquierda->opcion }}</p>
                                                                    <p class="text-sm text-slate-500 mt-1">
                                                                        Selecciona este elemento y luego elige su coincidencia.
                                                                    </p>
                                                                </div>

                                                                <span class="relacionar-estado inline-flex rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 whitespace-nowrap">
                                                                    {{ $textoRelacionado ? 'Asignado' : 'Sin asignar' }}
                                                                </span>
                                                            </div>

                                                            <div class="relacionar-seleccion rounded-lg border border-dashed border-slate-300 bg-slate-50 px-3 py-3 text-sm text-slate-700 min-h-[64px] flex items-center">
                                                                {{ $textoRelacionado ?: 'Todavía no has elegido una opción.' }}
                                                            </div>

                                                            <div class="pt-2">
                                                                <button type="button"
                                                                        class="relacionar-limpiar px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                                                    Limpiar relación
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div>
                                                <h5 class="font-semibold text-slate-800 mb-4">Opciones de respuesta</h5>

                                                <div class="space-y-4">
                                                    @foreach($derechas as $derecha)
                                                        <button type="button"
                                                                class="relacionar-right-option w-full text-left rounded-xl border border-slate-300 bg-white p-4 shadow-sm hover:bg-emerald-50 h-[220px] flex flex-col justify-between"
                                                                data-right-id="{{ $derecha->id_ejercicio_opcion }}"
                                                                data-right-label="{{ e($derecha->opcion) }}">
                                                            <div class="flex items-start justify-between gap-3">
                                                                <div class="flex-1">
                                                                    <p class="font-semibold text-slate-900 text-lg">{{ $derecha->opcion }}</p>
                                                                    <p class="text-sm text-slate-500 mt-1">
                                                                        Haz clic para asignarla a un elemento de la izquierda.
                                                                    </p>
                                                                </div>

                                                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 whitespace-nowrap">
                                                                    Disponible
                                                                </span>
                                                            </div>

                                                            <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-3 py-3 text-sm text-slate-500 min-h-[64px] flex items-center">
                                                                Selecciona esta opción para relacionarla.
                                                            </div>

                                                            <div class="pt-2">
                                                                <span class="inline-flex px-4 py-2 bg-slate-800 text-white rounded-lg text-sm">
                                                                    Seleccionar opción
                                                                </span>
                                                            </div>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                @elseif($pregunta->tipo_pregunta === 'checklist_guiado')
                                    @php
                                        $itemsMarcadosOld = old('respuestas.' . $pregunta->id_ejercicio_pregunta . '.items', []);
                                    @endphp

                                    <div class="rounded border border-emerald-300 bg-emerald-50 p-4">
                                        <p class="text-sm text-emerald-800 mb-4">
                                            Marca los ítems del checklist que correspondan a esta actividad.
                                        </p>

                                        <div class="space-y-3">
                                            @foreach($pregunta->opciones->sortBy('orden') as $opcion)
                                                <label class="flex items-start gap-3 rounded border border-slate-300 bg-white px-4 py-3 shadow-sm cursor-pointer">
                                                    <input type="checkbox"
                                                        name="respuestas[{{ $pregunta->id_ejercicio_pregunta }}][items][]"
                                                        value="{{ $opcion->id_ejercicio_opcion }}"
                                                        class="mt-1 h-5 w-5 rounded border-gray-300 text-emerald-600"
                                                        {{ in_array($opcion->id_ejercicio_opcion, $itemsMarcadosOld) ? 'checked' : '' }}>

                                                    <span class="text-black">{{ $opcion->opcion }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                @elseif($pregunta->tipo_pregunta === 'completar')
                                    @php
                                        $partesCompletar = explode('[[blank]]', $pregunta->enunciado);
                                        $textoAntes = $partesCompletar[0] ?? '';
                                        $textoDespues = $partesCompletar[1] ?? '';
                                        $valorOld = old('respuestas.' . $pregunta->id_ejercicio_pregunta . '.texto', '');
                                    @endphp

                                    <div class="rounded border border-slate-300 bg-white px-4 py-4 text-black">
                                        <label class="block text-sm font-medium mb-3">Completa la frase:</label>

                                        <div class="flex flex-wrap items-center gap-2 text-base leading-7">
                                            <span>{{ $textoAntes }}</span>

                                            <input type="text"
                                                name="respuestas[{{ $pregunta->id_ejercicio_pregunta }}][texto]"
                                                value="{{ $valorOld }}"
                                                class="min-w-[180px] border rounded px-3 py-2 text-black">

                                            <span>{{ $textoDespues }}</span>
                                        </div>
                                    </div>

                                @elseif($pregunta->tipo_pregunta === 'respuesta_corta')
                                    @php
                                        $configRespuestaBreveUsuario = json_decode($pregunta->configuracion_json ?? '{}', true);
                                        $placeholderBreve = $configRespuestaBreveUsuario['placeholder'] ?? 'Escribe tu respuesta breve aquí...';
                                        $minBreve = $configRespuestaBreveUsuario['min_caracteres'] ?? null;
                                        $maxBreve = $configRespuestaBreveUsuario['max_caracteres'] ?? null;
                                        $valorBreveOld = old('respuestas.' . $pregunta->id_ejercicio_pregunta . '.texto', '');
                                    @endphp

                                    <div class="rounded border border-blue-300 bg-blue-50 p-4">
                                        <input
                                            type="text"
                                            name="respuestas[{{ $pregunta->id_ejercicio_pregunta }}][texto]"
                                            value="{{ $valorBreveOld }}"
                                            class="w-full rounded border-gray-300 text-black respuesta-breve-input"
                                            placeholder="{{ $placeholderBreve }}"
                                            @if(!is_null($maxBreve)) maxlength="{{ $maxBreve }}" @endif
                                        >

                                        <div class="mt-2 text-sm text-slate-600 flex flex-wrap gap-4">
                                            @if(!is_null($minBreve))
                                                <span>Mínimo: {{ $minBreve }} caracteres</span>
                                            @endif

                                            @if(!is_null($maxBreve))
                                                <span>Máximo: {{ $maxBreve }} caracteres</span>
                                            @endif

                                            <span class="respuesta-breve-contador">Caracteres: {{ mb_strlen($valorBreveOld) }}</span>
                                        </div>
                                    </div>

                                @elseif($pregunta->tipo_pregunta === 'caso_practico')
                                    @php
                                        $configCasoUsuario = json_decode($pregunta->configuracion_json ?? '{}', true);
                                        $placeholderCaso = $configCasoUsuario['placeholder'] ?? 'Describe tu análisis del caso...';
                                        $minCaso = $configCasoUsuario['min_caracteres'] ?? null;
                                        $maxCaso = $configCasoUsuario['max_caracteres'] ?? null;
                                        $valorCasoOld = old('respuestas.' . $pregunta->id_ejercicio_pregunta . '.texto', '');
                                    @endphp

                                    <div class="rounded border border-purple-300 bg-purple-50 p-4">
                                        <div class="mb-3 rounded border border-purple-200 bg-white px-4 py-3 text-purple-900">
                                            <strong>Caso de estudio:</strong> analiza la situación y redacta tu respuesta con claridad.
                                        </div>

                                        <textarea
                                            name="respuestas[{{ $pregunta->id_ejercicio_pregunta }}][texto]"
                                            rows="8"
                                            class="w-full rounded border-gray-300 text-black caso-estudio-textarea"
                                            placeholder="{{ $placeholderCaso }}"
                                            @if(!is_null($maxCaso)) maxlength="{{ $maxCaso }}" @endif
                                        >{{ $valorCasoOld }}</textarea>

                                        <div class="mt-2 text-sm text-slate-600 flex flex-wrap gap-4">
                                            @if(!is_null($minCaso))
                                                <span>Mínimo: {{ $minCaso }} caracteres</span>
                                            @endif

                                            @if(!is_null($maxCaso))
                                                <span>Máximo: {{ $maxCaso }} caracteres</span>
                                            @endif

                                            <span class="caso-estudio-contador">Caracteres: {{ mb_strlen($valorCasoOld) }}</span>
                                        </div>
                                    </div>
                                @else
                                    <input
                                        type="text"
                                        name="respuestas[{{ $pregunta->id_ejercicio_pregunta }}][texto]"
                                        value="{{ old('respuestas.' . $pregunta->id_ejercicio_pregunta . '.texto') }}"
                                        class="w-full rounded border-gray-300 text-black"
                                    >
                                @endif

                            </div>
                        </div>
                    @endforeach

                    <div class="flex gap-3">
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded">
                            Finalizar ejercicio
                        </button>

                        <a href="{{ route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $ejercicio->modulo->id_capacitacion_modulo]) }}#contenido-ejercicio-{{ $ejercicio->id_ejercicio }}"
                            target="{{ request()->boolean('integrado_modulo') ? '_top' : '_self' }}"
                            class="px-4 py-2 bg-gray-600 text-white rounded">
                                Cancelar
                        </a>
                    </div>
                </form>
            @else
                <div class="flex gap-3">
                    <a href="{{ route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $ejercicio->modulo->id_capacitacion_modulo]) }}#contenido-ejercicio-{{ $ejercicio->id_ejercicio }}"
                        target="{{ request()->boolean('integrado_modulo') ? '_top' : '_self' }}"
                        class="px-4 py-2 bg-gray-600 text-white rounded">
                            Volver al módulo
                    </a>
                </div>
            @endif

        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.ordenar-container').forEach(function (container) {
                let draggedItem = null;

                function actualizarOrdenVisual() {
                    const items = Array.from(container.querySelectorAll('.ordenar-item'));

                    items.forEach(function (item, index) {
                        const posicion = index + 1;
                        const badge = item.querySelector('.ordenar-posicion');
                        const input = item.querySelector('.ordenar-input');

                        if (badge) {
                            badge.textContent = posicion;
                        }

                        if (input) {
                            input.value = posicion;
                        }
                    });
                }

                container.querySelectorAll('.ordenar-item').forEach(function (item) {
                    item.addEventListener('dragstart', function () {
                        draggedItem = item;
                        item.classList.add('opacity-50');
                    });

                    item.addEventListener('dragend', function () {
                        item.classList.remove('opacity-50');
                        draggedItem = null;
                        actualizarOrdenVisual();
                    });

                    item.addEventListener('dragover', function (event) {
                        event.preventDefault();
                    });

                    item.addEventListener('drop', function (event) {
                        event.preventDefault();

                        if (!draggedItem || draggedItem === item) {
                            return;
                        }

                        const items = Array.from(container.querySelectorAll('.ordenar-item'));
                        const draggedIndex = items.indexOf(draggedItem);
                        const targetIndex = items.indexOf(item);

                        if (draggedIndex < targetIndex) {
                            container.insertBefore(draggedItem, item.nextSibling);
                        } else {
                            container.insertBefore(draggedItem, item);
                        }

                        actualizarOrdenVisual();
                    });
                });

                actualizarOrdenVisual();
            });

            document.querySelectorAll('.relacionar-board').forEach(function (board) {
                let itemActivo = null;

                const ayuda = board.querySelector('.relacionar-ayuda');
                const textoAyudaDefault = 'Paso 1: haz clic en un elemento de la izquierda. Paso 2: haz clic en su pareja correcta a la derecha.';

                const leftItems = Array.from(board.querySelectorAll('.relacionar-left-item'));
                const rightOptions = Array.from(board.querySelectorAll('.relacionar-right-option'));

                function limpiarEstadoActivo() {
                    leftItems.forEach(function (item) {
                        item.classList.remove('ring-2', 'ring-blue-500', 'border-blue-500');
                    });
                }

                function actualizarVisualRelacionar() {
                    const usados = {};

                    leftItems.forEach(function (item) {
                        const input = item.querySelector('.relacionar-input');
                        const estado = item.querySelector('.relacionar-estado');
                        const seleccion = item.querySelector('.relacionar-seleccion');
                        const valor = input ? input.value : '';

                        if (valor) {
                            const boton = board.querySelector('.relacionar-right-option[data-right-id="' + valor + '"]');
                            const texto = boton ? boton.dataset.rightLabel : 'Todavía no has elegido una opción.';

                            if (seleccion) {
                                seleccion.textContent = texto;
                            }

                            if (estado) {
                                estado.textContent = 'Asignado';
                                estado.classList.remove('bg-slate-200', 'text-slate-700');
                                estado.classList.add('bg-emerald-100', 'text-emerald-700');
                            }

                            usados[valor] = true;
                        } else {
                            if (seleccion) {
                                seleccion.textContent = 'Todavía no has elegido una opción.';
                            }

                            if (estado) {
                                estado.textContent = 'Sin asignar';
                                estado.classList.remove('bg-emerald-100', 'text-emerald-700');
                                estado.classList.add('bg-slate-200', 'text-slate-700');
                            }
                        }
                    });

                    rightOptions.forEach(function (boton) {
                        boton.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50');

                        if (usados[boton.dataset.rightId]) {
                            boton.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50');
                        }
                    });
                }

                leftItems.forEach(function (item) {
                    item.addEventListener('click', function (event) {
                        if (event.target.closest('.relacionar-limpiar')) {
                            return;
                        }

                        limpiarEstadoActivo();
                        item.classList.add('ring-2', 'ring-blue-500', 'border-blue-500');
                        itemActivo = item;

                        const textoPrincipal = item.querySelector('.font-medium')?.textContent?.trim() ?? 'este elemento';

                        if (ayuda) {
                            ayuda.textContent = 'Ahora elige una opción de la derecha para: "' + textoPrincipal + '".';
                            ayuda.classList.remove('text-red-700');
                            ayuda.classList.add('text-blue-700');
                        }
                    });

                    const botonLimpiar = item.querySelector('.relacionar-limpiar');

                    if (botonLimpiar) {
                        botonLimpiar.addEventListener('click', function (event) {
                            event.stopPropagation();

                            const input = item.querySelector('.relacionar-input');

                            if (input) {
                                input.value = '';
                            }

                            if (itemActivo === item) {
                                itemActivo = null;
                            }

                            item.classList.remove('ring-2', 'ring-blue-500', 'border-blue-500');

                            if (ayuda) {
                                ayuda.textContent = textoAyudaDefault;
                                ayuda.classList.remove('text-red-700', 'text-blue-700');
                            }

                            actualizarVisualRelacionar();
                        });
                    }
                });

                rightOptions.forEach(function (boton) {
                    boton.addEventListener('click', function () {
                        if (!itemActivo) {
                            if (ayuda) {
                                ayuda.textContent = 'Primero selecciona un elemento de la izquierda.';
                                ayuda.classList.remove('text-blue-700');
                                ayuda.classList.add('text-red-700');
                            }
                            return;
                        }

                        const rightId = boton.dataset.rightId;

                        leftItems.forEach(function (item) {
                            const input = item.querySelector('.relacionar-input');

                            if (item !== itemActivo && input && input.value === rightId) {
                                input.value = '';
                            }
                        });

                        const inputActivo = itemActivo.querySelector('.relacionar-input');

                        if (inputActivo) {
                            inputActivo.value = rightId;
                        }

                        itemActivo.classList.remove('ring-2', 'ring-blue-500', 'border-blue-500');
                        itemActivo = null;

                        if (ayuda) {
                            ayuda.textContent = textoAyudaDefault;
                            ayuda.classList.remove('text-red-700', 'text-blue-700');
                        }

                        actualizarVisualRelacionar();
                    });
                });

                actualizarVisualRelacionar();
            });
        });

        document.querySelectorAll('.respuesta-breve-input').forEach(function (input) {
            const contenedor = input.closest('.rounded');
            const contador = contenedor ? contenedor.querySelector('.respuesta-breve-contador') : null;

            function actualizarContador() {
                const valor = input.value || '';
                if (contador) {
                    contador.textContent = 'Caracteres: ' + valor.length;
                }
            }

            input.addEventListener('input', actualizarContador);
            actualizarContador();
        });

        document.querySelectorAll('.caso-estudio-textarea').forEach(function (textarea) {
            const contenedor = textarea.closest('.rounded');
            const contador = contenedor ? contenedor.querySelector('.caso-estudio-contador') : null;

            function actualizarContador() {
                const valor = textarea.value || '';
                if (contador) {
                    contador.textContent = 'Caracteres: ' + valor.length;
                }
            }

            textarea.addEventListener('input', actualizarContador);
            actualizarContador();
        });

        document.querySelectorAll('.caso-estudio-textarea').forEach(function (textarea) {
            const contenedor = textarea.closest('.rounded');
            const contador = contenedor ? contenedor.querySelector('.caso-estudio-contador') : null;

            function actualizarContador() {
                const valor = textarea.value || '';
                if (contador) {
                    contador.textContent = 'Caracteres: ' + valor.length;
                }
            }

            textarea.addEventListener('input', actualizarContador);
            actualizarContador();
        });

    </script>

        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            document.addEventListener('DOMContentLoaded', function () {
                const segundosIniciales = @json($segundosRestantes);

                if (segundosIniciales === null) {
                    return;
                }

                let segundosRestantes = parseInt(segundosIniciales, 10);
                let intervalo = null;

                const timer = document.getElementById('timerEjercicio');
                const form = document.getElementById('formEjercicio');

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