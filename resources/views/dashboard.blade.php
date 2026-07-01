<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                {{ $esAdminDashboard ? 'Inicio administrativo' : ($esInstructorDashboard ? 'Panel del instructor' : 'Inicio') }}
            </p>

            <h2 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100 leading-tight">
                Panel de control de capacitaciones
            </h2>

            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                {{ $esAdminDashboard
                    ? 'Resumen general para administrar usuarios, capacitaciones y seguimiento.'
                    : ($esInstructorDashboard
                        ? 'Resumen de tus capacitaciones, participantes y seguimiento.'
                        : 'Acceso a tus capacitaciones asignadas.') }}
            </p>
        </div>
    </x-slot>

    @if($esAdminDashboard || $esInstructorDashboard)
        <div class="py-8">
            <div class="w-full max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8 space-y-7">

                @if($esInstructorDashboard && !$instructorActualDashboard)
                    <div class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-800 shadow-sm">
                        Tu usuario tiene rol de instructor, pero todavía no está vinculado a un empleado interno y a un registro de instructor activo.
                    </div>
                @endif

                <div class="esf-surface overflow-hidden">
                    <div class="p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                                Bienvenido
                            </p>

                            <h3 class="mt-2 text-3xl font-black text-slate-900 dark:text-slate-100">
                                {{ auth()->user()->name }}
                            </h3>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-2xl">
                                @if($esAdminDashboard)
                                    Desde este panel puedes revisar el estado general del sistema, entrar a los módulos principales y dar seguimiento a las capacitaciones asignadas.
                                @else
                                    Desde este panel puedes gestionar tus capacitaciones y revisar el seguimiento de los empleados asignados a ellas.
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('capacitaciones.index') }}"
                               class="esf-btn esf-btn-primary">
                                Ver capacitaciones
                            </a>

                            <a href="{{ route('seguimiento_capacitaciones.index') }}"
                               class="esf-btn esf-btn-soft">
                                Ver seguimiento
                            </a>

                            @if($esAdminDashboard)
                                <a href="{{ route('reportes.index') }}"
                                   class="esf-btn esf-btn-green">
                                    Ver reportes
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <a href="{{ route('capacitaciones.index') }}"
                       class="esf-card-link esf-card-blue">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] font-black text-slate-400 dark:text-slate-500">
                                    Capacitaciones
                                </p>

                                <p class="mt-4 text-4xl font-black text-slate-900 dark:text-slate-100">
                                    {{ $totalCapacitaciones ?? 0 }}
                                </p>

                                <p class="mt-2 text-sm font-bold text-blue-700 dark:text-blue-300">
                                    {{ $esAdminDashboard ? 'Ver capacitaciones creadas' : 'Ver mis capacitaciones como instructor' }}
                                </p>
                            </div>

                            <div class="h-14 w-14 rounded-2xl bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200 flex items-center justify-center text-lg font-black">
                                CA
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('seguimiento_capacitaciones.index') }}"
                       class="esf-card-link esf-card-green">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] font-black text-slate-400 dark:text-slate-500">
                                    Seguimiento
                                </p>

                                <p class="mt-4 text-4xl font-black text-slate-900 dark:text-slate-100">
                                    {{ $totalAsignaciones ?? 0 }}
                                </p>

                                <p class="mt-2 text-sm font-bold text-emerald-700 dark:text-emerald-300">
                                    {{ $esAdminDashboard ? 'Asignaciones registradas' : 'Participantes en mis capacitaciones' }}
                                </p>
                            </div>

                            <div class="h-14 w-14 rounded-2xl bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200 flex items-center justify-center text-lg font-black">
                                SG
                            </div>
                        </div>
                    </a>

                    @if($esAdminDashboard)
                        <a href="{{ url('/instructores') }}"
                           class="esf-card-link esf-card-sky">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.16em] font-black text-slate-400 dark:text-slate-500">
                                        Instructores
                                    </p>

                                    <p class="mt-4 text-4xl font-black text-slate-900 dark:text-slate-100">
                                        {{ $totalInstructores ?? 0 }}
                                    </p>

                                    <p class="mt-2 text-sm font-bold text-sky-700 dark:text-sky-300">
                                        Gestionar instructores
                                    </p>
                                </div>

                                <div class="h-14 w-14 rounded-2xl bg-sky-100 text-sky-800 dark:bg-sky-900/50 dark:text-sky-200 flex items-center justify-center text-lg font-black">
                                    IS
                                </div>
                            </div>
                        </a>
                    @endif
                </div>

                <div class="esf-surface p-6 sm:p-8">
                    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                                Seguimiento de capacitaciones
                            </p>

                            <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-slate-100">
                                Estado de avance
                            </h3>

                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                {{ $esAdminDashboard
                                    ? 'Resumen de avance de empleados según asignaciones registradas.'
                                    : 'Resumen de avance de los empleados asignados a tus capacitaciones.' }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('seguimiento_capacitaciones.index') }}"
                               class="esf-btn esf-btn-primary">
                                Abrir seguimiento
                            </a>

                            @if($esAdminDashboard)
                                <a href="{{ route('reportes.index') }}"
                                   class="esf-btn esf-btn-soft">
                                    Abrir reportes
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                        <div class="esf-kpi-card esf-kpi-amber">
                            <p class="text-xs uppercase tracking-[0.16em] font-black text-amber-700 dark:text-amber-300">
                                Pendientes
                            </p>

                            <p class="mt-3 text-4xl font-black text-slate-900 dark:text-slate-100">
                                {{ $totalAsignacionesPendientes ?? 0 }}
                            </p>
                        </div>

                        <div class="esf-kpi-card esf-kpi-sky">
                            <p class="text-xs uppercase tracking-[0.16em] font-black text-sky-700 dark:text-sky-300">
                                En proceso
                            </p>

                            <p class="mt-3 text-4xl font-black text-slate-900 dark:text-slate-100">
                                {{ $totalAsignacionesEnProceso ?? 0 }}
                            </p>
                        </div>

                        <div class="esf-kpi-card esf-kpi-emerald">
                            <p class="text-xs uppercase tracking-[0.16em] font-black text-emerald-700 dark:text-emerald-300">
                                Aprobadas
                            </p>

                            <p class="mt-3 text-4xl font-black text-slate-900 dark:text-slate-100">
                                {{ $totalAsignacionesAprobadas ?? 0 }}
                            </p>
                        </div>

                        <div class="esf-kpi-card esf-kpi-rose">
                            <p class="text-xs uppercase tracking-[0.16em] font-black text-rose-700 dark:text-rose-300">
                                Reprobadas
                            </p>

                            <p class="mt-3 text-4xl font-black text-slate-900 dark:text-slate-100">
                                {{ $totalAsignacionesReprobadas ?? 0 }}
                            </p>
                        </div>

                        <div class="esf-kpi-card esf-kpi-blue">
                            <p class="text-xs uppercase tracking-[0.16em] font-black text-blue-700 dark:text-blue-300">
                                Han continuado
                            </p>

                            <p class="mt-3 text-4xl font-black text-slate-900 dark:text-slate-100">
                                {{ $totalConAvance ?? 0 }}
                            </p>
                        </div>

                        <div class="esf-kpi-card esf-kpi-slate">
                            <p class="text-xs uppercase tracking-[0.16em] font-black text-slate-600 dark:text-slate-300">
                                No han continuado
                            </p>

                            <p class="mt-3 text-4xl font-black text-slate-900 dark:text-slate-100">
                                {{ $totalSinAvance ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="py-8">
            <div class="w-full max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <div class="esf-surface overflow-hidden">
                    <div class="p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] font-black text-slate-400 dark:text-slate-500">
                                Bienvenido
                            </p>

                            <h3 class="mt-2 text-3xl font-black text-slate-900 dark:text-slate-100">
                                {{ auth()->user()->name }}
                            </h3>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-2xl">
                                Desde aquí puedes revisar tus capacitaciones asignadas, continuar módulos, consultar calificaciones y ver tu avance.
                            </p>
                        </div>

                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <a href="{{ route('mis_capacitaciones.index') }}"
                    class="esf-card-link esf-card-blue">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.16em] font-black text-slate-400 dark:text-slate-500">
                                    Capacitaciones
                                </p>

                                <p class="mt-4 text-4xl font-black text-slate-900 dark:text-slate-100">
                                    →
                                </p>

                                <p class="mt-2 text-sm font-bold text-blue-700 dark:text-blue-300">
                                    Continuar mi aprendizaje
                                </p>
                            </div>

                            <div class="h-14 w-14 rounded-2xl bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200 flex items-center justify-center text-lg font-black">
                                MC
                            </div>
                        </div>
                    </a>
                </div>

            </div>
        </div>
    @endif
</x-app-layout>