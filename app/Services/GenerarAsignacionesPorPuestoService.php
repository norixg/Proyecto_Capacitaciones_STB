<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\EmpleadoCapacitacion;
use App\Models\PuestosCapacitacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GenerarAsignacionesPorPuestoService
{
    public function ejecutar(?int $idUsuarioAsigno = null): array
    {
        $relacionesPorPuesto = PuestosCapacitacion::query()
            ->where('estado', 1)
            ->orderBy('id_puesto_trabajo_matriz')
            ->get()
            ->groupBy('id_puesto_trabajo_matriz');

        if ($relacionesPorPuesto->isEmpty()) {
            return [
                'creadas' => 0,
                'omitidas' => 0,
            ];
        }

        $empleados = Empleado::query()
            ->where('estado', 1)
            ->whereNotNull('id_puesto_trabajo_matriz')
            ->orderBy('id_empleado')
            ->get();

        $creadas = 0;
        $omitidas = 0;

        DB::transaction(function () use ($empleados, $relacionesPorPuesto, $idUsuarioAsigno, &$creadas, &$omitidas) {
            foreach ($empleados as $empleado) {
                $relacionesDelPuesto = $relacionesPorPuesto->get((int) $empleado->id_puesto_trabajo_matriz, collect());

                foreach ($relacionesDelPuesto as $relacion) {
                    $existe = EmpleadoCapacitacion::query()
                        ->where('id_empleado', $empleado->id_empleado)
                        ->where('id_capacitacion', $relacion->id_capacitacion)
                        ->exists();

                    if ($existe) {
                        $omitidas++;
                        continue;
                    }

                    $fechaAsignacion = $relacion->fecha_asignacion
                        ? Carbon::parse($relacion->fecha_asignacion)->toDateString()
                        : now()->toDateString();

                    $fechaLimite = !is_null($relacion->dias_para_vencer)
                        ? Carbon::parse($fechaAsignacion)->addDays((int) $relacion->dias_para_vencer)->toDateString()
                        : null;

                    EmpleadoCapacitacion::create([
                        'id_empleado' => $empleado->id_empleado,
                        'id_capacitacion' => $relacion->id_capacitacion,
                        'origen_asignacion' => 'puesto',
                        'id_referencia_asignacion' => $relacion->id_puestos_capacitacion,
                        'obligatoria' => (int) $relacion->obligatoria,
                        'fecha_asignacion' => $fechaAsignacion,
                        'fecha_inicio' => null,
                        'fecha_limite' => $fechaLimite,
                        'fecha_vencimiento' => $fechaLimite,
                        'fecha_finalizacion' => null,
                        'estado' => 'pendiente',
                        'progreso' => 0,
                        'nota_final' => null,
                        'aprobado' => 0,
                        'id_usuario_asigno' => $idUsuarioAsigno,
                    ]);

                    $creadas++;
                }
            }
        });

        return [
            'creadas' => $creadas,
            'omitidas' => $omitidas,
        ];
    }
}