<?php

namespace App\Services;

use App\Models\EmpleadoCapacitacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Registra en rrhh.asistencia_capacitacion (solo INSERT, tabla ajena a esta
 * app) cada vez que un empleado aprueba una capacitación aquí. El usuario de
 * base de datos de esta app (capacitaciones_app) hoy solo tiene rol
 * db_datareader en la base de RRHH, así que este INSERT fallará con un error
 * de permisos hasta que alguien con acceso a esa base le otorgue INSERT (o
 * db_datawriter) sobre esta tabla. El fallo se registra en el log y nunca
 * interrumpe el flujo del empleado.
 */
class SincronizarAsistenciaRrhhService
{
    public function registrarAprobacion(EmpleadoCapacitacion $miCapacitacion): void
    {
        $miCapacitacion->loadMissing('capacitacion.instructor');

        $idCapacitacionInstructor = $miCapacitacion->capacitacion?->id_capacitacion_instructor;

        if (!$idCapacitacionInstructor) {
            Log::warning('No se sincronizó la asistencia a RRHH: la capacitación no tiene una oferta de RRHH vinculada (id_capacitacion_instructor).', [
                'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
                'id_capacitacion' => $miCapacitacion->id_capacitacion,
            ]);

            return;
        }

        try {
            DB::connection('rrhh')->table('asistencia_capacitacion')->insert([
                'id_empleado' => $miCapacitacion->id_empleado,
                'id_capacitacion_instructor' => $idCapacitacionInstructor,
                'instructor_temporal' => $miCapacitacion->capacitacion?->instructor?->instructor,
                'fecha_recibida' => now()->format('d/m/Y'),
            ]);
        } catch (Throwable $e) {
            Log::error('No se pudo registrar la asistencia en RRHH (asistencia_capacitacion). Probablemente falta el permiso INSERT para capacitaciones_app en db_rrhh_stb.', [
                'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
