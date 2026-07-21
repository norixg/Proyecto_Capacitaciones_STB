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
use App\Models\HistorialCapacitacionEmpleado;

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
            ? $usuario->instructorRrhhActual()
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

        $this->marcarAsignacionesVencidasDashboard($consultaAsignaciones);

        $totalCapacitaciones = (clone $consultaCapacitaciones)->count();

        $totalUsuarios = $esAdminDashboard
            ? User::count()
            : (clone $consultaAsignaciones)->distinct()->count('id_empleado');

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

    private function marcarAsignacionesVencidasDashboard($consultaAsignaciones): void
    {
        $hoy = now()->startOfDay()->format('Ymd H:i:s');
        $fechaMovimiento = now()->format('Ymd H:i:s');

        (clone $consultaAsignaciones)
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->where(function ($query) use ($hoy) {
                $query->where(function ($subQuery) use ($hoy) {
                    $subQuery->whereNotNull('fecha_vencimiento')
                        ->where('fecha_vencimiento', '<', $hoy);
                })->orWhere(function ($subQuery) use ($hoy) {
                    $subQuery->whereNull('fecha_vencimiento')
                        ->whereNotNull('fecha_limite')
                        ->where('fecha_limite', '<', $hoy);
                });
            })
            ->orderBy('id_empleado_capacitacion')
            ->chunkById(200, function ($asignaciones) use ($fechaMovimiento) {
                $ids = [];
                $historial = [];

                foreach ($asignaciones as $asignacion) {
                    $ids[] = $asignacion->id_empleado_capacitacion;

                    $historial[] = [
                        'id_empleado_capacitacion' => $asignacion->id_empleado_capacitacion,
                        'estado_anterior' => $asignacion->estado,
                        'estado_nuevo' => 'vencida',
                        'observacion' => 'Cambio automático por fecha límite vencida desde dashboard.',
                        'fecha_movimiento' => $fechaMovimiento,
                        'id_user' => Auth::check() ? Auth::id() : null,
                    ];
                }

                if (!empty($historial)) {
                    HistorialCapacitacionEmpleado::insert($historial);
                }

                if (!empty($ids)) {
                    EmpleadoCapacitacion::whereIn('id_empleado_capacitacion', $ids)
                        ->update([
                            'estado' => 'vencida',
                            'aprobado' => 0,
                            'fecha_finalizacion' => null,
                            'updated_at' => $fechaMovimiento,
                        ]);
                }
            }, 'id_empleado_capacitacion');
    }
}
