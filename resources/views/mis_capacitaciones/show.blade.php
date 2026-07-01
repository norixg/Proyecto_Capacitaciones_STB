<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detalle de mi capacitación
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @php
                $estadoCapacitacionClase = match($miCapacitacion->estado) {
                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                    'en_proceso' => 'bg-blue-100 text-blue-800',
                    'aprobada' => 'bg-green-100 text-green-800',
                    'reprobada' => 'bg-red-100 text-red-800',
                    'vencida' => 'bg-orange-100 text-orange-800',
                    'cancelada' => 'bg-gray-200 text-gray-800',
                    default => 'bg-gray-100 text-gray-800',
                };
            @endphp

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 overflow-x-auto">
                    <div class="flex min-w-max border-b border-gray-200 dark:border-gray-700">

                        <a href="{{ route('mis_capacitaciones.show', $miCapacitacion->id_empleado_capacitacion) }}"
                           class="px-5 py-3 text-sm font-bold border-b-2 border-blue-600 text-blue-700 dark:text-blue-300">
                            Descripción
                        </a>

                        <a href="{{ route('mis_calificaciones.show', $miCapacitacion->id_empleado_capacitacion) }}"
                            class="px-5 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-blue-700 hover:border-blue-300 dark:text-gray-300 dark:hover:text-blue-300">
                            Calificaciones
                        </a>

                        @foreach($modulos as $moduloTab)
                            <a href="{{ route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $moduloTab->id_capacitacion_modulo]) }}"
                               class="px-5 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-blue-700 hover:border-blue-300 dark:text-gray-300 dark:hover:text-blue-300">
                                Módulo {{ $moduloTab->orden }}: {{ $moduloTab->titulo }}
                            </a>
                        @endforeach

                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-2xl font-bold mb-2">
                        {{ $miCapacitacion->capacitacion?->capacitacion }}
                    </h3>

                    <div class="mb-4">
                        <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $estadoCapacitacionClase }}">
                            {{ ucfirst(str_replace('_', ' ', $miCapacitacion->estado ?? 'pendiente')) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p><strong>Progreso:</strong> {{ number_format((float) ($miCapacitacion->progreso ?? 0), 2) }}%</p>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                                <div class="bg-blue-600 h-4 rounded-full" style="width: {{ max(0, min(100, (float) ($miCapacitacion->progreso ?? 0))) }}%"></div>
                            </div>
                        </div>

                        <div>
                            <p><strong>Nota final:</strong> {{ is_null($miCapacitacion->nota_final) ? '-' : number_format((float) $miCapacitacion->nota_final, 2) . '%' }}</p>
                        </div>

                        <div>
                            <p><strong>Fecha límite:</strong> {{ $miCapacitacion->fecha_limite ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h4 class="text-lg font-semibold mb-4">Módulos</h4>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300">
                            <thead class="bg-gray-100 text-black">
                                <tr>
                                    <th class="px-4 py-2 border">Orden</th>
                                    <th class="px-4 py-2 border">Título</th>
                                    <th class="px-4 py-2 border">Duración</th>
                                    <th class="px-4 py-2 border">Requiere evaluación</th>
                                    <th class="px-4 py-2 border">Estado del módulo</th>
                                    <th class="px-4 py-2 border">Progreso</th>
                                    <th class="px-4 py-2 border">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($modulos as $modulo)
                                    @php
                                        $avance = $avancesPorModulo->get($modulo->id_capacitacion_modulo);

                                        $estadoModulo = !$avance
                                            ? 'pendiente'
                                            : ($avance->estado ?? 'pendiente');

                                        $progresoModulo = !$avance
                                            ? 0
                                            : (float) ($avance->progreso ?? 0);

                                            $estadoModuloClase = match($estadoModulo) {
                                            'pendiente' => 'bg-gray-100 text-gray-800',
                                            'en_proceso' => 'bg-blue-100 text-blue-800',
                                            'completado' => 'bg-green-100 text-green-800',
                                            'reprobado' => 'bg-red-100 text-red-800',
                                            'vencido' => 'bg-orange-100 text-orange-800',
                                            default => 'bg-slate-100 text-slate-800',
                                        };
                                    @endphp

                                    <tr class="text-center">
                                        <td class="px-4 py-2 border">{{ $modulo->orden }}</td>
                                        <td class="px-4 py-2 border">{{ $modulo->titulo }}</td>
                                        <td class="px-4 py-2 border">{{ $modulo->duracion_horas ?? '-' }}</td>
                                        <td class="px-4 py-2 border">
                                            {{ (int) $modulo->requiere_evaluacion === 1 ? 'Sí' : 'No' }}
                                        </td>

                                        <td class="px-4 py-2 border">
                                            <span class="px-2 py-1 rounded {{ $estadoModuloClase }}">
                                                {{ ucfirst(str_replace('_', ' ', $estadoModulo)) }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-2 border">
                                            <div class="text-sm font-medium mb-1">
                                                {{ number_format($progresoModulo, 2) }}%
                                            </div>

                                            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                                <div class="bg-blue-600 h-3 rounded-full" style="width: {{ max(0, min(100, $progresoModulo)) }}%"></div>
                                            </div>
                                        </td>

                                        <td class="px-4 py-2 border">
                                            <a href="{{ route('mis_modulos.show', [$miCapacitacion->id_empleado_capacitacion, $modulo->id_capacitacion_modulo]) }}"
                                               class="px-3 py-1 bg-blue-600 text-white rounded">
                                                Abrir módulo
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-4 border text-center">
                                            Esta capacitación no tiene módulos registrados todavía.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('mis_capacitaciones.index') }}"
                           class="px-4 py-2 bg-gray-600 text-white rounded">
                            Volver
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>