<?php

namespace App\Http\Controllers;

use App\Models\EmpleadoCapacitacion;
use Illuminate\Support\Facades\Auth;
use App\Services\ResumenCapacitacionEmpleadoService;

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

        $misCapacitaciones = EmpleadoCapacitacion::with('capacitacion')
            ->where('id_empleado', $empleadoId)
            ->whereHas('capacitacion', function ($query) {
                $query->where('estado', 1);
            })
            ->orderByDesc('id_empleado_capacitacion')
            ->get();

        $servicioResumen = app(ResumenCapacitacionEmpleadoService::class);

        $misCapacitaciones->each(function ($miCapacitacion) use ($servicioResumen) {
            $servicioResumen->recalcular($miCapacitacion);
            $miCapacitacion->refresh();
            $miCapacitacion->load('capacitacion');
        });

        return view('mis_capacitaciones.index', [
            'misCapacitaciones' => $misCapacitaciones,
            'sinEmpleadoVinculado' => false,
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