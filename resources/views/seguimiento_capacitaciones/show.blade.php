<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Seguimiento administrativo
            </p>

            <h2 class="esf-seguimiento-title">
                Detalle de una asignación
            </h2>

            <p class="esf-seguimiento-subtitle">
                Vista enfocada en un empleado y una capacitación específica: avance, módulos, evaluaciones, ejercicios e historial.
            </p>
        </div>
    </x-slot>

    <div class="py-8 esf-seguimiento-page esf-history-page esf-admin-detail-page">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @php
                $estadoClase = match($estadoGlobalReal) {
                    'pendiente' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                    'en_proceso' => 'bg-blue-100 text-blue-800 border-blue-300',
                    'aprobada' => 'bg-green-100 text-green-800 border-green-300',
                    'reprobada' => 'bg-red-100 text-red-800 border-red-300',
                    'vencida' => 'bg-orange-100 text-orange-800 border-orange-300',
                    'cancelada' => 'bg-gray-200 text-gray-800 border-gray-300',
                    'pendiente_revision' => 'bg-purple-100 text-purple-800 border-purple-300',
                    default => 'bg-gray-100 text-gray-800 border-gray-300',
                };

                $estadoTextoGlobal = match($estadoGlobalReal) {
                    'pendiente' => 'Pendiente',
                    'en_proceso' => 'En proceso',
                    'aprobada' => 'Aprobada',
                    'reprobada' => 'Reprobada por evaluación',
                    'vencida' => 'Reprobada por fecha límite',
                    'cancelada' => 'Cancelada',
                    'pendiente_revision' => 'Pendiente de revisión',
                    default => ucfirst(str_replace('_', ' ', $estadoGlobalReal)),
                };
            @endphp

            <div class="esf-history-card">
                <div class="esf-history-body">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3 mb-4">
                        <h3 class="text-2xl font-bold">
                            {{ $seguimiento->empleado?->nombre_completo ?? 'Empleado' }}
                        </h3>

                        <div class="flex flex-wrap gap-2">
                            @if($seguimiento->empleado)
                                <a href="{{ route('seguimiento_capacitaciones.expediente_empleado', $seguimiento->empleado->id_empleado) }}"
                                class="esf-history-btn-secondary">
                                    Expediente del empleado
                                </a>
                            @endif

                            <a href="{{ route('seguimiento_capacitaciones.index') }}"
                            class="esf-history-btn-primary">
                                Volver al monitoreo
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Capacitación</strong>
                            <div class="mt-2">{{ $seguimiento->capacitacion?->capacitacion ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Código empleado</strong>
                            <div class="mt-2">{{ $seguimiento->empleado?->codigo_empleado ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Puesto</strong>
                            <div class="mt-2">{{ $seguimiento->empleado?->puestoTrabajo?->puesto_trabajo_matriz ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Departamento</strong>
                            <div class="mt-2">{{ $seguimiento->empleado?->puestoTrabajo?->departamento?->departamento ?? '-' }}</div>
                        </div>

                        <div class="rounded border {{ $estadoClase }} px-4 py-3">
                            <strong>Estado global</strong>
                            <div class="mt-2 text-lg font-bold">
                                {{ $estadoTextoGlobal }}
                            </div>

                            @if($estadoGlobalReal === 'pendiente_revision')
                                <div class="text-xs mt-2">
                                    Hay ejercicios o respuestas que necesitan revisión manual del administrador.
                                </div>
                            @endif
                        </div>
                        <div class="rounded border border-blue-300 bg-blue-100 px-4 py-3 text-blue-800">
                            <strong>Progreso global</strong>
                            <div class="mt-2 text-lg font-bold">{{ number_format((float) $progresoGlobalReal, 2) }}%</div>
                        </div>

                        <div class="esf-history-note">
                            <strong>Nota final</strong>
                            <div class="mt-2 text-lg font-bold">
                                {{ !is_null($seguimiento->nota_final) ? number_format((float) $seguimiento->nota_final, 2) . '%' : '-' }}
                            </div>
                        </div>

                        <div class="rounded border border-indigo-300 bg-indigo-100 px-4 py-3 text-indigo-800">
                            <strong>Última actividad</strong>
                            <div class="mt-2 text-lg font-bold">
                                {{ $ultimaActividad ? $ultimaActividad->format('d/m/Y H:i') : '-' }}
                            </div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Fecha asignación</strong>
                            <div class="mt-2">{{ $seguimiento->fecha_asignacion?->format('d/m/Y') ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Fecha límite</strong>
                            <div class="mt-2">{{ $seguimiento->fecha_limite?->format('d/m/Y') ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Fecha de vencimiento</strong>
                            <div class="mt-2">{{ $seguimiento->fecha_vencimiento?->format('d/m/Y') ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Fecha inicio</strong>
                            <div class="mt-2">{{ $seguimiento->fecha_inicio?->format('d/m/Y H:i') ?? '-' }}</div>
                        </div>

                        <div class="rounded border border-slate-300 bg-slate-100 px-4 py-3 text-slate-800">
                            <strong>Fecha finalización</strong>
                            <div class="mt-2">{{ $seguimiento->fecha_finalizacion?->format('d/m/Y H:i') ?? '-' }}</div>
                        </div>

                        <div class="rounded border {{ $haContinuado ? 'border-green-300 bg-green-100 text-green-800' : 'border-gray-300 bg-gray-100 text-gray-800' }} px-4 py-3">
                            <strong>¿Ha continuado?</strong>
                            <div class="mt-2 text-lg font-bold">{{ $haContinuado ? 'Sí' : 'No' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded border border-green-300 bg-green-100 px-4 py-3 text-green-800">
                Este seguimiento ya registra avance de navegación por secciones, subsecciones, recursos, ejercicios y evaluaciones vistos por el empleado.
            </div>

            @if(($avancesContenido ?? collect())->count() > 0)
                <div class="esf-history-card">
                    <div class="esf-history-body overflow-x-auto">
                        <p class="esf-history-kicker">Actividad del empleado</p>
                        <h4 class="esf-history-heading mb-4">Avance de contenido</h4>

                        <table class="esf-history-table">
                            <thead class="bg-gray-100 text-black">
                                <tr>
                                    <th class="px-4 py-2 border">Fecha</th>
                                    <th class="px-4 py-2 border">Tipo</th>
                                    <th class="px-4 py-2 border">Módulo</th>
                                    <th class="px-4 py-2 border">Estado</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($avancesContenido as $avanceContenido)
                                    @php
                                        $moduloAvanceContenido = $seguimiento->capacitacion?->capacitacionModulos
                                            ?->firstWhere('id_capacitacion_modulo', $avanceContenido->id_capacitacion_modulo);
                                    @endphp

                                    <tr class="text-center">
                                        <td class="px-4 py-2 border">
                                            {{ $avanceContenido->fecha_ultima_actividad ? \Carbon\Carbon::parse($avanceContenido->fecha_ultima_actividad)->format('d/m/Y H:i') : '-' }}
                                        </td>

                                        <td class="px-4 py-2 border">
                                            {{ ucfirst($avanceContenido->tipo_contenido) }}
                                        </td>

                                        <td class="px-4 py-2 border">
                                            {{ $moduloAvanceContenido?->titulo ?? 'Módulo no identificado' }}
                                        </td>

                                        <td class="px-4 py-2 border">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-green-100 text-green-800">
                                                {{ ucfirst($avanceContenido->estado) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($seguimiento->historial->count() > 0)
                <div class="esf-history-card">
                    <div class="esf-history-body overflow-x-auto">
                        <p class="esf-history-kicker">Actividad del empleado</p>
                        <h4 class="esf-history-heading mb-4">Historial formal de movimientos</h4>

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
                                @foreach($seguimiento->historial as $movimiento)
                                    <tr class="text-center">
                                        <td class="px-4 py-2 border">
                                            {{ $movimiento->fecha_movimiento?->format('d/m/Y H:i') ?? '-' }}
                                        </td>

                                        <td class="px-4 py-2 border">
                                            {{ $movimiento->estado_anterior ? ucfirst(str_replace('_', ' ', $movimiento->estado_anterior)) : '-' }}
                                        </td>

                                        <td class="px-4 py-2 border">
                                            {{ ucfirst(str_replace('_', ' ', $movimiento->estado_nuevo)) }}
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
                </div>
            @endif

            <div class="esf-history-card">
                <div class="esf-history-body overflow-x-auto">
                    <p class="esf-history-kicker">Contenido de la capacitación</p>
                    <h4 class="esf-history-heading mb-4">Seguimiento por módulo</h4>

                    <table class="esf-history-table">
                        <thead class="bg-gray-100 text-black">
                            <tr>
                                <th class="px-4 py-2 border">Orden</th>
                                <th class="px-4 py-2 border">Módulo</th>
                                <th class="px-4 py-2 border">Requiere evaluación</th>
                                <th class="px-4 py-2 border">Estado</th>
                                <th class="px-4 py-2 border">Progreso</th>
                                <th class="px-4 py-2 border">Nota</th>
                                <th class="px-4 py-2 border">Última actividad</th>
                                <th class="px-4 py-2 border">Intentos</th>
                                <th class="px-4 py-2 border">Último resultado</th>
                                <th class="px-4 py-2 border">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detalleModulos as $detalle)
                                @php
                                    $modulo = $detalle['modulo'];
                                    $avance = $detalle['avance'];
                                    $evaluacion = $detalle['evaluacion'];
                                    $ultimoIntento = $detalle['ultimo_intento'];

                                    $ultimaActividadModulo = collect([
                                        $avance?->fecha_ultima_actividad,
                                        $ultimoIntento?->fecha_fin,
                                    ])->filter()->sortDesc()->first();

                                    $estadoModulo = $avance->estado ?? 'pendiente';

                                    $estadoModulo = $detalle['estado_real'];

                                    $estadoModuloClase = match($estadoModulo) {
                                        'pendiente' => 'bg-yellow-100 text-yellow-800',
                                        'en_proceso' => 'bg-blue-100 text-blue-800',
                                        'completado' => 'bg-green-100 text-green-800',
                                        'reprobado' => 'bg-red-100 text-red-800',
                                        'pendiente_revision' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp

                                <tr class="text-center">
                                    <td class="px-4 py-2 border">{{ $modulo->orden }}</td>
                                    <td class="px-4 py-2 border">{{ $modulo->titulo }}</td>
                                    <td class="px-4 py-2 border">{{ (int) $modulo->requiere_evaluacion === 1 ? 'Sí' : 'No' }}</td>
                                    <td class="px-4 py-2 border">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estadoModuloClase }}">
                                            {{ ucfirst(str_replace('_', ' ', $estadoModulo)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 border">{{ number_format((float) $detalle['progreso_real'], 2) }}%</td>
                                    <td class="px-4 py-2 border">
                                        {{ !is_null($avance?->nota) ? number_format((float) $avance->nota, 2) . '%' : '-' }}
                                    </td>
                                    <td class="px-4 py-2 border">
                                        {{ $ultimaActividadModulo ? $ultimaActividadModulo->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="px-4 py-2 border">{{ $detalle['intentos_realizados'] }}</td>
                                    <td class="px-4 py-2 border">
                                        @if($ultimoIntento)
                                            {{ (int) $ultimoIntento->aprobado === 1 ? 'Aprobado' : 'Reprobado' }}
                                            ({{ number_format((float) $ultimoIntento->nota, 2) }}%)
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border">
                                        @if($ultimoIntento)
                                            <a href="{{ route('seguimiento_capacitaciones.intentos.show', [$seguimiento->id_empleado_capacitacion, $ultimoIntento->id_evaluacion_intento]) }}"
                                               class="esf-history-btn-primary">
                                                Ver intento
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-4 border text-center">
                                        Esta capacitación no tiene módulos activos.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="esf-history-card">
                <div class="esf-history-body overflow-x-auto">
                    <p class="esf-history-kicker">Evaluaciones</p>
                    <h4 class="esf-history-heading mb-4">Historial de intentos de evaluación</h4>

                    <table class="esf-history-table">
                        <thead class="bg-gray-100 text-black">
                            <tr>
                                <th class="px-4 py-2 border">Módulo</th>
                                <th class="px-4 py-2 border">Evaluación</th>
                                <th class="px-4 py-2 border">Intento</th>
                                <th class="px-4 py-2 border">Nota</th>
                                <th class="px-4 py-2 border">Resultado</th>
                                <th class="px-4 py-2 border">Fecha fin</th>
                                <th class="px-4 py-2 border">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($seguimiento->intentosEvaluacion as $intento)
                                <tr class="text-center">
                                    <td class="px-4 py-2 border">{{ $intento->evaluacion?->capacitacionModulo?->titulo ?? '-' }}</td>
                                    <td class="px-4 py-2 border">{{ $intento->evaluacion?->titulo ?? '-' }}</td>
                                    <td class="px-4 py-2 border">#{{ $intento->numero_intento }}</td>
                                    <td class="px-4 py-2 border">{{ number_format((float) $intento->nota, 2) }}%</td>
                                    <td class="px-4 py-2 border">
                                        @if((int) $intento->aprobado === 1)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Aprobado</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded">Reprobado</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 border">{{ $intento->fecha_fin?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="px-4 py-2 border">
                                        <a href="{{ route('seguimiento_capacitaciones.intentos.show', [$seguimiento->id_empleado_capacitacion, $intento->id_evaluacion_intento]) }}"
                                           class="esf-history-btn-primary">
                                            Ver intento
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-4 border text-center">
                                        No hay intentos registrados todavía.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

                @forelse($detalleModulos as $detalleModulo)
                    <div class="esf-history-card">
                        <div class="esf-history-body">
                            <p class="esf-history-kicker">Módulos y ejercicios</p>
                            <h4 class="esf-history-heading mb-4">
                                Módulo {{ $detalleModulo['modulo']->orden }}. {{ $detalleModulo['modulo']->titulo }}
                            </h4>

                            @if($detalleModulo['ejercicios']->count() > 0)
                                <div class="space-y-4">
                                    @foreach($detalleModulo['ejercicios'] as $detalleEjercicio)
                                        <div class="rounded border p-4">
                                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                                <div class="flex-1">
                                                    <h5 class="font-semibold text-lg">
                                                        {{ $detalleEjercicio['ejercicio']->orden }}. {{ $detalleEjercicio['ejercicio']->titulo }}
                                                    </h5>

                                                    @if($detalleEjercicio['ejercicio']->descripcion)
                                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                                            {{ $detalleEjercicio['ejercicio']->descripcion }}
                                                        </p>
                                                    @endif

                                                    <div class="mt-3 text-sm space-y-1">
                                                        <p><strong>Obligatorio:</strong> {{ (int) $detalleEjercicio['ejercicio']->obligatorio === 1 ? 'Sí' : 'No' }}</p>
                                                        <p><strong>Intentos realizados:</strong> {{ $detalleEjercicio['intentos_realizados'] }}</p>
                                                        <p><strong>Estado:</strong>
                                                            @if($detalleEjercicio['pendiente_revision'])
                                                                <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-xs font-semibold">Pendiente revisión</span>
                                                            @elseif($detalleEjercicio['completado'])
                                                                <span class="px-2 py-1 rounded bg-green-100 text-green-800 text-xs font-semibold">Completado</span>
                                                            @else
                                                                <span class="px-2 py-1 rounded bg-gray-100 text-gray-800 text-xs font-semibold">Pendiente</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            @if($detalleEjercicio['intentos']->count() > 0)
                                                <div class="mt-4 overflow-x-auto">
                                                    <table class="esf-history-table">
                                                        <thead class="bg-gray-100 text-black">
                                                            <tr>
                                                                <th class="px-4 py-2 border text-center">Intento</th>
                                                                <th class="px-4 py-2 border text-center">Estado</th>
                                                                <th class="px-4 py-2 border text-center">Resultado</th>
                                                                <th class="px-4 py-2 border text-center">Fecha fin</th>
                                                                <th class="px-4 py-2 border text-center">Acción</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($detalleEjercicio['intentos'] as $intento)
                                                                <tr>
                                                                    <td class="px-4 py-2 border text-center">#{{ $intento->numero_intento }}</td>
                                                                    <td class="px-4 py-2 border text-center">{{ ucfirst(str_replace('_', ' ', $intento->estado)) }}</td>
                                                                    <td class="px-4 py-2 border text-center">
                                                                        @if(is_null($intento->porcentaje_obtenido))
                                                                            -
                                                                        @else
                                                                            {{ number_format((float) $intento->porcentaje_obtenido, 2) }}%
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-4 py-2 border text-center">{{ $intento->fecha_fin ?? '-' }}</td>
                                                                        <td class="px-4 py-2 border text-center">
                                                                            <a href="{{ route('seguimiento_capacitaciones.ejercicio_intento.show', [$seguimiento->id_empleado_capacitacion, $intento->id_ejercicio_intento]) }}"
                                                                            class="esf-history-btn-primary">
                                                                                {{ $intento->estado === 'pendiente_revision' ? 'Revisar' : 'Ver' }}
                                                                            </a>
                                                                        </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="mt-4 rounded border border-gray-300 bg-gray-100 px-4 py-3 text-gray-800">
                                                    Este ejercicio todavía no tiene intentos registrados por el usuario.
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded border border-gray-300 bg-gray-100 px-4 py-3 text-gray-800">
                                    Este módulo no tiene ejercicios activos.
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="esf-history-card">
                        <div class="esf-history-body">
                            Esta capacitación no tiene módulos con ejercicios activos.
                        </div>
                    </div>
                @endforelse

            <div>
                <a href="{{ route('seguimiento_capacitaciones.index') }}"
                   class="esf-history-btn-primary">
                    Volver al monitoreo
                </a>
            </div>

        </div>
    </div>
</x-app-layout>