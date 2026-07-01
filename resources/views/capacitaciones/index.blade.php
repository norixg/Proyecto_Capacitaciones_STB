<x-app-layout>

    @php
        $esAdminCapacitaciones = auth()->user()?->esAdminSistema() === true;
        $modoArchivadas = (bool) ($modoArchivadas ?? false);
    @endphp

    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Gestión de capacitaciones
            </p>

            <h2 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100 leading-tight">
                Catálogo de capacitaciones
            </h2>

            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Administra las capacitaciones disponibles. Toca una tarjeta para abrir su constructor.
            </p>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ detalleAbierto: null }">
        <div class="w-full max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="esf-alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="esf-alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="esf-page-card overflow-hidden">
                <div class="p-6 sm:p-8 border-b border-slate-200/80 dark:border-slate-700/80">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                                {{ $modoArchivadas ? 'Capacitaciones archivadas' : 'Capacitaciones creadas' }}
                            </p>

                            <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">
                                {{ $modoArchivadas ? 'Archivo de capacitaciones' : 'Estas son todas las capacitaciones existentes' }}
                            </h3>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-2xl">
                                {{ $modoArchivadas
                                    ? 'Aquí se guardan las capacitaciones archivadas. No aparecen en el catálogo activo ni a los usuarios asignados.'
                                    : 'Cada tarjeta representa una capacitación. Al hacer clic en la portada o información principal, entrarás directamente a esa capacitación.' }}
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/60 px-4 py-3">
                                <p class="text-[11px] uppercase tracking-[0.14em] font-black text-blue-500 dark:text-blue-300">
                                    Total visible
                                </p>

                                <p class="text-2xl font-black text-slate-900 dark:text-slate-100">
                                    {{ count($capacitaciones) }}
                                </p>
                            </div>

                            @if($modoArchivadas)
                                <a href="{{ route('capacitaciones.index') }}"
                                class="esf-btn esf-btn-soft">
                                    Volver al catálogo
                                </a>
                            @else
                                @if($esAdminCapacitaciones)
                                    <a href="{{ route('capacitaciones.archivadas') }}"
                                    class="esf-btn esf-btn-soft">
                                        Ver archivadas
                                    </a>
                                @endif

                                <a href="{{ route('capacitaciones.create') }}"
                                class="esf-btn esf-btn-primary">
                                    + Nueva capacitación
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    @if($capacitaciones->count() > 0)
                        <div class="mb-5 rounded-3xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/60">
                            <label for="buscadorCapacitacionesAdmin"
                                class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                                Buscar capacitación
                            </label>

                            <input type="search"
                                id="buscadorCapacitacionesAdmin"
                                autocomplete="off"
                                placeholder="Buscar por nombre, código, instructor o estado..."
                                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-800 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-blue-900/40">
                        </div>

                        <div id="gridCapacitacionesAdmin" class="esf-training-grid">
                            @foreach($capacitaciones as $capacitacion)
                               @php
                                    if ((int) $capacitacion->estado === 2) {
                                        $estadoTexto = 'Archivada';
                                        $estadoClase = 'esf-badge-slate';
                                    } else {
                                        $estadoTexto = (int) $capacitacion->estado === 1 ? 'Activa' : 'Inactiva';
                                        $estadoClase = (int) $capacitacion->estado === 1 ? 'esf-badge-green' : 'esf-badge-red';
                                    }

                                    $inicialesCapacitacion = collect(explode(' ', trim($capacitacion->capacitacion)))
                                        ->filter()
                                        ->take(2)
                                        ->map(fn($parte) => mb_substr($parte, 0, 1))
                                        ->implode('');
                                @endphp

                                <article class="esf-training-card" data-capacitacion-admin-card>
                                    <a href="{{ $modoArchivadas ? '#' : route('capacitaciones.builder', $capacitacion->id_capacitacion) }}"
                                        class="block"
                                        @if($modoArchivadas) onclick="return false;" @endif>
                                        <div class="esf-training-cover">
                                            @if($capacitacion->ruta_portada)
                                                <img src="{{ asset('storage/' . $capacitacion->ruta_portada) }}"
                                                     alt="Portada de {{ $capacitacion->capacitacion }}">
                                            @else
                                                <div class="esf-training-placeholder">
                                                    <span>{{ $inicialesCapacitacion ?: 'CA' }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="p-5">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <p class="text-xs uppercase tracking-[0.16em] font-black text-slate-400 dark:text-slate-500">
                                                        {{ $capacitacion->codigo ?: 'Sin código' }}
                                                    </p>

                                                    <h3 class="mt-2 text-xl font-black text-slate-900 dark:text-slate-100 leading-tight">
                                                        {{ $capacitacion->capacitacion }}
                                                    </h3>
                                                </div>

                                                <span class="esf-badge {{ $estadoClase }}">
                                                    {{ $estadoTexto }}
                                                </span>
                                            </div>



                                            <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
                                                Instructor:
                                                <span class="font-bold text-slate-700 dark:text-slate-200">
                                                    {{ $capacitacion->instructor?->instructor ?: '-' }}
                                                </span>
                                            </p>


                                        </div>
                                    </a>

                                    <div class="esf-training-actions">
                                        <a href="{{ route('capacitaciones.edit', $capacitacion->id_capacitacion) }}"
                                           class="esf-action-btn esf-action-edit">
                                            Editar
                                        </a>

                                        @if($esAdminCapacitaciones)
                                            @if($modoArchivadas)
                                                <form method="POST"
                                                    action="{{ route('capacitaciones.restaurar_archivada', $capacitacion->id_capacitacion) }}">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit"
                                                            onclick="return confirm('¿Restaurar esta capacitación como inactiva? Luego podrás activarla desde el catálogo.');"
                                                            class="esf-action-btn esf-action-restore">
                                                        Restaurar
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST"
                                                    action="{{ route('capacitaciones.archivar', $capacitacion->id_capacitacion) }}">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit"
                                                            onclick="return confirm('¿Archivar esta capacitación? Dejará de aparecer en el catálogo activo y tampoco será visible para los usuarios asignados.');"
                                                            class="esf-action-btn esf-action-delete">
                                                        Archivar
                                                    </button>
                                                </form>

                                                <form method="POST"
                                                    action="{{ route('capacitaciones.toggleEstado', $capacitacion->id_capacitacion) }}">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit"
                                                            onclick="return confirm('¿Seguro que deseas {{ (int) $capacitacion->estado === 1 ? 'inactivar' : 'activar' }} esta capacitación?')"
                                                            class="esf-action-btn {{ (int) $capacitacion->estado === 1 ? 'esf-action-status' : 'esf-action-restore' }}">
                                                        {{ (int) $capacitacion->estado === 1 ? 'Inactivar' : 'Activar' }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        <button type="button"
                                                @click="detalleAbierto = {{ $capacitacion->id_capacitacion }}"
                                                class="esf-action-btn esf-action-status">
                                            Ver detalles
                                        </button>
                                    </div>
                                </article>

                                <div x-cloak
                                     x-show="detalleAbierto === {{ $capacitacion->id_capacitacion }}"
                                     x-transition
                                     class="esf-modal-backdrop">
                                    <div class="esf-modal-card"
                                         @click.away="detalleAbierto = null">
                                        <div class="p-6 border-b border-slate-200/80 dark:border-slate-700/80 flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                                                    Detalle de capacitación
                                                </p>

                                                <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">
                                                    {{ $capacitacion->capacitacion }}
                                                </h3>
                                            </div>

                                            <button type="button"
                                                    @click="detalleAbierto = null"
                                                    class="esf-icon-button">
                                                ✕
                                            </button>
                                        </div>

                                        <div class="p-6 space-y-3">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div class="esf-kpi-card esf-kpi-blue">
                                                    <p class="text-xs font-black uppercase tracking-[0.12em] text-blue-700 dark:text-blue-300">ID</p>
                                                    <p class="mt-1 font-black text-slate-900 dark:text-slate-100">{{ $capacitacion->id_capacitacion }}</p>
                                                </div>

                                                <div class="esf-kpi-card esf-kpi-slate">
                                                    <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-600 dark:text-slate-300">Código</p>
                                                    <p class="mt-1 font-black text-slate-900 dark:text-slate-100">{{ $capacitacion->codigo ?: '-' }}</p>
                                                </div>

                                                <div class="esf-kpi-card esf-kpi-sky">
                                                    <p class="text-xs font-black uppercase tracking-[0.12em] text-sky-700 dark:text-sky-300">Instructor</p>
                                                    <p class="mt-1 font-black text-slate-900 dark:text-slate-100">{{ $capacitacion->instructor?->instructor ?: '-' }}</p>
                                                </div>

                                                <div class="esf-kpi-card esf-kpi-emerald">
                                                    <p class="text-xs font-black uppercase tracking-[0.12em] text-emerald-700 dark:text-emerald-300">% Aprobación</p>
                                                    <p class="mt-1 font-black text-slate-900 dark:text-slate-100">{{ number_format((float) $capacitacion->porcentaje_aprobacion, 2) }}%</p>
                                                </div>

                                                <div class="esf-kpi-card esf-kpi-amber">
                                                    <p class="text-xs font-black uppercase tracking-[0.12em] text-amber-700 dark:text-amber-300">Vigencia</p>
                                                    <p class="mt-1 font-black text-slate-900 dark:text-slate-100">{{ $capacitacion->dias_vigencia ?: '-' }}</p>
                                                </div>

                                                <div class="esf-kpi-card {{ (int) $capacitacion->estado === 1 ? 'esf-kpi-emerald' : 'esf-kpi-rose' }}">
                                                    <p class="text-xs font-black uppercase tracking-[0.12em] {{ (int) $capacitacion->estado === 1 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">Estado</p>
                                                    <p class="mt-1 font-black text-slate-900 dark:text-slate-100">{{ $estadoTexto }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="px-6 py-5 border-t border-slate-200/80 dark:border-slate-700/80 flex justify-end gap-3">
                                            <button type="button"
                                                    @click="detalleAbierto = null"
                                                    class="esf-btn esf-btn-soft">
                                                Cerrar
                                            </button>

                                            <a href="{{ route('capacitaciones.builder', $capacitacion->id_capacitacion) }}"
                                               class="esf-btn esf-btn-primary">
                                                Abrir capacitación
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div id="sinResultadosCapacitacionesAdmin"
                            class="hidden rounded-3xl border border-amber-200 bg-amber-50 px-5 py-8 text-center text-sm font-bold text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">
                            No se encontraron capacitaciones con ese criterio de búsqueda.
                        </div>
                @else
                        <div class="py-12 text-center">
                            <p class="text-lg font-black text-slate-800 dark:text-slate-100">
                                {{ $modoArchivadas ? 'No hay capacitaciones archivadas.' : 'No hay capacitaciones registradas.' }}
                            </p>

                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $modoArchivadas
                                    ? 'Cuando archives capacitaciones, aparecerán guardadas en esta pantalla.'
                                    : 'Cuando creés capacitaciones, aparecerán como tarjetas en esta pantalla.' }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const buscador = document.getElementById('buscadorCapacitacionesAdmin');
            const tarjetas = document.querySelectorAll('[data-capacitacion-admin-card]');
            const mensajeVacio = document.getElementById('sinResultadosCapacitacionesAdmin');

            if (!buscador || tarjetas.length === 0) {
                return;
            }

            function normalizarTexto(texto) {
                return (texto || '')
                    .toString()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .trim();
            }

            function filtrarCapacitaciones() {
                const valor = normalizarTexto(buscador.value);
                let visibles = 0;

                tarjetas.forEach(function (tarjeta) {
                    const textoTarjeta = normalizarTexto(tarjeta.textContent);
                    const coincide = valor === '' || textoTarjeta.includes(valor);

                    tarjeta.style.display = coincide ? '' : 'none';

                    if (coincide) {
                        visibles++;
                    }
                });

                if (mensajeVacio) {
                    mensajeVacio.classList.toggle('hidden', visibles > 0);
                }
            }

            buscador.addEventListener('input', filtrarCapacitaciones);
        });
        </script>
</x-app-layout>