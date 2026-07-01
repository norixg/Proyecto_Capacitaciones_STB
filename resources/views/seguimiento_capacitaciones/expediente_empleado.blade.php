<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Expediente administrativo
            </p>

            <h2 class="esf-seguimiento-title">
                Expediente del empleado
            </h2>

            <p class="esf-seguimiento-subtitle">
                Historial consolidado de capacitaciones, módulos, intentos, avances, vencimientos y resultados.
            </p>
        </div>
    </x-slot>

    <div class="py-8 esf-seguimiento-page esf-history-page esf-admin-detail-page">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @php
                $claseEstado = function ($estado) {
                    return match($estado) {
                        'pendiente' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                        'en_proceso' => 'bg-blue-100 text-blue-800 border-blue-300',
                        'aprobada' => 'bg-green-100 text-green-800 border-green-300',
                        'reprobada' => 'bg-red-100 text-red-800 border-red-300',
                        'vencida' => 'bg-orange-100 text-orange-800 border-orange-300',
                        'cancelada' => 'bg-gray-200 text-gray-800 border-gray-300',
                        default => 'bg-gray-100 text-gray-800 border-gray-300',
                    };
                };
            @endphp

            <div class="esf-history-card">
                <div class="esf-history-card-header">
                    <div>
                        <p class="esf-history-kicker">Empleado</p>

                        <h3 class="esf-history-heading text-2xl">
                            {{ $empleado->nombre_completo }}
                        </h3>

                        <p class="esf-history-subtitle">
                            Expediente general de capacitaciones, intentos, avances, vencimientos, revisiones manuales y resultados.
                        </p>

                        <p class="mt-1 text-xs font-semibold text-slate-400 dark:text-slate-500">
                            Esta vista consolida lo que el usuario ha realizado durante su paso por las capacitaciones asignadas.
                        </p>
                    </div>

                    <div class="esf-history-actions">
                        <a href="{{ route('reportes.empleado.expediente_pdf', $empleado->id_empleado) }}"
                           class="esf-history-btn-green">
                            Exportar expediente PDF
                        </a>

                        <a href="{{ route('seguimiento_capacitaciones.index') }}"
                           class="esf-history-btn-primary">
                            Volver al monitoreo
                        </a>
                    </div>
                </div>
            </div>

            <div class="esf-history-card">
                <div class="esf-history-body">
                    <p class="esf-history-kicker">Datos laborales</p>
                    <h4 class="esf-history-heading mb-4">Datos del empleado</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Código</strong>
                            <div class="mt-2">{{ $empleado->codigo_empleado ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Identidad</strong>
                            <div class="mt-2">{{ $empleado->identidad ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Correo</strong>
                            <div class="mt-2">{{ $empleado->correo ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Teléfono</strong>
                            <div class="mt-2">{{ $empleado->telefono ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Puesto</strong>
                            <div class="mt-2">{{ $empleado->puestoTrabajo?->puesto_trabajo_matriz ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Departamento</strong>
                            <div class="mt-2">{{ $empleado->puestoTrabajo?->departamento?->departamento ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Fecha ingreso</strong>
                            <div class="mt-2">{{ $empleado->fecha_ingreso?->format('d/m/Y') ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Estado empleado</strong>
                            <div class="mt-2">{{ (int) $empleado->estado === 1 ? 'Activo' : 'Inactivo' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="rounded border border-slate-300 bg-slate-100 px-4 py-4 text-slate-800">
                    <div class="text-sm font-semibold uppercase">Capacitaciones asignadas</div>
                    <div class="mt-2 text-3xl font-bold">{{ $totales['asignadas'] }}</div>
                </div>

                <div class="rounded border border-green-300 bg-green-100 px-4 py-4 text-green-800">
                    <div class="text-sm font-semibold uppercase">Aprobadas</div>
                    <div class="mt-2 text-3xl font-bold">{{ $totales['aprobadas'] }}</div>
                </div>

                <div class="rounded border border-blue-300 bg-blue-100 px-4 py-4 text-blue-800">
                    <div class="text-sm font-semibold uppercase">En proceso</div>
                    <div class="mt-2 text-3xl font-bold">{{ $totales['en_proceso'] }}</div>
                </div>

                <div class="rounded border border-yellow-300 bg-yellow-100 px-4 py-4 text-yellow-800">
                    <div class="text-sm font-semibold uppercase">Pendientes</div>
                    <div class="mt-2 text-3xl font-bold">{{ $totales['pendientes'] }}</div>
                </div>

                <div class="rounded border border-red-300 bg-red-100 px-4 py-4 text-red-800">
                    <div class="text-sm font-semibold uppercase">Reprobadas</div>
                    <div class="mt-2 text-3xl font-bold">{{ $totales['reprobadas'] }}</div>
                </div>

                <div class="rounded border border-orange-300 bg-orange-100 px-4 py-4 text-orange-800">
                    <div class="text-sm font-semibold uppercase">Vencidas</div>
                    <div class="mt-2 text-3xl font-bold">{{ $totales['vencidas'] }}</div>
                </div>

                <div class="rounded border border-amber-300 bg-amber-100 px-4 py-4 text-amber-800">
                    <div class="text-sm font-semibold uppercase">Próximas a vencer</div>
                    <div class="mt-2 text-3xl font-bold">{{ $totales['por_vencer'] }}</div>
                    <div class="text-xs mt-1">Dentro de 30 días</div>
                </div>

                <div class="rounded border border-indigo-300 bg-indigo-100 px-4 py-4 text-indigo-800">
                    <div class="text-sm font-semibold uppercase">Pendientes de revisión</div>
                    <div class="mt-2 text-3xl font-bold">{{ $totales['pendientes_revision'] }}</div>
                </div>
            </div>

            <div class="esf-history-card">
                <div class="esf-history-body">
                    <p class="esf-history-kicker">Resumen consolidado</p>
                    <h4 class="esf-history-heading mb-4">Resumen académico</h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded border border-purple-300 bg-purple-100 px-4 py-3 text-purple-800">
                            <strong>Intentos de evaluación</strong>
                            <div class="mt-2 text-2xl font-bold">{{ $totales['intentos_evaluacion'] }}</div>
                        </div>

                        <div class="rounded border border-blue-300 bg-blue-100 px-4 py-3 text-blue-800">
                            <strong>Intentos de ejercicios</strong>
                            <div class="mt-2 text-2xl font-bold">{{ $totales['intentos_ejercicio'] }}</div>
                        </div>

                        <div class="rounded border border-green-300 bg-green-100 px-4 py-3 text-green-800">
                            <strong>Nota promedio registrada</strong>
                            <div class="mt-2 text-2xl font-bold">
                                {{ !is_null($totales['nota_promedio']) ? number_format((float) $totales['nota_promedio'], 2) . '%' : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                @forelse($capacitaciones as $item)
                    @php
                        $estadoItemClase = $claseEstado($item->estado);

                        $ultimaActividad = collect([
                            $item->fecha_inicio,
                            $item->modulosAvance->max('fecha_ultima_actividad'),
                            $item->intentosEvaluacion->max('fecha_fin'),
                            $item->intentosEjercicio->max('fecha_fin'),
                            $item->intentosEjercicio->max('fecha_inicio'),
                        ])->filter()->map(function ($fecha) {
                            return $fecha instanceof \Illuminate\Support\Carbon
                                ? $fecha
                                : \Illuminate\Support\Carbon::parse($fecha);
                        })->sortDesc()->first();
                    @endphp

                    <details class="esf-history-card border border-gray-200 dark:border-gray-700">
                        <summary class="cursor-pointer px-6 py-4 bg-gray-50 dark:bg-gray-900">
                            <div class="inline-flex w-full flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                        {{ $item->capacitacion?->capacitacion ?? 'Capacitación' }}
                                    </h4>

                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Asignada: {{ $item->fecha_asignacion?->format('d/m/Y') ?? '-' }}
                                        · Última actividad: {{ $ultimaActividad ? $ultimaActividad->format('d/m/Y H:i') : '-' }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <span class="px-3 py-1 rounded-full border text-xs font-semibold {{ $estadoItemClase }}">
                                        {{ ucfirst(str_replace('_', ' ', $item->estado ?? 'pendiente')) }}
                                    </span>

                                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold">
                                        Progreso: {{ number_format((float) ($item->progreso ?? 0), 2) }}%
                                    </span>

                                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">
                                        Nota: {{ !is_null($item->nota_final) ? number_format((float) $item->nota_final, 2) . '%' : '-' }}
                                    </span>
                                </div>
                            </div>
                        </summary>

                        <div class="p-6 space-y-5 text-gray-900 dark:text-gray-100">

                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                                <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                                    <strong>Fecha inicio</strong>
                                    <div class="mt-2">{{ $item->fecha_inicio?->format('d/m/Y H:i') ?? '-' }}</div>
                                </div>

                                <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                                    <strong>Fecha finalización</strong>
                                    <div class="mt-2">{{ $item->fecha_finalizacion?->format('d/m/Y H:i') ?? '-' }}</div>
                                </div>

                                <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                                    <strong>Fecha límite</strong>
                                    <div class="mt-2">{{ $item->fecha_limite?->format('d/m/Y') ?? '-' }}</div>
                                </div>

                                <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                                    <strong>Fecha vencimiento</strong>
                                    <div class="mt-2">{{ $item->fecha_vencimiento?->format('d/m/Y') ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <p class="esf-history-kicker">Detalle interno</p>
                                <h5 class="font-black mb-3 text-slate-900 dark:text-slate-100">Detalle por módulo</h5>

                                <table class="esf-history-table">
                                    <thead class="bg-gray-100 text-black">
                                        <tr>
                                            <th class="px-4 py-2 border">Módulo</th>
                                            <th class="px-4 py-2 border">Estado</th>
                                            <th class="px-4 py-2 border">Progreso</th>
                                            <th class="px-4 py-2 border">Nota</th>
                                            <th class="px-4 py-2 border">Intentos evaluación</th>
                                            <th class="px-4 py-2 border">Intentos ejercicios</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($item->capacitacion?->capacitacionModulos ?? collect() as $modulo)
                                            @php
                                                $avanceModulo = $item->modulosAvance->firstWhere('id_capacitacion_modulo', $modulo->id_capacitacion_modulo);

                                                $intentosEvaluacionModulo = $item->intentosEvaluacion->filter(function ($intento) use ($modulo) {
                                                    return optional($intento->evaluacion)->id_capacitacion_modulo === $modulo->id_capacitacion_modulo;
                                                });

                                                $intentosEjercicioModulo = $item->intentosEjercicio->filter(function ($intento) use ($modulo) {
                                                    return optional($intento->ejercicio)->id_capacitacion_modulo === $modulo->id_capacitacion_modulo;
                                                });
                                            @endphp

                                            <tr class="text-center">
                                                <td class="px-4 py-2 border text-left">
                                                    {{ $modulo->orden }}. {{ $modulo->titulo }}
                                                </td>

                                                <td class="px-4 py-2 border">
                                                    {{ ucfirst(str_replace('_', ' ', $avanceModulo->estado ?? 'pendiente')) }}
                                                </td>

                                                <td class="px-4 py-2 border">
                                                    {{ number_format((float) ($avanceModulo->progreso ?? 0), 2) }}%
                                                </td>

                                                <td class="px-4 py-2 border">
                                                    {{ !is_null($avanceModulo?->nota) ? number_format((float) $avanceModulo->nota, 2) . '%' : '-' }}
                                                </td>

                                                <td class="px-4 py-2 border">
                                                    {{ $intentosEvaluacionModulo->count() }}
                                                </td>

                                                <td class="px-4 py-2 border">
                                                    {{ $intentosEjercicioModulo->count() }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-4 border text-center">
                                                    Esta capacitación no tiene módulos activos registrados.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('seguimiento_capacitaciones.show', $item->id_empleado_capacitacion) }}"
                                   class="esf-history-btn-primary">
                                    Abrir seguimiento de esta asignación
                                </a>
                            </div>

                            @if($item->historial->count() > 0)
                                <div class="overflow-x-auto">
                                    <p class="esf-history-kicker">Historial</p>
                                    <h5 class="font-black mb-3 text-slate-900 dark:text-slate-100">Historial formal registrado</h5>

                                    <table class="esf-history-table">
                                        <thead class="bg-gray-100 text-black">
                                            <tr>
                                                <th class="px-4 py-2 border">Fecha</th>
                                                <th class="px-4 py-2 border">Estado anterior</th>
                                                <th class="px-4 py-2 border">Estado nuevo</th>
                                                <th class="px-4 py-2 border">Observación</th>
                                                <th class="px-4 py-2 border">Usuario</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($item->historial->sortByDesc('fecha_movimiento') as $movimiento)
                                                <tr class="text-center">
                                                    <td class="px-4 py-2 border">
                                                        {{ $movimiento->fecha_movimiento?->format('d/m/Y H:i') ?? '-' }}
                                                    </td>

                                                    <td class="px-4 py-2 border">
                                                        {{ $movimiento->estado_anterior ?? '-' }}
                                                    </td>

                                                    <td class="px-4 py-2 border">
                                                        {{ $movimiento->estado_nuevo ?? '-' }}
                                                    </td>

                                                    <td class="px-4 py-2 border text-left">
                                                        {{ $movimiento->observacion ?? '-' }}
                                                    </td>

                                                    <td class="px-4 py-2 border">
                                                        {{ $movimiento->usuario?->name ?? '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="rounded border border-gray-300 bg-gray-100 px-4 py-3 text-gray-800">
                                    Todavía no hay movimientos formales registrados en la tabla de historial para esta asignación.
                                </div>
                            @endif
                        </div>
                    </details>
                @empty
                    <div class="esf-history-card">
                        <div class="esf-history-body text-center">
                            Este empleado todavía no tiene capacitaciones asignadas.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>