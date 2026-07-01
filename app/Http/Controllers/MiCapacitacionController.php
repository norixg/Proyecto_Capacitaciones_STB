<?php

namespace App\Http\Controllers;

use App\Models\EmpleadoCapacitacion;
use App\Models\HistorialCapacitacionEmpleado;
use Illuminate\Support\Facades\Auth;

class MiCapacitacionController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        $empleadoId = optional($usuario->empleadoUser)->id_empleado;

        if (!$empleadoId) {
            return view('mis_capacitaciones.index', [
                'misCapacitaciones' => collect(),
                'sinEmpleadoVinculado' => true,
            ]);
        }

        $this->marcarMisAsignacionesVencidas((int) $empleadoId);

        $misCapacitaciones = EmpleadoCapacitacion::with('capacitacion')
            ->where('id_empleado', $empleadoId)
            ->whereHas('capacitacion', function ($query) {
                $query->where('estado', 1);
            })
            ->orderByDesc('id_empleado_capacitacion')
            ->get();

        return view('mis_capacitaciones.index', [
            'misCapacitaciones' => $misCapacitaciones,
            'sinEmpleadoVinculado' => false,
        ]);
    }

    private function marcarMisAsignacionesVencidas(int $empleadoId): void
    {
        $hoy = now()->startOfDay()->format('Ymd H:i:s');
        $fechaMovimiento = now()->format('Ymd H:i:s');

        $asignacionesVencidas = EmpleadoCapacitacion::query()
            ->where('id_empleado', $empleadoId)
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
            ->get(['id_empleado_capacitacion', 'estado']);

        if ($asignacionesVencidas->isEmpty()) {
            return;
        }

        $historial = $asignacionesVencidas->map(function ($asignacion) use ($fechaMovimiento) {
            return [
                'id_empleado_capacitacion' => $asignacion->id_empleado_capacitacion,
                'estado_anterior' => $asignacion->estado,
                'estado_nuevo' => 'vencida',
                'observacion' => 'Cambio automático por fecha límite vencida desde mis capacitaciones.',
                'fecha_movimiento' => $fechaMovimiento,
                'id_user' => Auth::check() ? Auth::id() : null,
            ];
        })->all();

        HistorialCapacitacionEmpleado::insert($historial);

        EmpleadoCapacitacion::whereIn(
            'id_empleado_capacitacion',
            $asignacionesVencidas->pluck('id_empleado_capacitacion')
        )->update([
            'estado' => 'vencida',
            'aprobado' => 0,
            'fecha_finalizacion' => null,
            'updated_at' => $fechaMovimiento,
        ]);
    }

    public function show($id)
    {
        $usuario = Auth::user();
        $empleadoId = optional($usuario->empleadoUser)->id_empleado;

        $miCapacitacion = EmpleadoCapacitacion::with([
            'capacitacion',
            'capacitacion.capacitacionModulos' => function ($query) {
                $query->where('estado', 1)->orderBy('orden');
            },
            'modulosAvance',
        ])
        ->where('id_empleado_capacitacion', $id)
        ->where('id_empleado', $empleadoId)
        ->whereHas('capacitacion', function ($query) {
            $query->where('estado', 1);
        })
        ->firstOrFail();

        $modulos = $miCapacitacion->capacitacion->capacitacionModulos;

        $primerModulo = $modulos->first();

        if ($primerModulo) {
            return redirect()->route('mis_modulos.show', [
                $miCapacitacion->id_empleado_capacitacion,
                $primerModulo->id_capacitacion_modulo,
            ]);
        }

        $avancesPorModulo = $miCapacitacion->modulosAvance
            ->keyBy('id_capacitacion_modulo');

        return view('mis_capacitaciones.show', compact(
            'miCapacitacion',
            'modulos',
            'avancesPorModulo'
        ));
    }
}