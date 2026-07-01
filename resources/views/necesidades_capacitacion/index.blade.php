<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                    Planificación de capacitaciones
                </p>

                <h2 class="esf-seguimiento-title">
                    Capacitaciones necesarias por empleado
                </h2>

                <p class="esf-seguimiento-subtitle">
                    Revisa qué capacitaciones necesita cada empleado según su puesto.
                </p>
            </div>

            <a href="{{ route('puestos_capacitacion.index') }}"
               class="esf-btn esf-btn-soft">
                ← Ir a matriz por puesto
            </a>
        </div>
    </x-slot>

    <div class="py-8 esf-seguimiento-page">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="mb-5 rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-800 shadow-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="GET"
                  action="{{ route('necesidades_capacitacion.index') }}"
                  class="esf-seguimiento-panel p-5 sm:p-6">
                <div class="esf-seguimiento-filter-grid">
                    <div>
                        <label>Buscar</label>
                        <input
                            type="text"
                            name="buscar"
                            value="{{ $buscar }}"
                            placeholder="Empleado, puesto o capacitación"
                        >
                    </div>

                    <div>
                        <label>Capacitación</label>
                        <select name="id_capacitacion">
                            <option value="">Todas</option>
                            @foreach($capacitaciones as $capacitacion)
                                <option value="{{ $capacitacion->id_capacitacion }}" {{ (string) $idCapacitacion === (string) $capacitacion->id_capacitacion ? 'selected' : '' }}>
                                    {{ $capacitacion->capacitacion }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Departamento</label>
                        <select name="id_departamento">
                            <option value="">Todos</option>
                            @foreach($departamentos as $departamento)
                                <option value="{{ $departamento->id_departamento }}" {{ (string) $idDepartamento === (string) $departamento->id_departamento ? 'selected' : '' }}>
                                    {{ $departamento->departamento }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Puesto</label>
                        <select name="id_puesto_trabajo_matriz">
                            <option value="">Todos</option>
                            @foreach($puestos as $puesto)
                                <option value="{{ $puesto->id_puesto_trabajo_matriz }}" {{ (string) $idPuestoTrabajoMatriz === (string) $puesto->id_puesto_trabajo_matriz ? 'selected' : '' }}>
                                    {{ $puesto->puesto_trabajo_matriz }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Estado actual</label>
                        <select name="estado_necesidad">
                            <option value="">Todos</option>
                            @foreach($estadosNecesidad as $clave => $label)
                                <option value="{{ $clave }}" {{ $estadoNecesidad === $clave ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-start-4 flex items-end justify-end gap-3">
                        <button type="submit" class="esf-btn esf-btn-primary min-w-[110px]">
                            Filtrar
                        </button>

                        <a href="{{ route('necesidades_capacitacion.index') }}"
                           class="esf-btn esf-btn-soft min-w-[110px] text-center">
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>

            <div class="esf-seguimiento-kpi-grid esf-kpi-balanced-4">
                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-slate">
                    <p>Total requeridas</p>
                    <p>{{ $totalRequeridas }}</p>
                </div>

                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-amber">
                    <p>Sin asignar</p>
                    <p>{{ $totalSinAsignar }}</p>
                </div>

                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-blue">
                    <p>Con asignación</p>
                    <p>{{ $totalConAsignacion }}</p>
                </div>

                <div class="esf-seguimiento-kpi esf-seguimiento-kpi-green">
                    <p>Aprobadas</p>
                    <p>{{ $totalAprobadas }}</p>
                </div>
            </div>

            <div class="esf-seguimiento-table-card esf-admin-sheet-card">
                <div class="esf-admin-table-toolbar">
                    <div>
                        <h3 class="esf-admin-table-title">
                            Tabla de necesidades por empleado
                        </h3>

                        <p class="esf-admin-table-subtitle">
                            Resultado filtrado según puesto, departamento, capacitación y estado.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('puestos_capacitacion.generar') }}">
                        @csrf

                        <button
                            type="submit"
                            class="esf-btn esf-btn-green"
                            onclick="return confirm('¿Generar asignaciones automáticas faltantes según la matriz actual?')"
                        >
                            Generar asignaciones automáticas
                        </button>
                    </form>
                </div>

                <div class="esf-seguimiento-table-scroll esf-no-sticky-fields">
                    <table class="esf-seguimiento-table-modern min-w-[1250px]">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Código</th>
                                <th>Puesto</th>
                                <th>Departamento</th>
                                <th>Capacitación requerida</th>
                                <th class="text-center">Estado actual</th>
                                <th class="text-center">Progreso</th>
                                <th class="text-center">Nota final</th>
                                <th class="text-center">Obligatoria</th>
                                <th class="text-center">Días para vencer</th>
                                <th class="text-center">Fecha asignación</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($necesidades as $item)
                                @php
                                    $estadoClase = match($item->estado_necesidad) {
                                        'necesita_asignacion' => 'bg-orange-50 text-orange-700 border border-orange-200',
                                        'pendiente' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                        'en_proceso' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                        'aprobada' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                        'reprobada' => 'bg-red-50 text-red-700 border border-red-200',
                                        'vencida' => 'bg-orange-50 text-orange-700 border border-orange-200',
                                        'cancelada' => 'bg-slate-100 text-slate-700 border border-slate-200',
                                        default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                    };

                                    $estadoTexto = $item->estado_necesidad === 'necesita_asignacion'
                                        ? 'Necesita asignación'
                                        : ucfirst(str_replace('_', ' ', $item->estado_necesidad));

                                    $inicialesEmpleado = collect(preg_split('/\s+/', trim($item->nombre_completo ?? '')))
                                        ->filter()
                                        ->take(2)
                                        ->map(fn ($parteNombre) => mb_strtoupper(mb_substr($parteNombre, 0, 1)))
                                        ->implode('');

                                    if ($inicialesEmpleado === '') {
                                        $inicialesEmpleado = 'NA';
                                    }

                                    $progresoNecesidad = (float) ($item->progreso ?? 0);
                                @endphp

                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3 min-w-[230px]">
                                            <div class="esf-admin-initials">
                                                {{ $inicialesEmpleado }}
                                            </div>

                                            <div>
                                                <div class="font-black text-slate-900 dark:text-slate-100">
                                                    {{ $item->nombre_completo }}
                                                </div>

                                                <div class="text-xs font-semibold text-slate-400">
                                                    Empleado
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>{{ $item->codigo_empleado ?: '-' }}</td>
                                    <td>{{ $item->puesto_trabajo_matriz ?: '-' }}</td>
                                    <td>{{ $item->departamento ?: '-' }}</td>

                                    <td>
                                        <div class="font-black text-slate-900 dark:text-slate-100">
                                            {{ $item->capacitacion }}
                                        </div>

                                        <div class="text-xs font-semibold text-slate-400">
                                            {{ $item->codigo_capacitacion ?: 'Sin código' }}
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-black {{ $estadoClase }}">
                                            {{ $estadoTexto }}
                                        </span>
                                    </td>

                                    <td class="min-w-[160px]">
                                        <div class="esf-seguimiento-progress-cell">
                                            <div class="esf-seguimiento-progress-top">
                                                <span>Avance</span>
                                                <span class="esf-seguimiento-progress-value">
                                                    {{ number_format($progresoNecesidad, 2) }}%
                                                </span>
                                            </div>

                                            <div class="esf-seguimiento-progress-track">
                                                <div class="esf-seguimiento-progress-bar"
                                                     style="width: {{ min(100, max(0, $progresoNecesidad)) }}%;">
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center font-black">
                                        {{ !is_null($item->nota_final) ? number_format((float) $item->nota_final, 2) : '-' }}
                                    </td>

                                    <td class="text-center">
                                        @if((int) $item->obligatoria === 1)
                                            <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700 ring-1 ring-blue-100">
                                                Sí
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600 ring-1 ring-slate-200">
                                                No
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center font-semibold">
                                        {{ !is_null($item->dias_para_vencer) ? (int) $item->dias_para_vencer : '-' }}
                                    </td>

                                    <td class="text-center font-semibold">
                                        {{ $item->fecha_asignacion ? \Illuminate\Support\Carbon::parse($item->fecha_asignacion)->format('d/m/Y') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-10 text-center">
                                        <div class="esf-admin-empty">
                                            <p class="font-black text-slate-800 dark:text-slate-100">
                                                No hay resultados para esos filtros.
                                            </p>

                                            <p class="mt-1 text-sm">
                                                Probá limpiar los filtros o buscar otro empleado.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="px-6 py-5">
                        {{ $necesidades->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>