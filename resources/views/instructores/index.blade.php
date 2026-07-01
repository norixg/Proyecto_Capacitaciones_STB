<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                Gestión de instructores
            </p>

            <h2 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100 leading-tight">
                Instructores registrados
            </h2>

            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Consultá, edita y activa o inactiva los instructores disponibles para las capacitaciones.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
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
                                Lista administrativa
                            </p>

                            <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">
                                Control de instructores
                            </h3>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-2xl">
                                Esta tabla muestra los instructores creados, su información de contacto, tipo, estado y acciones disponibles.
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">


                            <a href="{{ route('instructores.create') }}"
                               class="esf-btn esf-btn-primary">
                                + Nuevo instructor
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="esf-table-wrap">
                        <table class="esf-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Instructor</th>
                                    <th>Correo</th>
                                    <th>Teléfono</th>
                                    <th>Tipo</th>
                                    <th>Empleado vinculado</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($instructores as $instructor)
                                    @php
                                        $inicialesInstructor = collect(explode(' ', trim($instructor->instructor)))
                                            ->filter()
                                            ->take(2)
                                            ->map(fn($parte) => mb_substr($parte, 0, 1))
                                            ->implode('');

                                        $tipoInstructor = (int) $instructor->interno === 1 ? 'Interno' : 'Externo';
                                    @endphp

                                    <tr>
                                        <td>
                                            <span class="font-black text-slate-700 dark:text-slate-200">
                                                {{ $instructor->id_instructor }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="esf-user-avatar">
                                                    {{ $inicialesInstructor ?: 'IS' }}
                                                </div>

                                                <div>
                                                    <p class="font-black text-slate-900 dark:text-slate-100">
                                                        {{ $instructor->instructor }}
                                                    </p>

                                                    <p class="text-xs font-bold text-sky-600 dark:text-sky-300">
                                                        Instructor
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                {{ $instructor->correo ?? 'Sin correo' }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                {{ $instructor->telefono ?? 'Sin teléfono' }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="esf-badge {{ (int) $instructor->interno === 1 ? 'esf-badge-blue' : 'esf-badge-slate' }}">
                                                {{ $tipoInstructor }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                                {{ $instructor->empleado?->nombre_completo ?? 'Sin vínculo' }}
                                            </span>
                                        </td>

                                        <td>
                                            @if((int) $instructor->estado === 1)
                                                <span class="esf-badge esf-badge-green">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="esf-badge esf-badge-red">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="flex flex-wrap justify-center gap-2">
                                                <a href="{{ route('instructores.edit', $instructor->id_instructor) }}"
                                                   class="esf-action-btn esf-action-edit">
                                                    Editar
                                                </a>

                                                <form action="{{ route('instructores.toggleEstado', $instructor->id_instructor) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('¿Seguro que quieres cambiar el estado de este instructor?');">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit"
                                                            class="esf-action-btn {{ (int) $instructor->estado === 1 ? 'esf-action-status' : 'esf-action-restore' }}">
                                                        {{ (int) $instructor->estado === 1 ? 'Inactivar' : 'Activar' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="py-10 text-center">
                                                <p class="text-lg font-black text-slate-800 dark:text-slate-100">
                                                    No hay instructores registrados.
                                                </p>

                                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                    Cuando creés instructores, aparecerán en esta tabla.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <p class="mt-4 text-xs text-slate-400 dark:text-slate-500">
                        Consejo: mantené activos únicamente los instructores que actualmente participan en las capacitaciones.
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>