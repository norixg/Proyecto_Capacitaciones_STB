<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">Planificación individual</p>
            <h2 class="esf-seguimiento-title">Necesidades por empleado</h2>
            <p class="esf-seguimiento-subtitle">Consulta las capacitaciones obligatorias según el puesto de la matriz de RRHH.</p>
        </div>
    </x-slot>

    <div class="py-8 esf-seguimiento-page">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('necesidades_capacitacion.index') }}" class="esf-seguimiento-panel p-5 sm:p-6">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <label class="mb-1 block">Empleado de RRHH</label>
                        <x-autocomplete-select
                            name="id_empleado"
                            :options="$opcionesEmpleados"
                            :selected="$idEmpleado"
                            placeholder="Escribe nombre, código o identidad"
                        />
                    </div>
                    <div class="lg:col-span-3">
                        <label class="mb-1 block">Capacitación</label>
                        <input type="search" name="cap" value="{{ $filtroCapacitacion }}" list="capacitaciones-rrhh" placeholder="Todas las capacitaciones">
                        <datalist id="capacitaciones-rrhh">
                            @foreach($todasCapacitaciones as $capacitacion)
                                <option value="{{ $capacitacion->capacitacion }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="mb-1 block">Año de asistencia</label>
                        <select name="anio">
                            @foreach($anios as $opcionAnio)
                                <option value="{{ $opcionAnio }}" @selected($anio === $opcionAnio)>{{ $opcionAnio }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2 lg:col-span-2">
                        <button type="submit" class="esf-btn esf-btn-primary">Consultar</button>
                        <a href="{{ route('necesidades_capacitacion.index') }}" class="esf-btn esf-btn-soft">Limpiar</a>
                    </div>
                </div>
            </form>

            @if(!$empleadoSeleccionado)
                <div class="esf-needs-person-empty">
                    <span aria-hidden="true">⌕</span>
                    <h3>Busca un empleado para comenzar</h3>
                    <p>Selecciona una persona de RRHH y presiona “Consultar” para ver las capacitaciones requeridas por su puesto.</p>
                </div>
            @else
                <section class="esf-needs-person-card">
                    <div class="esf-needs-person-avatar">
                        {{ collect(preg_split('/\s+/', $empleadoSeleccionado->nombre_completo))->filter()->take(2)->map(fn($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))->implode('') }}
                    </div>
                    <div class="esf-needs-person-info">
                        <span>Empleado seleccionado</span>
                        <h3>{{ $empleadoSeleccionado->nombre_completo }}</h3>
                        <p>{{ $empleadoSeleccionado->codigo_empleado ?: 'Sin código' }} · {{ $empleadoSeleccionado->identidad ?: 'Sin identidad' }}</p>
                    </div>
                    <div class="esf-needs-person-role">
                        <span>Puesto de la matriz</span>
                        <strong>{{ $puestoSeleccionado?->puesto_trabajo_matriz ?? 'Sin correspondencia' }}</strong>
                        <small>{{ $puestoSeleccionado?->departamento ?? 'Sin departamento' }}</small>
                    </div>
                </section>

                @if($sinCorrespondenciaPuesto)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-bold text-amber-800">
                        El puesto legacy de este empleado no tiene una coincidencia exacta en <code>puesto_trabajo_matriz</code>; por eso no es posible determinar sus capacitaciones obligatorias.
                    </div>
                @else
                    <div class="esf-seguimiento-kpi-grid esf-kpi-balanced-4">
                        <div class="esf-seguimiento-kpi esf-seguimiento-kpi-slate"><p>Necesarias</p><p>{{ $resumen['total'] }}</p></div>
                        <div class="esf-seguimiento-kpi esf-seguimiento-kpi-green"><p>Recibidas en {{ $anio }}</p><p>{{ $resumen['recibidas'] }}</p></div>
                        <div class="esf-seguimiento-kpi esf-seguimiento-kpi-amber"><p>Pendientes</p><p>{{ $resumen['pendientes'] }}</p></div>
                        <div class="esf-seguimiento-kpi {{ $resumen['porcentaje'] >= 80 ? 'esf-seguimiento-kpi-green' : ($resumen['porcentaje'] >= 50 ? 'esf-seguimiento-kpi-amber' : 'esf-seguimiento-kpi-red') }}">
                            <p>Avance individual</p><p>{{ number_format($resumen['porcentaje'], 1) }}%</p>
                        </div>
                    </div>

                    <div class="esf-seguimiento-table-card esf-admin-sheet-card">
                        <div class="esf-admin-table-toolbar">
                            <div>
                                <span class="esf-readonly-badge">Puesto matriz · {{ $anio }}</span>
                                <h3 class="esf-admin-table-title mt-2">Capacitaciones necesarias</h3>
                                <p class="esf-admin-table-subtitle">Una capacitación aparece únicamente cuando está asignada al puesto en <code>puestos_capacitacion</code>.</p>
                            </div>
                            <a href="{{ route('necesidades_capacitacion.exportar', request()->query()) }}" class="esf-btn esf-btn-green">Exportar para Excel</a>
                        </div>
                        <div class="esf-seguimiento-table-scroll esf-no-sticky-fields">
                            <table class="esf-seguimiento-table-modern min-w-[760px]">
                                <thead><tr><th>Capacitación obligatoria</th><th class="text-center">Estado en {{ $anio }}</th><th>Fecha recibida</th></tr></thead>
                                <tbody>
                                    @forelse($necesidades as $necesidad)
                                        <tr>
                                            <td><div class="esf-needs-course-name"><span>C</span><strong>{{ $necesidad['capacitacion'] }}</strong></div></td>
                                            <td class="text-center">
                                                <span class="esf-needs-person-status {{ $necesidad['recibida'] ? 'is-complete' : 'is-pending' }}">
                                                    {{ $necesidad['recibida'] ? '✓ Recibida' : 'Pendiente' }}
                                                </span>
                                            </td>
                                            <td>{{ $necesidad['fecha'] ?: '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3"><div class="esf-admin-empty"><p class="font-black">No hay capacitaciones obligatorias.</p><p class="mt-1 text-sm">El puesto no tiene relaciones para el filtro seleccionado.</p></div></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="esf-readonly-matrix-footer"><span class="esf-readonly-lock">✓</span> Solo lectura desde db_rrhh_stb.</div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
