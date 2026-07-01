<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Calificaciones de la capacitación
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">


            {{-- RESUMEN GENERAL --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Capacitación
                            </p>

                            <h3 class="text-2xl font-bold">
                                {{ $miCapacitacion->capacitacion->capacitacion ?? 'Capacitación' }}
                            </h3>

                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                Aquí puedes revisar tus notas, intentos y avance por módulo.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 min-w-full lg:min-w-[520px]">
                            <div class="rounded border border-blue-300 bg-blue-100 px-4 py-3 text-blue-900">
                                <p class="text-xs uppercase font-bold">Progreso</p>
                                <p class="mt-2 text-2xl font-bold">
                                    {{ number_format((float) ($miCapacitacion->progreso ?? 0), 2) }}%
                                </p>
                            </div>

                            <div class="rounded border border-green-300 bg-green-100 px-4 py-3 text-green-900">
                                <p class="text-xs uppercase font-bold">Nota final</p>
                                <p class="mt-2 text-2xl font-bold">
                                    {{ !is_null($miCapacitacion->nota_final) ? number_format((float) $miCapacitacion->nota_final, 2) . '%' : '-' }}
                                </p>
                            </div>

                            <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-900">
                                <p class="text-xs uppercase font-bold">Estado</p>
                                <p class="mt-2 text-2xl font-bold">
                                    {{ ucfirst(str_replace('_', ' ', $miCapacitacion->estado ?? 'pendiente')) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CALIFICACIONES POR MÓDULO --}}
            <div class="space-y-4">
                @forelse($modulos as $modulo)
                    @php
                        $avanceModulo = $avancesModulo->get($modulo->id_capacitacion_modulo);

                        $estadoModulo = $avanceModulo->estado ?? 'pendiente';
                        $progresoModulo = $avanceModulo->progreso ?? 0;
                        $notaModulo = $avanceModulo->nota ?? null;

                        $totalActividadesModulo = $modulo->evaluaciones->count() + $modulo->ejercicios->count();
                    @endphp

                    <details class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden" open>
                        <summary class="cursor-pointer px-6 py-4 bg-gray-50 dark:bg-gray-900">
                            <div class="inline-flex w-full flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-gray-100">
                                        Módulo {{ $modulo->orden }}: {{ $modulo->titulo }}
                                    </h4>

                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $modulo->evaluaciones->count() }} evaluación(es) ·
                                        {{ $modulo->ejercicios->count() }} ejercicio(s)
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                                        Progreso: {{ number_format((float) $progresoModulo, 2) }}%
                                    </span>

                                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">
                                        Nota: {{ !is_null($notaModulo) ? number_format((float) $notaModulo, 2) . '%' : '-' }}
                                    </span>

                                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800 text-xs font-semibold">
                                        {{ ucfirst(str_replace('_', ' ', $estadoModulo)) }}
                                    </span>
                                </div>
                            </div>
                        </summary>

                        <div class="p-6 space-y-5">

                            @if($totalActividadesModulo > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full border border-gray-300 text-sm">
                                        <thead class="bg-gray-100 text-black">
                                            <tr>
                                                <th class="px-4 py-2 border text-left">Actividad</th>
                                                <th class="px-4 py-2 border">Tipo</th>
                                                <th class="px-4 py-2 border">Intentos</th>
                                                <th class="px-4 py-2 border">Mejor nota</th>
                                                <th class="px-4 py-2 border">Último resultado</th>
                                                <th class="px-4 py-2 border">Fecha</th>
                                                <th class="px-4 py-2 border">Detalle</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            {{-- EVALUACIONES --}}
                                            @foreach($modulo->evaluaciones as $evaluacion)
                                                @php
                                                    $intentos = $intentosEvaluacion->get($evaluacion->id_evaluacion, collect());
                                                    $ultimoIntento = $intentos->first();
                                                    $mejorNota = $intentos->max('nota');
                                                @endphp

                                                <tr>
                                                    <td class="px-4 py-2 border">
                                                        <div class="font-semibold text-gray-900">
                                                            {{ $evaluacion->titulo }}
                                                        </div>

                                                        @if($evaluacion->descripcion)
                                                            <div class="text-xs text-gray-500 mt-1">
                                                                {{ $evaluacion->descripcion }}
                                                            </div>
                                                        @endif
                                                    </td>

                                                    <td class="px-4 py-2 border text-center">
                                                        <span class="px-2 py-1 rounded bg-purple-100 text-purple-800">
                                                            Evaluación
                                                        </span>
                                                    </td>

                                                    <td class="px-4 py-2 border text-center">
                                                        {{ $intentos->count() }}
                                                    </td>

                                                    <td class="px-4 py-2 border text-center">
                                                        {{ !is_null($mejorNota) ? number_format((float) $mejorNota, 2) . '%' : '-' }}
                                                    </td>

                                                    <td class="px-4 py-2 border text-center">
                                                        @if($ultimoIntento)
                                                            @if((int) $ultimoIntento->aprobado === 1)
                                                                <span class="px-2 py-1 rounded bg-green-100 text-green-800">
                                                                    Aprobado
                                                                </span>
                                                            @else
                                                                <span class="px-2 py-1 rounded bg-red-100 text-red-800">
                                                                    Reprobado
                                                                </span>
                                                            @endif
                                                        @else
                                                            <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800">
                                                                Pendiente
                                                            </span>
                                                        @endif
                                                    </td>

                                                    <td class="px-4 py-2 border text-center">
                                                        {{ $ultimoIntento->fecha_fin ?? '-' }}
                                                    </td>

                                                    <td class="px-4 py-2 border text-center">
                                                        @if($ultimoIntento)
                                                            <a href="{{ route('mis_evaluaciones.resultado', [$miCapacitacion->id_empleado_capacitacion, $ultimoIntento->id_evaluacion_intento]) }}"
                                                               class="px-3 py-1 bg-slate-700 text-white rounded">
                                                                Ver último detalle
                                                            </a>
                                                        @else
                                                            <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded">
                                                                Sin intento
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>

                                                @if($intentos->count() > 0)
                                                    <tr>
                                                        <td colspan="7" class="px-4 py-3 border bg-gray-50">
                                                            <details class="rounded border border-gray-300 bg-white overflow-hidden">
                                                                <summary class="cursor-pointer px-4 py-3 font-semibold text-gray-800 hover:bg-gray-100">
                                                                    Historial de intentos de esta evaluación
                                                                    <span class="text-sm text-gray-500">
                                                                        ({{ $intentos->count() }} intento(s))
                                                                    </span>
                                                                </summary>

                                                                <div class="p-4 overflow-x-auto">
                                                                    <table class="min-w-full border border-gray-300 text-sm">
                                                                        <thead class="bg-gray-100 text-black">
                                                                            <tr>
                                                                                <th class="px-4 py-2 border">Intento</th>
                                                                                <th class="px-4 py-2 border">Nota</th>
                                                                                <th class="px-4 py-2 border">Resultado</th>
                                                                                <th class="px-4 py-2 border">Fecha inicio</th>
                                                                                <th class="px-4 py-2 border">Fecha fin</th>
                                                                                <th class="px-4 py-2 border">Detalle</th>
                                                                            </tr>
                                                                        </thead>

                                                                        <tbody>
                                                                            @foreach($intentos as $intentoHistorial)
                                                                                <tr class="text-center">
                                                                                    <td class="px-4 py-2 border">
                                                                                        {{ $intentoHistorial->numero_intento }}
                                                                                    </td>

                                                                                    <td class="px-4 py-2 border">
                                                                                        {{ !is_null($intentoHistorial->nota) ? number_format((float) $intentoHistorial->nota, 2) . '%' : '-' }}
                                                                                    </td>

                                                                                    <td class="px-4 py-2 border">
                                                                                        @if((int) $intentoHistorial->aprobado === 1)
                                                                                            <span class="px-2 py-1 rounded bg-green-100 text-green-800">
                                                                                                Aprobado
                                                                                            </span>
                                                                                        @else
                                                                                            <span class="px-2 py-1 rounded bg-red-100 text-red-800">
                                                                                                Reprobado
                                                                                            </span>
                                                                                        @endif
                                                                                    </td>

                                                                                    <td class="px-4 py-2 border">
                                                                                        {{ $intentoHistorial->fecha_inicio ?? '-' }}
                                                                                    </td>

                                                                                    <td class="px-4 py-2 border">
                                                                                        {{ $intentoHistorial->fecha_fin ?? '-' }}
                                                                                    </td>

                                                                                    <td class="px-4 py-2 border">
                                                                                        <a href="{{ route('mis_evaluaciones.resultado', [$miCapacitacion->id_empleado_capacitacion, $intentoHistorial->id_evaluacion_intento]) }}"
                                                                                           class="px-3 py-1 bg-slate-700 text-white rounded">
                                                                                            Ver detalle
                                                                                        </a>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </details>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach

                                            {{-- EJERCICIOS --}}
                                            @foreach($modulo->ejercicios as $ejercicio)
                                                @php
                                                    $intentos = $intentosEjercicio->get($ejercicio->id_ejercicio, collect());
                                                    $ultimoIntento = $intentos->first();

                                                    $mejorNota = $intentos
                                                        ->map(function ($intento) {
                                                            return $intento->porcentaje_obtenido ?? $intento->nota ?? null;
                                                        })
                                                        ->filter(fn($valor) => !is_null($valor))
                                                        ->max();

                                                    $notaUltimo = $ultimoIntento
                                                        ? ($ultimoIntento->porcentaje_obtenido ?? $ultimoIntento->nota ?? null)
                                                        : null;
                                                @endphp

                                                <tr>
                                                    <td class="px-4 py-2 border">
                                                        <div class="font-semibold text-gray-900">
                                                            {{ $ejercicio->titulo }}
                                                        </div>

                                                        @if($ejercicio->descripcion)
                                                            <div class="text-xs text-gray-500 mt-1">
                                                                {{ $ejercicio->descripcion }}
                                                            </div>
                                                        @endif
                                                    </td>

                                                    <td class="px-4 py-2 border text-center">
                                                        <span class="px-2 py-1 rounded bg-emerald-100 text-emerald-800">
                                                            Ejercicio
                                                        </span>
                                                    </td>

                                                    <td class="px-4 py-2 border text-center">
                                                        {{ $intentos->count() }}
                                                    </td>

                                                    <td class="px-4 py-2 border text-center">
                                                        {{ !is_null($mejorNota) ? number_format((float) $mejorNota, 2) . '%' : '-' }}
                                                    </td>

                                                    <td class="px-4 py-2 border text-center">
                                                        @if($ultimoIntento)
                                                            @if((int) $ultimoIntento->aprobado === 1)
                                                                <span class="px-2 py-1 rounded bg-green-100 text-green-800">
                                                                    Aprobado
                                                                </span>
                                                            @elseif($ultimoIntento->estado === 'pendiente_revision')
                                                                <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800">
                                                                    Pendiente revisión
                                                                </span>
                                                            @else
                                                                <span class="px-2 py-1 rounded bg-red-100 text-red-800">
                                                                    No aprobado
                                                                </span>
                                                            @endif
                                                        @else
                                                            <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800">
                                                                Pendiente
                                                            </span>
                                                        @endif
                                                    </td>

                                                    <td class="px-4 py-2 border text-center">
                                                        {{ $ultimoIntento->fecha_fin ?? '-' }}
                                                    </td>

                                                    <td class="px-4 py-2 border text-center">
                                                        @if($ultimoIntento)
                                                            <a href="{{ route('mis_ejercicios.resultado', [$miCapacitacion->id_empleado_capacitacion, $ultimoIntento->id_ejercicio_intento]) }}"
                                                               class="px-3 py-1 bg-slate-700 text-white rounded">
                                                                Ver último intento
                                                            </a>
                                                        @else
                                                            <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded">
                                                                Sin intento
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>

                                                @if($intentos->count() > 0)
                                                    <tr>
                                                        <td colspan="7" class="px-4 py-3 border bg-gray-50">
                                                            <details class="rounded border border-gray-300 bg-white overflow-hidden">
                                                                <summary class="cursor-pointer px-4 py-3 font-semibold text-gray-800 hover:bg-gray-100">
                                                                    Historial de intentos de este ejercicio
                                                                    <span class="text-sm text-gray-500">
                                                                        ({{ $intentos->count() }} intento(s))
                                                                    </span>
                                                                </summary>

                                                                <div class="p-4 overflow-x-auto">
                                                                    <table class="min-w-full border border-gray-300 text-sm">
                                                                        <thead class="bg-gray-100 text-black">
                                                                            <tr>
                                                                                <th class="px-4 py-2 border">Intento</th>
                                                                                <th class="px-4 py-2 border">Nota</th>
                                                                                <th class="px-4 py-2 border">Estado</th>
                                                                                <th class="px-4 py-2 border">Fecha inicio</th>
                                                                                <th class="px-4 py-2 border">Fecha fin</th>
                                                                                <th class="px-4 py-2 border">Detalle</th>
                                                                            </tr>
                                                                        </thead>

                                                                        <tbody>
                                                                            @foreach($intentos as $intentoHistorial)
                                                                                @php
                                                                                    $notaIntentoEjercicio = $intentoHistorial->porcentaje_obtenido
                                                                                        ?? $intentoHistorial->nota
                                                                                        ?? null;
                                                                                @endphp

                                                                                <tr class="text-center">
                                                                                    <td class="px-4 py-2 border">
                                                                                        {{ $intentoHistorial->numero_intento }}
                                                                                    </td>

                                                                                    <td class="px-4 py-2 border">
                                                                                        {{ !is_null($notaIntentoEjercicio) ? number_format((float) $notaIntentoEjercicio, 2) . '%' : '-' }}
                                                                                    </td>

                                                                                    <td class="px-4 py-2 border">
                                                                                        @if((int) $intentoHistorial->aprobado === 1)
                                                                                            <span class="px-2 py-1 rounded bg-green-100 text-green-800">
                                                                                                Aprobado
                                                                                            </span>
                                                                                        @elseif($intentoHistorial->estado === 'pendiente_revision')
                                                                                            <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800">
                                                                                                Pendiente revisión
                                                                                            </span>
                                                                                        @else
                                                                                            <span class="px-2 py-1 rounded bg-red-100 text-red-800">
                                                                                                No aprobado
                                                                                            </span>
                                                                                        @endif
                                                                                    </td>

                                                                                    <td class="px-4 py-2 border">
                                                                                        {{ $intentoHistorial->fecha_inicio ?? '-' }}
                                                                                    </td>

                                                                                    <td class="px-4 py-2 border">
                                                                                        {{ $intentoHistorial->fecha_fin ?? '-' }}
                                                                                    </td>

                                                                                    <td class="px-4 py-2 border">
                                                                                        <a href="{{ route('mis_ejercicios.resultado', [$miCapacitacion->id_empleado_capacitacion, $intentoHistorial->id_ejercicio_intento]) }}"
                                                                                           class="px-3 py-1 bg-slate-700 text-white rounded">
                                                                                            Ver detalle
                                                                                        </a>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </details>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="rounded border border-gray-300 bg-gray-100 px-4 py-3 text-gray-800">
                                    Este módulo todavía no tiene evaluaciones ni ejercicios con calificación.
                                </div>
                            @endif
                        </div>
                    </details>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <p class="text-gray-600 dark:text-gray-300">
                            Esta capacitación todavía no tiene módulos disponibles.
                        </p>
                    </div>
                @endforelse
            </div>

            <div>
                <a href="{{ route('mis_capacitaciones.show', $miCapacitacion->id_empleado_capacitacion) }}"
                   class="px-4 py-2 bg-gray-700 text-white rounded">
                    Volver a la capacitación
                </a>
            </div>

        </div>
    </div>
</x-app-layout>