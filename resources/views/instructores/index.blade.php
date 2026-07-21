<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Recursos Humanos
            </p>

            <h2 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100 leading-tight">
                Instructores
            </h2>

            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Datos consultados directamente de la tabla instructor de RR. HH.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="esf-page-card overflow-hidden">
                <div class="p-6 sm:p-8 border-b border-slate-200/80 dark:border-slate-700/80">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                                Lista de instructores
                            </p>

                            <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">
                                Tabla instructor
                            </h3>
                        </div>

                        <input
                            type="search"
                            id="buscadorInstructoresSistema"
                            autocomplete="off"
                            placeholder="Buscar instructor..."
                            class="w-full sm:w-72 rounded-full border border-slate-200 bg-white/90 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition placeholder:text-slate-400 focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-blue-900/40"
                        >
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="esf-table-wrap">
                        <table class="esf-table">
                            <thead>
                                <tr>
                                    <th>id_instructor</th>
                                    <th>instructor</th>
                                    <th>institucion</th>
                                </tr>
                            </thead>

                            <tbody id="listaInstructoresSistema">
                                @forelse($instructores as $instructor)
                                    <tr data-instructor-sistema-row>
                                        <td>{{ $instructor->id_instructor }}</td>
                                        <td>{{ $instructor->instructor }}</td>
                                        <td>{{ $instructor->institucion }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-10 text-center">
                                            No hay registros en la tabla instructor.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const buscador = document.getElementById('buscadorInstructoresSistema');
            const filas = document.querySelectorAll('[data-instructor-sistema-row]');

            if (!buscador) {
                return;
            }

            buscador.addEventListener('input', function () {
                const valor = buscador.value.toLowerCase();

                filas.forEach(function (fila) {
                    fila.hidden = !fila.textContent.toLowerCase().includes(valor);
                });
            });
        });
    </script>
</x-app-layout>
