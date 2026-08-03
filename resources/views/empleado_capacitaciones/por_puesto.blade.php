<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Gestión de asignaciones
            </p>

            <h2 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100 leading-tight">
                Asignar por puesto
            </h2>

            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Basado en la matriz puesto → capacitación de RRHH: elegí un puesto, previsualizá quién falta y asigná en lote.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="esf-alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="esf-alert-error">
                    <strong>Revisa los siguientes errores:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="esf-page-card overflow-visible">
                <div class="p-6 sm:p-8">
                    <form method="GET" action="{{ route('empleado_capacitaciones.por_puesto') }}">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <div class="md:col-span-2">
                                <label for="id_puesto_trabajo_matriz" class="block mb-1 font-medium text-slate-700 dark:text-slate-200">
                                    Puesto de trabajo (matriz RRHH)
                                </label>

                                <x-autocomplete-select
                                    name="id_puesto_trabajo_matriz"
                                    :options="$opcionesPuestos"
                                    :selected="$idPuesto ?: ''"
                                    placeholder="Buscar puesto por nombre o departamento"
                                />
                            </div>

                            <div class="flex gap-3">
                                <button type="submit" class="esf-btn esf-btn-primary">
                                    Previsualizar
                                </button>

                                <a href="{{ route('empleado_capacitaciones.index') }}" class="esf-btn esf-btn-soft">
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if($idPuesto > 0 && !$vistaPrevia)
                <div class="esf-alert-error">
                    No se encontró el puesto seleccionado en la matriz de RRHH.
                </div>
            @endif

            @if($vistaPrevia)
                @php
                    $empleados = $vistaPrevia['empleados'];
                    $capacitaciones = $vistaPrevia['capacitaciones'];
                @endphp

                <div class="esf-page-card overflow-hidden">
                    <div class="p-6 sm:p-8 border-b border-slate-200/80 dark:border-slate-700/80">
                        <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">
                            {{ $vistaPrevia['puesto']['puesto_trabajo_matriz'] }}
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            {{ $vistaPrevia['puesto']['departamento'] ?: 'Sin departamento' }}
                        </p>

                        <div class="flex flex-wrap gap-4 mt-4 text-xs">
                            <span class="inline-flex items-center gap-2">
                                <i class="inline-block w-3 h-3 rounded-full bg-red-500"></i>
                                Sin usuario / no existe en este sistema (no se puede asignar)
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <i class="inline-block w-3 h-3 rounded-full bg-amber-500"></i>
                                Ya recibida este año (según RRHH)
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <i class="inline-block w-3 h-3 rounded-full bg-slate-400"></i>
                                Ya asignada en este sistema
                            </span>
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-3">
                            Pasa el mouse sobre un empleado para ver, en la lista de la derecha, qué capacitaciones ya recibió este año o ya tiene asignadas.
                        </p>
                    </div>

                    @if(empty($empleados))
                        <div class="p-6 text-slate-600 dark:text-slate-300">
                            Este puesto no tiene empleados activos en RRHH.
                        </div>
                    @elseif(empty($capacitaciones))
                        <div class="p-6 text-slate-600 dark:text-slate-300">
                            Este puesto no tiene capacitaciones obligatorias definidas en la matriz de RRHH.
                        </div>
                    @else
                        <form method="POST" action="{{ route('empleado_capacitaciones.por_puesto.store') }}">
                            @csrf
                            <input type="hidden" name="id_puesto_trabajo_matriz" value="{{ $vistaPrevia['puesto']['id_puesto_trabajo_matriz'] }}">

                            <script type="application/json" id="celdas-data">@json($vistaPrevia['celdas'])</script>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 sm:p-8">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-black text-slate-800 dark:text-slate-100">
                                            Empleados en este puesto ({{ count($empleados) }})
                                        </h4>
                                    </div>

                                    <input
                                        type="text"
                                        id="filtro-empleados"
                                        placeholder="Buscar por nombre o código"
                                        class="w-full border rounded px-3 py-2 text-black mb-3"
                                    >

                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <button type="button" id="seleccionar-empleados" class="px-3 py-2 bg-slate-700 text-white rounded text-sm">
                                            Marcar visibles
                                        </button>
                                        <button type="button" id="limpiar-empleados" class="px-3 py-2 bg-gray-500 text-white rounded text-sm">
                                            Limpiar
                                        </button>
                                    </div>

                                    <div class="max-h-[28rem] overflow-y-auto border rounded divide-y dark:divide-slate-700">
                                        @forelse($empleados as $empleado)
                                            @php
                                                $textoBusqueda = mb_strtolower(trim(($empleado['nombre_completo'] ?? '').' '.($empleado['codigo_empleado'] ?? '')));
                                            @endphp

                                            <label
                                                class="empleado-item flex items-start gap-3 p-3 {{ $empleado['tiene_usuario'] ? 'hover:bg-slate-50 dark:hover:bg-slate-800/60' : 'bg-red-50 dark:bg-red-950/30' }}"
                                                data-texto="{{ $textoBusqueda }}"
                                                data-id-empleado="{{ $empleado['id_empleado'] }}"
                                            >
                                                <input
                                                    type="checkbox"
                                                    name="id_empleados[]"
                                                    value="{{ $empleado['id_empleado'] }}"
                                                    class="mt-1 empleado-checkbox"
                                                    {{ $empleado['tiene_usuario'] ? '' : 'disabled' }}
                                                >

                                                <span>
                                                    <span class="block font-medium {{ $empleado['tiene_usuario'] ? 'text-slate-900 dark:text-slate-100' : 'text-red-700 dark:text-red-300' }}">
                                                        {{ $empleado['nombre_completo'] }}
                                                    </span>
                                                    <span class="block text-xs text-slate-500 dark:text-slate-400">
                                                        {{ $empleado['codigo_empleado'] ?: 'Sin código' }}
                                                    </span>
                                                    @if(!$empleado['tiene_usuario'])
                                                        <span class="block text-xs font-semibold text-red-700 dark:text-red-300 mt-1">
                                                            Sin usuario en el sistema
                                                        </span>
                                                    @endif
                                                </span>
                                            </label>
                                        @empty
                                            <p class="p-3 text-slate-600">No hay empleados activos en este puesto.</p>
                                        @endforelse
                                    </div>

                                    @error('id_empleados') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-black text-slate-800 dark:text-slate-100">
                                            Capacitaciones obligatorias ({{ count($capacitaciones) }})
                                        </h4>
                                    </div>

                                    <div class="mb-3 text-xs text-slate-500 dark:text-slate-400" id="detalle-empleado-vacio">
                                        Sin empleado seleccionado para ver el detalle.
                                    </div>

                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <button type="button" id="seleccionar-capacitaciones" class="px-3 py-2 bg-slate-700 text-white rounded text-sm">
                                            Marcar disponibles
                                        </button>
                                        <button type="button" id="limpiar-capacitaciones" class="px-3 py-2 bg-gray-500 text-white rounded text-sm">
                                            Limpiar
                                        </button>
                                    </div>

                                    <div class="max-h-[28rem] overflow-y-auto border rounded divide-y dark:divide-slate-700">
                                        @forelse($capacitaciones as $capacitacion)
                                            <label
                                                class="capacitacion-item flex items-start gap-3 p-3 {{ $capacitacion['existe_local'] ? 'hover:bg-slate-50 dark:hover:bg-slate-800/60' : 'bg-red-50 dark:bg-red-950/30' }}"
                                                data-id-rrhh="{{ $capacitacion['id_capacitacion_rrhh'] }}"
                                            >
                                                <input
                                                    type="checkbox"
                                                    name="id_capacitaciones[]"
                                                    value="{{ $capacitacion['id_capacitacion_local'] }}"
                                                    class="mt-1 capacitacion-checkbox"
                                                    {{ $capacitacion['existe_local'] ? '' : 'disabled' }}
                                                >

                                                <span>
                                                    <span class="block font-medium {{ $capacitacion['existe_local'] ? 'text-slate-900 dark:text-slate-100' : 'text-red-700 dark:text-red-300' }}">
                                                        {{ $capacitacion['nombre'] }}
                                                    </span>
                                                    @if(!$capacitacion['existe_local'])
                                                        <span class="block text-xs font-semibold text-red-700 dark:text-red-300 mt-1">
                                                            No existe en este sistema
                                                        </span>
                                                    @endif
                                                    <span class="badge-detalle hidden block text-xs font-semibold mt-1"></span>
                                                </span>
                                            </label>
                                        @empty
                                            <p class="p-3 text-slate-600">No hay capacitaciones obligatorias para este puesto.</p>
                                        @endforelse
                                    </div>

                                    @error('id_capacitaciones') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="p-6 sm:p-8 border-t border-slate-200/80 dark:border-slate-700/80">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block mb-1 font-medium">Fecha de asignación</label>
                                        <input type="date" name="fecha_asignacion" value="{{ old('fecha_asignacion', now()->format('Y-m-d')) }}" required
                                            class="w-full border rounded px-3 py-2 text-black @error('fecha_asignacion') border-red-500 @enderror">
                                        @error('fecha_asignacion') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block mb-1 font-medium">Fecha límite</label>
                                        <input type="date" name="fecha_limite" value="{{ old('fecha_limite') }}" required
                                            class="w-full border rounded px-3 py-2 text-black @error('fecha_limite') border-red-500 @enderror">
                                        @error('fecha_limite') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block mb-1 font-medium">Fecha de vencimiento</label>
                                        <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" required
                                            class="w-full border rounded px-3 py-2 text-black @error('fecha_vencimiento') border-red-500 @enderror">
                                        @error('fecha_vencimiento') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div class="mt-6 flex gap-3">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                                        Guardar asignaciones
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const celdasElemento = document.getElementById('celdas-data');
            const celdas = celdasElemento ? JSON.parse(celdasElemento.textContent) : {};

            const empleadoItems = document.querySelectorAll('.empleado-item');
            const capacitacionItems = document.querySelectorAll('.capacitacion-item');

            function actualizarDetalle(idEmpleado) {
                const vacio = document.getElementById('detalle-empleado-vacio');

                capacitacionItems.forEach(function (item) {
                    const badge = item.querySelector('.badge-detalle');
                    const idRrhh = item.dataset.idRrhh;
                    const info = idEmpleado ? celdas[idEmpleado + '-' + idRrhh] : null;

                    if (!info || (!info.ya_asignada_local && !info.ya_dada_anio)) {
                        badge.textContent = '';
                        badge.classList.add('hidden');
                        return;
                    }

                    badge.classList.remove('hidden');

                    if (info.ya_asignada_local) {
                        badge.textContent = 'Ya asignada en este sistema';
                        badge.className = 'badge-detalle block text-xs font-semibold mt-1 text-slate-500 dark:text-slate-400';
                    } else {
                        badge.textContent = 'Ya recibida este año (RRHH)';
                        badge.className = 'badge-detalle block text-xs font-semibold mt-1 text-amber-600 dark:text-amber-400';
                    }
                });

                if (vacio) {
                    vacio.classList.toggle('hidden', Boolean(idEmpleado));
                }
            }

            empleadoItems.forEach(function (item) {
                item.addEventListener('mouseenter', function () {
                    actualizarDetalle(item.dataset.idEmpleado);
                });
            });

            const filtro = document.getElementById('filtro-empleados');
            if (filtro) {
                filtro.addEventListener('input', function () {
                    const valor = this.value.toLowerCase().trim();
                    empleadoItems.forEach(function (item) {
                        const texto = item.dataset.texto || '';
                        item.style.display = texto.includes(valor) ? 'flex' : 'none';
                    });
                });
            }

            const seleccionarEmpleados = document.getElementById('seleccionar-empleados');
            if (seleccionarEmpleados) {
                seleccionarEmpleados.addEventListener('click', function () {
                    empleadoItems.forEach(function (item) {
                        if (item.style.display !== 'none') {
                            const checkbox = item.querySelector('.empleado-checkbox');
                            if (checkbox && !checkbox.disabled) {
                                checkbox.checked = true;
                            }
                        }
                    });
                });
            }

            const limpiarEmpleados = document.getElementById('limpiar-empleados');
            if (limpiarEmpleados) {
                limpiarEmpleados.addEventListener('click', function () {
                    document.querySelectorAll('.empleado-checkbox').forEach(function (checkbox) {
                        checkbox.checked = false;
                    });
                });
            }

            const seleccionarCapacitaciones = document.getElementById('seleccionar-capacitaciones');
            if (seleccionarCapacitaciones) {
                seleccionarCapacitaciones.addEventListener('click', function () {
                    document.querySelectorAll('.capacitacion-checkbox').forEach(function (checkbox) {
                        if (!checkbox.disabled) {
                            checkbox.checked = true;
                        }
                    });
                });
            }

            const limpiarCapacitaciones = document.getElementById('limpiar-capacitaciones');
            if (limpiarCapacitaciones) {
                limpiarCapacitaciones.addEventListener('click', function () {
                    document.querySelectorAll('.capacitacion-checkbox').forEach(function (checkbox) {
                        checkbox.checked = false;
                    });
                });
            }
        });
    </script>
</x-app-layout>
