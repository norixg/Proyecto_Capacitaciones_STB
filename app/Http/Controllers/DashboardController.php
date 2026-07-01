<?php

namespace App\Http\Controllers;

use App\Models\Capacitacion;
use App\Models\Empleado;
use App\Models\EmpleadoCapacitacion;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ResumenCapacitacionEmpleadoService;

class DashboardController extends Controller
{
    public function index()
    {
        $usuarioAutenticado = Auth::user();

        /** @var User|null $usuario */
        $usuario = $usuarioAutenticado instanceof User ? $usuarioAutenticado : null;

        $esAdminDashboard = $usuario?->esAdminSistema() === true;
        $esInstructorDashboard = $usuario?->esInstructorSistema() === true && !$esAdminDashboard;
        $instructorActualDashboard = $esInstructorDashboard && $usuario
            ? $usuario->instructorInternoActual()
            : null;

        if (!$esAdminDashboard && !$esInstructorDashboard) {
            return redirect()->route('mis_capacitaciones.index');
        }

        $consultaCapacitaciones = Capacitacion::query();
        $consultaAsignaciones = EmpleadoCapacitacion::query();

        if ($esInstructorDashboard) {
            if ($instructorActualDashboard) {
                $consultaCapacitaciones->where('id_instructor', $instructorActualDashboard->id_instructor);

                $consultaAsignaciones->whereHas('capacitacion', function ($query) use ($instructorActualDashboard) {
                    $query->where('id_instructor', $instructorActualDashboard->id_instructor);
                });
            } else {
                $consultaCapacitaciones->whereRaw('1 = 0');
                $consultaAsignaciones->whereRaw('1 = 0');
            }
        }

        $this->sincronizarAsignacionesDashboard($consultaAsignaciones);

        $totalCapacitaciones = (clone $consultaCapacitaciones)->count();

        $totalUsuarios = $esAdminDashboard
            ? User::count()
            : (clone $consultaAsignaciones)->pluck('id_empleado')->unique()->count();

        $totalUsuariosActivos = $esAdminDashboard ? User::where('estado', 1)->count() : $totalUsuarios;
        $totalUsuariosInactivos = $esAdminDashboard ? User::where('estado', 0)->count() : 0;
        $totalEmpleados = $esAdminDashboard ? Empleado::count() : $totalUsuarios;

        $totalInstructores = $esAdminDashboard
            ? Instructor::count()
            : ($instructorActualDashboard ? 1 : 0);

        $totalAsignaciones = (clone $consultaAsignaciones)->count();
        $totalAsignacionesPendientes = (clone $consultaAsignaciones)->where('estado', 'pendiente')->count();
        $totalAsignacionesEnProceso = (clone $consultaAsignaciones)->where('estado', 'en_proceso')->count();
        $totalAsignacionesAprobadas = (clone $consultaAsignaciones)->where('estado', 'aprobada')->count();
        $totalAsignacionesReprobadas = (clone $consultaAsignaciones)->whereIn('estado', ['reprobada', 'vencida'])->count();

        $totalConAvance = (clone $consultaAsignaciones)
            ->where(function ($query) {
                $query->where('progreso', '>', 0)
                    ->orWhereNotNull('fecha_inicio')
                    ->orWhereHas('modulosAvance')
                    ->orWhereHas('intentosEvaluacion');
            })
            ->count();

        $totalSinAvance = (clone $consultaAsignaciones)
            ->where('progreso', '<=', 0)
            ->whereNull('fecha_inicio')
            ->doesntHave('modulosAvance')
            ->doesntHave('intentosEvaluacion')
            ->count();

        $totalRequeridas = 0;
        $totalFaltantes = 0;
        $totalAsignadasPorNecesidad = 0;
        $totalAprobadasPorNecesidad = 0;

        if ($esAdminDashboard) {
            $baseNecesidades = DB::table('empleado as e')
                ->join('puesto_trabajo_matriz as pt', 'pt.id_puesto_trabajo_matriz', '=', 'e.id_puesto_trabajo_matriz')
                ->join('puestos_capacitacion as pc', function ($join) {
                    $join->on('pc.id_puesto_trabajo_matriz', '=', 'e.id_puesto_trabajo_matriz')
                        ->where('pc.estado', 1);
                })
                ->join('capacitacion as c', function ($join) {
                    $join->on('c.id_capacitacion', '=', 'pc.id_capacitacion')
                        ->where('c.estado', 1);
                })
                ->leftJoin('empleado_capacitacion as ec', function ($join) {
                    $join->on('ec.id_empleado', '=', 'e.id_empleado')
                        ->on('ec.id_capacitacion', '=', 'pc.id_capacitacion');
                })
                ->where('e.estado', 1);

            $totalRequeridas = (clone $baseNecesidades)->count();
            $totalFaltantes = (clone $baseNecesidades)->whereNull('ec.id_empleado_capacitacion')->count();
            $totalAsignadasPorNecesidad = $totalRequeridas - $totalFaltantes;
            $totalAprobadasPorNecesidad = (clone $baseNecesidades)->where('ec.estado', 'aprobada')->count();
        }

        return view('dashboard', compact(
            'usuario',
            'esAdminDashboard',
            'esInstructorDashboard',
            'instructorActualDashboard',
            'totalUsuarios',
            'totalUsuariosActivos',
            'totalUsuariosInactivos',
            'totalEmpleados',
            'totalCapacitaciones',
            'totalInstructores',
            'totalAsignaciones',
            'totalAsignacionesPendientes',
            'totalAsignacionesEnProceso',
            'totalAsignacionesAprobadas',
            'totalAsignacionesReprobadas',
            'totalConAvance',
            'totalSinAvance',
            'totalRequeridas',
            'totalFaltantes',
            'totalAsignadasPorNecesidad',
            'totalAprobadasPorNecesidad'
        ));
    }

    private function sincronizarAsignacionesDashboard($consultaAsignaciones): void
    {
        (clone $consultaAsignaciones)
            ->whereNotIn('estado', ['cancelada'])
            ->orderBy('id_empleado_capacitacion')
            ->chunkById(100, function ($asignaciones) {
                foreach ($asignaciones as $asignacion) {
                    app(ResumenCapacitacionEmpleadoService::class)->recalcular($asignacion);
                }
            }, 'id_empleado_capacitacion');
    }
}