<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Planificación de capacitaciones
            </p>

            <h2 class="esf-seguimiento-title">
                Matriz puesto → capacitación
            </h2>

            <p class="esf-seguimiento-subtitle">
                Definí qué capacitaciones necesita cada puesto de trabajo.
            </p>
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

            <div class="esf-seguimiento-panel p-5 sm:p-6">
                <p class="text-sm font-black text-slate-800 dark:text-slate-100">
                    Matriz de capacitación por puesto
                </p>

                <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                    Marcá las capacitaciones requeridas para cada puesto. Luego puedes generar asignaciones faltantes desde la misma matriz.
                </p>
            </div>

            <form method="GET"
                  action="{{ route('puestos_capacitacion.index') }}"
                  class="esf-seguimiento-panel p-5 sm:p-6">
                <div class="esf-seguimiento-filter-grid">
                    <div>
                        <label>Buscar puesto</label>
                        <input
                            type="text"
                            name="buscar_puesto"
                            value="{{ $buscarPuesto }}"
                            placeholder="Nombre del puesto"
                        >
                    </div>

                    <div>
                        <label>Buscar capacitación</label>
                        <input
                            type="text"
                            name="buscar_capacitacion"
                            value="{{ $buscarCapacitacion }}"
                            placeholder="Nombre o código"
                        >
                    </div>

                    <div class="md:col-start-4 flex items-end justify-end gap-3">
                        <button type="submit" class="esf-btn esf-btn-primary min-w-[110px]">
                            Filtrar
                        </button>

                        <a href="{{ route('puestos_capacitacion.index') }}"
                           class="esf-btn esf-btn-soft min-w-[110px] text-center">
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>

            <div class="esf-seguimiento-table-card esf-admin-sheet-card">
                <div class="esf-admin-table-toolbar">
                    <div>
                        <h3 class="esf-admin-table-title">
                            Matriz de capacitaciones y puestos
                        </h3>

                        <p class="esf-admin-table-subtitle">
                            Activá o desactiva la relación entre cada puesto y capacitación.
                        </p>
                    </div>

                    <div class="esf-admin-top-actions">
                        <a href="{{ route('necesidades_capacitacion.index') }}"
                        class="esf-btn esf-btn-soft">
                            Ver necesidades por empleado
                        </a>

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
                </div>

                <form method="POST" action="{{ route('puestos_capacitacion.store') }}">
                    @csrf

                    @foreach($puestos as $puesto)
                        <input type="hidden" name="puesto_ids[]" value="{{ $puesto->id_puesto_trabajo_matriz }}">
                    @endforeach

                    @foreach($capacitaciones as $capacitacion)
                        <input type="hidden" name="cap_ids[]" value="{{ $capacitacion->id_capacitacion }}">
                    @endforeach

                    <div class="esf-seguimiento-table-scroll esf-no-sticky-fields">
                        <table class="esf-seguimiento-table-modern min-w-[1100px]">
                            <thead>
                                <tr>
                                    <th class="min-w-[260px]">
                                        Puesto
                                    </th>

                                    @forelse($capacitaciones as $capacitacion)
                                        <th class="min-w-[220px] text-center">
                                            <div class="font-black text-slate-700 dark:text-slate-100">
                                                {{ $capacitacion->capacitacion }}
                                            </div>

                                            <div class="mt-1 text-xs font-bold text-slate-400">
                                                {{ $capacitacion->codigo ?: 'Sin código' }}
                                            </div>
                                        </th>
                                    @empty
                                        <th class="text-center">
                                            No hay capacitaciones activas.
                                        </th>
                                    @endforelse
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($puestos as $puesto)
                                    <tr>
                                        <td class="min-w-[260px]">
                                            <div class="font-black text-slate-900 dark:text-slate-100">
                                                {{ $puesto->puesto_trabajo_matriz }}
                                            </div>

                                            <div class="mt-1 text-xs font-semibold text-slate-400">
                                                {{ $puesto->departamento?->departamento ?? 'Sin departamento' }}
                                            </div>
                                        </td>

                                        @foreach($capacitaciones as $capacitacion)
                                            @php
                                                $clave = $puesto->id_puesto_trabajo_matriz . '-' . $capacitacion->id_capacitacion;
                                                $registro = $pivot[$clave] ?? null;
                                                $checked = $registro && (int) $registro->estado === 1;
                                            @endphp

                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    name="matrix[{{ $puesto->id_puesto_trabajo_matriz }}][{{ $capacitacion->id_capacitacion }}]"
                                                    value="1"
                                                    {{ $checked ? 'checked' : '' }}
                                                    class="esf-matrix-checkbox"
                                                >
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ max($capacitaciones->count() + 1, 2) }}"
                                            class="px-6 py-10 text-center">
                                            <div class="esf-admin-empty">
                                                <p class="font-black text-slate-800 dark:text-slate-100">
                                                    No hay puestos activos para mostrar.
                                                </p>

                                                <p class="mt-1 text-sm">
                                                    Revisa los filtros o registrá puestos activos.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-200/80 px-6 py-5 dark:border-slate-700/80 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs font-semibold text-slate-400">
                            Guarda la matriz después de marcar o desmarcar capacitaciones.
                        </p>

                        <button type="submit" class="esf-btn esf-btn-primary">
                            Guardar matriz
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>