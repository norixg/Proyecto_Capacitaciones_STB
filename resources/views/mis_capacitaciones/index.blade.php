<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Área del usuario
            </p>

            <h2 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100 leading-tight">
                Mis capacitaciones
            </h2>

            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Selecciona una capacitación para continuar con tus módulos, recursos, ejercicios y evaluaciones.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if($sinEmpleadoVinculado)
                <div class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-800 shadow-sm dark:border-amber-500/30 dark:bg-amber-900/30 dark:text-amber-200">
                    Tu usuario no tiene un empleado vinculado todavía. Por eso no se pueden mostrar capacitaciones asignadas.
                </div>
            @else
                <div class="esf-page-card overflow-hidden">
                    <div class="p-6 sm:p-8 border-b border-slate-200/80 dark:border-slate-700/80">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                                    Resumen
                                </p>

                                <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">
                                    Capacitaciones asignadas
                                </h3>

                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-2xl">
                                    Aquí puedes ver las capacitaciones que tienes asignadas. Haz clic en una tarjeta para continuar.
                                </p>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                                @if($misCapacitaciones->count() > 0)
                                    <input type="search"
                                        id="buscadorMisCapacitaciones"
                                        autocomplete="off"
                                        placeholder="Buscar capacitación..."
                                        class="w-full sm:w-72 rounded-full border border-slate-200 bg-white/90 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition placeholder:text-slate-400 focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-blue-900/40">
                                @endif

                                <div class="rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/60 px-4 py-3">
                                    <p class="text-[11px] uppercase tracking-[0.14em] font-black text-blue-500 dark:text-blue-300">
                                        Total asignadas
                                    </p>

                                    <p class="text-2xl font-black text-slate-900 dark:text-slate-100">
                                        {{ $misCapacitaciones->count() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6">
                        @if($misCapacitaciones->count() > 0)
                            <div id="gridMisCapacitaciones" class="esf-training-grid">
                                @foreach($misCapacitaciones as $item)
                                    @php
                                        $capacitacion = $item->capacitacion;
                                        $nombreCapacitacion = $capacitacion?->capacitacion ?? 'Sin nombre';
                                        $codigoCapacitacion = $capacitacion?->codigo ?: 'Sin código';
                                        $estado = $item->estado ?? 'pendiente';
                                        $progreso = max(0, min(100, (float) ($item->progreso ?? 0)));

                                        $estadoTexto = match($estado) {
                                            'pendiente' => 'Pendiente',
                                            'en_proceso' => 'En proceso',
                                            'aprobada' => 'Aprobada',
                                            'reprobada' => 'Reprobada por evaluación',
                                            'vencida' => 'Reprobada por fecha límite',
                                            'cancelada' => 'Cancelada',
                                            default => ucfirst(str_replace('_', ' ', $estado)),
                                        };

                                        $estadoClase = match($estado) {
                                            'pendiente' => 'esf-badge-amber',
                                            'en_proceso' => 'esf-badge-blue',
                                            'aprobada' => 'esf-badge-green',
                                            'reprobada' => 'esf-badge-red',
                                            'vencida' => 'esf-badge-amber',
                                            'cancelada' => 'esf-badge-slate',
                                            default => 'esf-badge-slate',
                                        };

                                        $inicialesCapacitacion = collect(explode(' ', trim($nombreCapacitacion)))
                                            ->filter()
                                            ->take(2)
                                            ->map(fn($parte) => mb_substr($parte, 0, 1))
                                            ->implode('');
                                    @endphp

                                    <article class="esf-training-card" data-mis-capacitaciones-card>
                                        <a href="{{ route('mis_capacitaciones.show', $item->id_empleado_capacitacion) }}"
                                           class="block">
                                            <div class="esf-training-cover">
                                                @if($capacitacion?->ruta_portada)
                                                    <img src="{{ asset('storage/' . $capacitacion->ruta_portada) }}"
                                                         alt="Portada de {{ $nombreCapacitacion }}">
                                                @else
                                                    <div class="esf-training-placeholder">
                                                        <span>{{ $inicialesCapacitacion ?: 'CA' }}</span>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="p-5">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div class="min-w-0">
                                                        <p class="text-xs uppercase tracking-[0.16em] font-black text-slate-400 dark:text-slate-500">
                                                            {{ $codigoCapacitacion }}
                                                        </p>

                                                        <h3 class="mt-2 text-xl font-black text-slate-900 dark:text-slate-100 leading-tight">
                                                            {{ $nombreCapacitacion }}
                                                        </h3>
                                                    </div>

                                                    <span class="esf-badge {{ $estadoClase }}">
                                                        {{ $estadoTexto }}
                                                    </span>
                                                </div>

                                                <div class="mt-5">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <p class="text-sm font-black text-slate-700 dark:text-slate-200">
                                                            Progreso
                                                        </p>

                                                        <p class="text-sm font-black text-slate-900 dark:text-slate-100">
                                                            {{ number_format($progreso, 2) }}%
                                                        </p>
                                                    </div>

                                                    <div class="mt-2 esf-progress-track">
                                                        <div class="esf-progress-fill"
                                                             style="width: {{ $progreso }}%">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <div class="rounded-2xl border border-slate-200 bg-white/70 p-3 dark:border-slate-700 dark:bg-slate-900/60">
                                                        <p class="text-[11px] uppercase tracking-[0.14em] font-black text-slate-400 dark:text-slate-500">
                                                            Nota final
                                                        </p>

                                                        <p class="mt-1 text-lg font-black text-slate-900 dark:text-slate-100">
                                                            {{ is_null($item->nota_final) ? '-' : number_format((float) $item->nota_final, 2) }}
                                                        </p>
                                                    </div>

                                                    <div class="rounded-2xl border border-slate-200 bg-white/70 p-3 dark:border-slate-700 dark:bg-slate-900/60">
                                                        <p class="text-[11px] uppercase tracking-[0.14em] font-black text-slate-400 dark:text-slate-500">
                                                            Estado
                                                        </p>

                                                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-slate-100">
                                                            {{ $estadoTexto }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <div class="rounded-2xl border border-slate-200 bg-white/70 p-3 dark:border-slate-700 dark:bg-slate-900/60">
                                                        <p class="text-[11px] uppercase tracking-[0.14em] font-black text-slate-400 dark:text-slate-500">
                                                            Fecha límite
                                                        </p>

                                                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-slate-100">
                                                            {{ $item->fecha_limite ? $item->fecha_limite->format('d/m/Y') : '-' }}
                                                        </p>
                                                    </div>

                                                    <div class="rounded-2xl border border-slate-200 bg-white/70 p-3 dark:border-slate-700 dark:bg-slate-900/60">
                                                        <p class="text-[11px] uppercase tracking-[0.14em] font-black text-slate-400 dark:text-slate-500">
                                                            Vencimiento
                                                        </p>

                                                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-slate-100">
                                                            {{ $item->fecha_vencimiento ? $item->fecha_vencimiento->format('d/m/Y') : '-' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>

                                        <div class="esf-training-actions justify-end">
                                            <a href="{{ route('mis_capacitaciones.show', $item->id_empleado_capacitacion) }}"
                                               class="esf-action-btn esf-action-edit">
                                                Continuar
                                            </a>

                                            <a href="{{ route('mis_calificaciones.show', $item->id_empleado_capacitacion) }}"
                                               class="esf-action-btn esf-action-status">
                                                Calificaciones
                                            </a>
                                        </div>
                                    </article>
                                @endforeach
                                </div>

                                <div id="sinResultadosMisCapacitaciones"
                                    class="hidden rounded-3xl border border-amber-200 bg-amber-50 px-5 py-8 text-center text-sm font-bold text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">
                                    No se encontraron capacitaciones asignadas con ese criterio de búsqueda.
                                </div>
                            @else
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-10 text-center dark:border-slate-700 dark:bg-slate-900/60">
                                <p class="text-lg font-black text-slate-800 dark:text-slate-100">
                                    No tienes capacitaciones asignadas todavía.
                                </p>

                                <p class="mt-1 text-sm font-bold text-slate-500 dark:text-slate-400">
                                    Cuando se te asigne una capacitación, aparecerá en esta pantalla.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const buscador = document.getElementById('buscadorMisCapacitaciones');
            const tarjetas = document.querySelectorAll('[data-mis-capacitaciones-card]');
            const mensajeVacio = document.getElementById('sinResultadosMisCapacitaciones');

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

            function filtrarMisCapacitaciones() {
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

            buscador.addEventListener('input', filtrarMisCapacitaciones);
        });
        </script>
</x-app-layout>