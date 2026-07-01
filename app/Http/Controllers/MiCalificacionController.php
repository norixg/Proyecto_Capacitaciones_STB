<?php

namespace App\Http\Controllers;

use App\Models\CapacitacionModulo;
use App\Models\EmpleadoCapacitacion;
use App\Models\EmpleadoModuloAvance;
use App\Models\EvaluacionIntento;
use App\Models\EjercicioIntento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MiCalificacionController extends Controller
{
    public function show($id_empleado_capacitacion)
    {
        $empleadoUser = DB::table('empleado_user')
            ->where('id_user', Auth::id())
            ->first();

        if (!$empleadoUser) {
            abort(403, 'Tu usuario no está vinculado a un empleado.');
        }

        $miCapacitacion = EmpleadoCapacitacion::with('capacitacion')
            ->where('id_empleado_capacitacion', $id_empleado_capacitacion)
            ->where('id_empleado', $empleadoUser->id_empleado)
            ->firstOrFail();

        $modulos = CapacitacionModulo::with([
            'evaluaciones' => function ($query) {
                $query->where('activa', 1)
                    ->orderBy('orden')
                    ->orderBy('id_evaluacion');
            },
            'ejercicios' => function ($query) {
                $query->where('estado', 1)
                    ->orderBy('orden')
                    ->orderBy('id_ejercicio');
            },
        ])
            ->where('id_capacitacion', $miCapacitacion->id_capacitacion)
            ->where('estado', 1)
            ->orderBy('orden')
            ->orderBy('id_capacitacion_modulo')
            ->get();

        $avancesModulo = EmpleadoModuloAvance::where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->get()
            ->keyBy('id_capacitacion_modulo');

        $intentosEvaluacion = EvaluacionIntento::where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->orderByDesc('numero_intento')
            ->orderByDesc('id_evaluacion_intento')
            ->get()
            ->groupBy('id_evaluacion');

        $intentosEjercicio = EjercicioIntento::where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->orderByDesc('numero_intento')
            ->orderByDesc('id_ejercicio_intento')
            ->get()
            ->groupBy('id_ejercicio');

        return view('mis_calificaciones.show', compact(
            'miCapacitacion',
            'modulos',
            'avancesModulo',
            'intentosEvaluacion',
            'intentosEjercicio'
        ));
    }
}