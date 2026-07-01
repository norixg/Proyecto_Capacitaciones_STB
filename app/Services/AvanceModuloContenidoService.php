<?php

namespace App\Services;

use App\Models\CapacitacionModulo;
use App\Models\EmpleadoCapacitacion;
use App\Models\EmpleadoModuloAvance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AvanceModuloContenidoService
{
    public function sincronizar(
        EmpleadoCapacitacion $miCapacitacion,
        int|CapacitacionModulo $modulo,
        ?EmpleadoModuloAvance $avanceModulo = null
    ): ?EmpleadoModuloAvance {
        $modulo = $modulo instanceof CapacitacionModulo
            ? $modulo
            : CapacitacionModulo::with(['secciones', 'recursos', 'ejercicios', 'evaluaciones'])
                ->find($modulo);

        if (!$modulo) {
            return $avanceModulo;
        }

        $modulo->loadMissing(['secciones', 'recursos', 'ejercicios', 'evaluaciones']);

        $resumen = $this->calcular($miCapacitacion, $modulo);

        if ($resumen['total_contenido'] <= 0) {
            return $avanceModulo;
        }

        $avanceModulo = $avanceModulo ?: EmpleadoModuloAvance::firstOrNew([
            'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
        ]);

        if (!$avanceModulo->exists && $resumen['progreso'] <= 0) {
            return null;
        }

        $fechaActual = now()->format('Ymd H:i:s');

        if (!$avanceModulo->exists) {
            $avanceModulo->fecha_inicio = $fechaActual;
        }

        $avanceModulo->fecha_ultima_actividad = $fechaActual;
        $avanceModulo->progreso = min(100, max(0, (float) $resumen['progreso']));

        if (($avanceModulo->estado ?? null) !== 'vencido') {
            if ($resumen['progreso'] >= 100) {
                $avanceModulo->estado = $resumen['evaluacion_reprobada_por_intentos']
                    ? 'reprobado'
                    : 'completado';

                $avanceModulo->aprobado = $resumen['evaluacion_reprobada_por_intentos'] ? 0 : 1;
                $avanceModulo->fecha_finalizacion = $fechaActual;
            } elseif ($resumen['progreso'] > 0) {
                $avanceModulo->estado = 'en_proceso';
                $avanceModulo->aprobado = 0;
                $avanceModulo->fecha_finalizacion = null;
            } else {
                $avanceModulo->estado = 'pendiente';
                $avanceModulo->aprobado = 0;
                $avanceModulo->fecha_finalizacion = null;
            }
        }

        $avanceModulo->save();

        return $avanceModulo;
    }

    public function calcular(EmpleadoCapacitacion $miCapacitacion, CapacitacionModulo $modulo): array
    {
        $idsSecciones = collect($modulo->secciones ?? collect())
            ->where('estado', 1)
            ->pluck('id_capacitacion_modulo_seccion')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $idsRecursos = collect($modulo->recursos ?? collect())
            ->where('estado', 1)
            ->pluck('id_capacitacion_recurso')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $ejerciciosActivos = collect($modulo->ejercicios ?? collect())
            ->where('estado', 1)
            ->values();

        $evaluacionesActivas = collect($modulo->evaluaciones ?? collect())
            ->where('activa', 1)
            ->values();

        $totalContenido = $idsSecciones->count()
            + $idsRecursos->count()
            + $ejerciciosActivos->count()
            + $evaluacionesActivas->count();

        if ($totalContenido <= 0) {
            return [
                'total_contenido' => 0,
                'contenido_completado' => 0,
                'progreso' => 0,
                'evaluacion_reprobada_por_intentos' => false,
            ];
        }

        $contenidoCompletado = 0;

        $contenidoCompletado += $this->contarAvanceContenido(
            $miCapacitacion,
            $modulo,
            'seccion',
            'id_capacitacion_modulo_seccion',
            $idsSecciones
        );

        $contenidoCompletado += $this->contarAvanceContenido(
            $miCapacitacion,
            $modulo,
            'recurso',
            'id_capacitacion_recurso',
            $idsRecursos
        );

        $contenidoCompletado += $this->contarEjerciciosCompletados(
            $miCapacitacion,
            $ejerciciosActivos
        );

        $resumenEvaluaciones = $this->contarEvaluacionesFinalizadas(
            $miCapacitacion,
            $evaluacionesActivas
        );

        $contenidoCompletado += $resumenEvaluaciones['completadas'];

        if (
            $evaluacionesActivas->isNotEmpty()
            && $resumenEvaluaciones['completadas'] >= $evaluacionesActivas->count()
        ) {
            $contenidoCompletado = max($contenidoCompletado, $totalContenido);
        }

        $progresoCalculado = round(($contenidoCompletado / $totalContenido) * 100, 2);

        return [
            'total_contenido' => $totalContenido,
            'contenido_completado' => min($contenidoCompletado, $totalContenido),
            'progreso' => min(100, max(0, $progresoCalculado)),
            'evaluacion_reprobada_por_intentos' => $resumenEvaluaciones['reprobada_por_intentos'],
        ];
    }

    private function contarAvanceContenido(
        EmpleadoCapacitacion $miCapacitacion,
        CapacitacionModulo $modulo,
        string $tipo,
        string $columna,
        $ids
    ): int {
        $ids = collect($ids)->filter()->unique()->values();

        if ($ids->isEmpty() || !Schema::hasTable('empleado_contenido_avance')) {
            return 0;
        }

        return DB::table('empleado_contenido_avance')
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
            ->where('tipo_contenido', $tipo)
            ->whereIn($columna, $ids)
            ->count();
    }

    private function contarEjerciciosCompletados(EmpleadoCapacitacion $miCapacitacion, $ejerciciosActivos): int
    {
        $ejerciciosActivos = collect($ejerciciosActivos)->values();
        $idsEjercicios = $ejerciciosActivos
            ->pluck('id_ejercicio')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($idsEjercicios->isEmpty()) {
            return 0;
        }

        $intentos = DB::table('ejercicio_intento')
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->whereIn('id_ejercicio', $idsEjercicios)
            ->get()
            ->groupBy('id_ejercicio');

        $completados = 0;

        foreach ($ejerciciosActivos as $ejercicio) {
            $intentosEjercicio = collect($intentos->get($ejercicio->id_ejercicio, collect()));

            if ($intentosEjercicio->isEmpty()) {
                continue;
            }

            $tieneAprobado = $intentosEjercicio->contains(function ($intento) {
                return in_array($intento->estado, ['finalizado', 'revisado'], true)
                    && (int) ($intento->aprobado ?? 0) === 1;
            });

            if ($tieneAprobado) {
                $completados++;
                continue;
            }

            $tienePendienteRevision = $intentosEjercicio->contains(function ($intento) {
                return $intento->estado === 'pendiente_revision';
            });

            if ($tienePendienteRevision) {
                continue;
            }

            $intentosMaximos = $ejercicio->intentos_maximos
                ? (int) $ejercicio->intentos_maximos
                : null;

            if (!is_null($intentosMaximos) && $intentosEjercicio->count() >= $intentosMaximos) {
                $completados++;
            }
        }

        return $completados;
    }

    private function contarEvaluacionesFinalizadas(EmpleadoCapacitacion $miCapacitacion, $evaluacionesActivas): array
    {
        $evaluacionesActivas = collect($evaluacionesActivas)->values();
        $idsEvaluaciones = $evaluacionesActivas
            ->pluck('id_evaluacion')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($idsEvaluaciones->isEmpty()) {
            return [
                'completadas' => 0,
                'reprobada_por_intentos' => false,
            ];
        }

        $intentos = DB::table('evaluacion_intento')
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->whereIn('id_evaluacion', $idsEvaluaciones)
            ->get()
            ->groupBy('id_evaluacion');

        $completadas = 0;
        $reprobadaPorIntentos = false;

        foreach ($evaluacionesActivas as $evaluacion) {
            $intentosEvaluacion = collect($intentos->get($evaluacion->id_evaluacion, collect()));

            if ($intentosEvaluacion->isEmpty()) {
                continue;
            }

            $aprobada = $intentosEvaluacion->contains(function ($intento) {
                return (int) ($intento->aprobado ?? 0) === 1;
            });

            $tieneIntentoFinalizado = $intentosEvaluacion->contains(function ($intento) {
                return in_array($intento->estado, ['finalizado', 'revisado'], true);
            });

            $tieneIntentoFinalizadoReprobado = $intentosEvaluacion->contains(function ($intento) {
                return in_array($intento->estado, ['finalizado', 'revisado'], true)
                    && (int) ($intento->aprobado ?? 0) === 0;
            });

            $intentosMaximos = $evaluacion->intentos_maximos
                ? (int) $evaluacion->intentos_maximos
                : null;

            $cerradaPorIntentos = !is_null($intentosMaximos)
                && $intentosEvaluacion->count() >= $intentosMaximos
                && !$aprobada;

            if ($aprobada || $cerradaPorIntentos || $tieneIntentoFinalizado) {
                $completadas++;
            }

            if ($cerradaPorIntentos || ($tieneIntentoFinalizadoReprobado && !$aprobada)) {
                $reprobadaPorIntentos = true;
            }
        }

        return [
            'completadas' => $completadas,
            'reprobada_por_intentos' => $reprobadaPorIntentos,
        ];
    }
}