<?php

namespace App\Services;

use App\Models\Capacitacion;
use App\Models\EmpleadoCapacitacion;
use App\Models\EmpleadoRrhh;
use App\Models\EmpleadoUser;
use App\Models\PuestosCapacitacionRrhh;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AsignacionPorPuestoRrhhService
{
    public function previsualizar(int $idPuestoTrabajoMatriz): array
    {
        $puesto = DB::connection('rrhh')->table('puesto_trabajo_matriz as p')
            ->leftJoin('departamento as d', 'd.id_departamento', '=', 'p.id_departamento')
            ->where('p.id_puesto_trabajo_matriz', $idPuestoTrabajoMatriz)
            ->first(['p.id_puesto_trabajo_matriz', 'p.puesto_trabajo_matriz', 'd.departamento']);

        if (!$puesto) {
            return [
                'puesto' => null,
                'empleados' => [],
                'capacitaciones' => [],
                'celdas' => [],
            ];
        }

        $empleadosRrhh = EmpleadoRrhh::where('id_puesto_trabajo_matriz', $idPuestoTrabajoMatriz)
            ->where('estado', 1)
            ->orderBy('nombre_completo')
            ->get(['id_empleado', 'nombre_completo', 'codigo_empleado']);

        $idsEmpleados = $empleadosRrhh->pluck('id_empleado')->all();

        $idsConUsuario = EmpleadoUser::whereIn('id_empleado', $idsEmpleados)
            ->pluck('id_empleado')
            ->map(fn ($id) => (int) $id)
            ->all();

        $empleados = $empleadosRrhh->map(fn ($empleado) => [
            'id_empleado' => (int) $empleado->id_empleado,
            'nombre_completo' => $empleado->nombre_completo,
            'codigo_empleado' => $empleado->codigo_empleado,
            'tiene_usuario' => in_array((int) $empleado->id_empleado, $idsConUsuario, true),
        ])->values()->all();

        $idsCapacitacionesRrhh = PuestosCapacitacionRrhh::where('id_puesto_trabajo_matriz', $idPuestoTrabajoMatriz)
            ->pluck('id_capacitacion')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $capacitacionesRrhh = DB::connection('rrhh')->table('capacitacion')
            ->whereIn('id_capacitacion', $idsCapacitacionesRrhh)
            ->orderBy('capacitacion')
            ->get(['id_capacitacion', 'capacitacion']);

        // "id_capacitacion_instructor" en la capacitación local no es el id del catálogo
        // rrhh.capacitacion: es el id de una oferta puntual en rrhh.capacitacion_instructor
        // (capacitación + instructor). Hay que pasar por esa tabla puente para saber a
        // qué capacitación del catálogo corresponde realmente cada capacitación local.
        $ofertasPorCatalogo = DB::connection('rrhh')->table('capacitacion_instructor')
            ->whereIn('id_capacitacion', $idsCapacitacionesRrhh)
            ->get(['id_capacitacion_instructor', 'id_capacitacion'])
            ->groupBy('id_capacitacion');

        $idsOfertasRrhh = $ofertasPorCatalogo->flatten(1)
            ->pluck('id_capacitacion_instructor')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $capacitacionesLocalesPorOferta = Capacitacion::whereIn('id_capacitacion_instructor', $idsOfertasRrhh)
            ->where('estado', 1)
            ->get(['id_capacitacion', 'id_capacitacion_instructor'])
            ->keyBy(fn ($capacitacion) => (int) $capacitacion->id_capacitacion_instructor);

        $capacitaciones = $capacitacionesRrhh->map(function ($capacitacion) use ($ofertasPorCatalogo, $capacitacionesLocalesPorOferta) {
            $local = null;

            foreach ($ofertasPorCatalogo->get((int) $capacitacion->id_capacitacion, collect()) as $oferta) {
                $candidato = $capacitacionesLocalesPorOferta->get((int) $oferta->id_capacitacion_instructor);

                if ($candidato) {
                    $local = $candidato;
                    break;
                }
            }

            return [
                'id_capacitacion_rrhh' => (int) $capacitacion->id_capacitacion,
                'nombre' => $capacitacion->capacitacion,
                'existe_local' => (bool) $local,
                'id_capacitacion_local' => $local ? (int) $local->id_capacitacion : null,
            ];
        })->values()->all();

        $idsCapacitacionesLocal = collect($capacitaciones)
            ->pluck('id_capacitacion_local')
            ->filter()
            ->values();

        $asignacionesLocalesExistentes = EmpleadoCapacitacion::whereIn('id_empleado', $idsEmpleados)
            ->whereIn('id_capacitacion', $idsCapacitacionesLocal)
            ->get(['id_empleado', 'id_capacitacion'])
            ->map(fn ($asignacion) => $asignacion->id_empleado.'-'.$asignacion->id_capacitacion)
            ->all();

        $anioActual = (int) now()->year;

        $asistenciasEsteAnio = DB::connection('rrhh')->table('asistencia_capacitacion as ac')
            ->join('capacitacion_instructor as ci', 'ci.id_capacitacion_instructor', '=', 'ac.id_capacitacion_instructor')
            ->whereIn('ac.id_empleado', $idsEmpleados)
            ->whereIn('ci.id_capacitacion', $idsCapacitacionesRrhh)
            ->get(['ac.id_empleado', 'ci.id_capacitacion', 'ac.fecha_recibida'])
            ->filter(fn ($asistencia) => $this->extraerAnio($asistencia->fecha_recibida) === $anioActual)
            ->map(fn ($asistencia) => $asistencia->id_empleado.'-'.$asistencia->id_capacitacion)
            ->unique()
            ->all();

        $celdas = [];

        foreach ($empleados as $empleado) {
            foreach ($capacitaciones as $capacitacion) {
                $claveRrhh = $empleado['id_empleado'].'-'.$capacitacion['id_capacitacion_rrhh'];
                $claveLocal = $capacitacion['id_capacitacion_local']
                    ? $empleado['id_empleado'].'-'.$capacitacion['id_capacitacion_local']
                    : null;

                $celdas[$claveRrhh] = [
                    'ya_dada_anio' => in_array($claveRrhh, $asistenciasEsteAnio, true),
                    'ya_asignada_local' => $claveLocal ? in_array($claveLocal, $asignacionesLocalesExistentes, true) : false,
                ];
            }
        }

        return [
            'puesto' => [
                'id_puesto_trabajo_matriz' => (int) $puesto->id_puesto_trabajo_matriz,
                'puesto_trabajo_matriz' => $puesto->puesto_trabajo_matriz,
                'departamento' => $puesto->departamento,
            ],
            'empleados' => $empleados,
            'capacitaciones' => $capacitaciones,
            'celdas' => $celdas,
        ];
    }

    public function asignar(
        int $idPuestoTrabajoMatriz,
        array $idsEmpleadosSeleccionados,
        array $idsCapacitacionesLocalSeleccionadas,
        array $fechas,
        ?int $idUsuarioAsigno,
        AvisoCorreoService $avisoCorreoService
    ): array {
        $vistaPrevia = $this->previsualizar($idPuestoTrabajoMatriz);

        $resultado = [
            'creadas' => 0,
            'omitidas_ya_existian' => 0,
            'omitidas_sin_usuario' => 0,
            'omitidas_capacitacion_invalida' => 0,
        ];

        if (!$vistaPrevia['puesto']) {
            return $resultado;
        }

        $empleadosPorId = collect($vistaPrevia['empleados'])->keyBy('id_empleado');
        $capacitacionesPorLocal = collect($vistaPrevia['capacitaciones'])
            ->filter(fn ($capacitacion) => $capacitacion['existe_local'])
            ->keyBy('id_capacitacion_local');

        $fechaAsignacion = Carbon::parse($fechas['fecha_asignacion'])->format('Ymd H:i:s');
        $fechaLimite = Carbon::parse($fechas['fecha_limite'])->format('Ymd H:i:s');
        $fechaVencimiento = Carbon::parse($fechas['fecha_vencimiento'])->format('Ymd H:i:s');

        foreach ($idsCapacitacionesLocalSeleccionadas as $idCapacitacionLocal) {
            $idCapacitacionLocal = (int) $idCapacitacionLocal;
            $capacitacion = $capacitacionesPorLocal->get($idCapacitacionLocal);

            if (!$capacitacion) {
                $resultado['omitidas_capacitacion_invalida'] += count($idsEmpleadosSeleccionados);
                continue;
            }

            foreach ($idsEmpleadosSeleccionados as $idEmpleado) {
                $idEmpleado = (int) $idEmpleado;
                $empleado = $empleadosPorId->get($idEmpleado);

                if (!$empleado || !$empleado['tiene_usuario']) {
                    $resultado['omitidas_sin_usuario']++;
                    continue;
                }

                $existe = EmpleadoCapacitacion::where('id_empleado', $idEmpleado)
                    ->where('id_capacitacion', $idCapacitacionLocal)
                    ->exists();

                if ($existe) {
                    $resultado['omitidas_ya_existian']++;
                    continue;
                }

                $asignacionCreada = EmpleadoCapacitacion::create([
                    'id_empleado' => $idEmpleado,
                    'id_capacitacion' => $idCapacitacionLocal,
                    'origen_asignacion' => 'puesto',
                    'id_referencia_asignacion' => $idPuestoTrabajoMatriz,
                    'obligatoria' => 1,
                    'fecha_asignacion' => $fechaAsignacion,
                    'fecha_inicio' => null,
                    'fecha_limite' => $fechaLimite,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'fecha_finalizacion' => null,
                    'estado' => 'pendiente',
                    'progreso' => 0,
                    'nota_final' => null,
                    'aprobado' => 0,
                    'id_usuario_asigno' => $idUsuarioAsigno,
                ]);

                $avisoCorreoService->generarYEnviarAvisoAsignacion($asignacionCreada);

                $resultado['creadas']++;
            }
        }

        return $resultado;
    }

    private function extraerAnio(mixed $fecha): ?int
    {
        $texto = trim((string) $fecha);

        if ($texto === '') {
            return null;
        }

        if (preg_match('/\b(19|20)\d{2}\b/', $texto, $coincidencia)) {
            return (int) $coincidencia[0];
        }

        try {
            return Carbon::parse($texto)->year;
        } catch (\Throwable) {
            return null;
        }
    }
}
