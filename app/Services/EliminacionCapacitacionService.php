<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class EliminacionCapacitacionService
{
    public function eliminarCapacitacion(int $idCapacitacion): void
    {
        DB::transaction(function () use ($idCapacitacion) {
            $idsModulos = $this->pluckIds(
                'capacitacion_modulo',
                'id_capacitacion_modulo',
                'id_capacitacion',
                $idCapacitacion
            );

            $idsEmpleadoCapacitacion = $this->pluckIds(
                'empleado_capacitacion',
                'id_empleado_capacitacion',
                'id_capacitacion',
                $idCapacitacion
            );

            if ($this->columnExists('capacitacion', 'ruta_portada')) {
                $rutaPortada = DB::table('capacitacion')
                    ->where('id_capacitacion', $idCapacitacion)
                    ->value('ruta_portada');

                $this->eliminarArchivoPublico($rutaPortada);
            }

            $this->eliminarSeguimientoDeCapacitacion(
                $idCapacitacion,
                $idsModulos,
                $idsEmpleadoCapacitacion
            );

            $this->eliminarContenidoDeModulos($idsModulos);

            $this->deleteWhere('capacitacion_area', 'id_capacitacion', $idCapacitacion);
            $this->deleteWhere('puestos_capacitacion', 'id_capacitacion', $idCapacitacion);
            $this->deleteWhere('departamentos_capacitacion', 'id_capacitacion', $idCapacitacion);
            $this->deleteWhere('empleados_capacitacion', 'id_capacitacion', $idCapacitacion);

            $this->deleteWhere('capacitacion', 'id_capacitacion', $idCapacitacion);
        });
    }

    public function eliminarAsignacionEmpleado(int $idEmpleadoCapacitacion): void
    {
        DB::transaction(function () use ($idEmpleadoCapacitacion) {
            $idsEmpleadoCapacitacion = collect([$idEmpleadoCapacitacion]);

            $idsIntentosEvaluacion = $this->pluckIdsIn(
                'evaluacion_intento',
                'id_evaluacion_intento',
                'id_empleado_capacitacion',
                $idsEmpleadoCapacitacion
            );

            $idsIntentosEjercicio = $this->pluckIdsIn(
                'ejercicio_intento',
                'id_ejercicio_intento',
                'id_empleado_capacitacion',
                $idsEmpleadoCapacitacion
            );

            $this->deleteWhereIn(
                'evaluacion_intento_respuesta',
                'id_evaluacion_intento',
                $idsIntentosEvaluacion
            );

            $this->deleteWhereIn(
                'ejercicio_intento_respuesta',
                'id_ejercicio_intento',
                $idsIntentosEjercicio
            );

            $this->deleteWhereIn(
                'evaluacion_intento',
                'id_evaluacion_intento',
                $idsIntentosEvaluacion
            );

            $this->deleteWhereIn(
                'ejercicio_intento',
                'id_ejercicio_intento',
                $idsIntentosEjercicio
            );

            $this->limpiarAvanceContenido(
                idsEmpleadoCapacitacion: $idsEmpleadoCapacitacion
            );

            $this->deleteWhereIn(
                'empleado_modulo_avance',
                'id_empleado_capacitacion',
                $idsEmpleadoCapacitacion
            );

            $this->deleteWhereIn(
                'empleado_recurso_avance',
                'id_empleado_capacitacion',
                $idsEmpleadoCapacitacion
            );

            $this->deleteWhereIn(
                'aviso_correo',
                'id_empleado_capacitacion',
                $idsEmpleadoCapacitacion
            );

            $this->deleteWhereIn(
                'historial_capacitacion_empleado',
                'id_empleado_capacitacion',
                $idsEmpleadoCapacitacion
            );

            $this->deleteWhereIn(
                'empleado_capacitacion',
                'id_empleado_capacitacion',
                $idsEmpleadoCapacitacion
            );
        });
    }

    public function eliminarModulo(int $idModulo): void
    {
        DB::transaction(function () use ($idModulo) {
            $idsModulos = collect([$idModulo]);

            $idsEvaluaciones = $this->pluckIdsIn(
                'evaluacion',
                'id_evaluacion',
                'id_capacitacion_modulo',
                $idsModulos
            );

            $idsEjercicios = $this->pluckIdsIn(
                'ejercicio',
                'id_ejercicio',
                'id_capacitacion_modulo',
                $idsModulos
            );

            $idsRecursos = $this->pluckIdsIn(
                'capacitacion_recurso',
                'id_capacitacion_recurso',
                'id_capacitacion_modulo',
                $idsModulos
            );

            $idsSecciones = $this->pluckIdsIn(
                'capacitacion_modulo_seccion',
                'id_capacitacion_modulo_seccion',
                'id_capacitacion_modulo',
                $idsModulos
            );

            $this->limpiarAvanceContenido(
                idsModulos: $idsModulos,
                idsRecursos: $idsRecursos,
                idsEjercicios: $idsEjercicios,
                idsEvaluaciones: $idsEvaluaciones,
                idsSecciones: $idsSecciones
            );

            $this->deleteWhereIn(
                'empleado_modulo_avance',
                'id_capacitacion_modulo',
                $idsModulos
            );

            $this->eliminarEvaluacionesPorIds($idsEvaluaciones);
            $this->eliminarEjerciciosPorIds($idsEjercicios);
            $this->eliminarRecursosPorIds($idsRecursos);

            $this->deleteWhereIn(
                'capacitacion_modulo_seccion',
                'id_capacitacion_modulo',
                $idsModulos
            );

            $this->deleteWhereIn(
                'capacitacion_modulo',
                'id_capacitacion_modulo',
                $idsModulos
            );
        });
    }

    public function eliminarSeccionesModulo(Collection|array $idsSecciones): void
    {
        DB::transaction(function () use ($idsSecciones) {
            $idsSecciones = $this->expandirIdsSeccionesConHijas($idsSecciones);

            if ($idsSecciones->isEmpty()) {
                return;
            }

            $idsEvaluaciones = $this->pluckIdsIn(
                'evaluacion',
                'id_evaluacion',
                'id_capacitacion_modulo_seccion',
                $idsSecciones
            );

            $idsEjercicios = $this->pluckIdsIn(
                'ejercicio',
                'id_ejercicio',
                'id_capacitacion_modulo_seccion',
                $idsSecciones
            );

            $idsRecursos = $this->pluckIdsIn(
                'capacitacion_recurso',
                'id_capacitacion_recurso',
                'id_capacitacion_modulo_seccion',
                $idsSecciones
            );

            $this->limpiarAvanceContenido(
                idsRecursos: $idsRecursos,
                idsEjercicios: $idsEjercicios,
                idsEvaluaciones: $idsEvaluaciones,
                idsSecciones: $idsSecciones
            );

            $this->eliminarEvaluacionesPorIds($idsEvaluaciones);
            $this->eliminarEjerciciosPorIds($idsEjercicios);
            $this->eliminarRecursosPorIds($idsRecursos);

            $this->deleteWhereIn(
                'capacitacion_modulo_seccion',
                'id_capacitacion_modulo_seccion',
                $idsSecciones
            );
        });
    }

    public function eliminarRecurso(int $idRecurso): void
    {
        DB::transaction(function () use ($idRecurso) {
            $this->eliminarRecursosPorIds(collect([$idRecurso]));
        });
    }

    public function eliminarEjercicio(int $idEjercicio): void
    {
        DB::transaction(function () use ($idEjercicio) {
            $this->eliminarEjerciciosPorIds(collect([$idEjercicio]));
        });
    }

    public function eliminarPreguntaEjercicio(int $idPregunta): void
    {
        DB::transaction(function () use ($idPregunta) {
            $this->eliminarPreguntasEjercicioPorIds(collect([$idPregunta]));
        });
    }

    public function eliminarOpcionEjercicio(int $idOpcion): void
    {
        DB::transaction(function () use ($idOpcion) {
            $this->eliminarOpcionesEjercicioPorIds(collect([$idOpcion]));
        });
    }

    public function eliminarEvaluacion(int $idEvaluacion): void
    {
        DB::transaction(function () use ($idEvaluacion) {
            $this->eliminarEvaluacionesPorIds(collect([$idEvaluacion]));
        });
    }

    public function eliminarPreguntaEvaluacion(int $idPregunta): void
    {
        DB::transaction(function () use ($idPregunta) {
            $this->eliminarPreguntasEvaluacionPorIds(collect([$idPregunta]));
        });
    }

    public function eliminarOpcionEvaluacion(int $idOpcion): void
    {
        DB::transaction(function () use ($idOpcion) {
            $this->eliminarOpcionesEvaluacionPorIds(collect([$idOpcion]));
        });
    }

    private function eliminarSeguimientoDeCapacitacion(
        int $idCapacitacion,
        Collection $idsModulos,
        Collection $idsEmpleadoCapacitacion
    ): void {
        $idsEvaluaciones = $this->pluckIdsIn(
            'evaluacion',
            'id_evaluacion',
            'id_capacitacion_modulo',
            $idsModulos
        );

        $idsEjercicios = $this->pluckIdsIn(
            'ejercicio',
            'id_ejercicio',
            'id_capacitacion_modulo',
            $idsModulos
        );

        $idsRecursos = $this->pluckIdsIn(
            'capacitacion_recurso',
            'id_capacitacion_recurso',
            'id_capacitacion_modulo',
            $idsModulos
        );

        $idsSecciones = $this->pluckIdsIn(
            'capacitacion_modulo_seccion',
            'id_capacitacion_modulo_seccion',
            'id_capacitacion_modulo',
            $idsModulos
        );

        $idsIntentosEvaluacion = collect();

        if ($idsEmpleadoCapacitacion->isNotEmpty()) {
            $idsIntentosEvaluacion = $idsIntentosEvaluacion->merge(
                $this->pluckIdsIn(
                    'evaluacion_intento',
                    'id_evaluacion_intento',
                    'id_empleado_capacitacion',
                    $idsEmpleadoCapacitacion
                )
            );
        }

        if ($idsEvaluaciones->isNotEmpty()) {
            $idsIntentosEvaluacion = $idsIntentosEvaluacion->merge(
                $this->pluckIdsIn(
                    'evaluacion_intento',
                    'id_evaluacion_intento',
                    'id_evaluacion',
                    $idsEvaluaciones
                )
            );
        }

        $idsIntentosEvaluacion = $idsIntentosEvaluacion->unique()->values();

        $idsIntentosEjercicio = collect();

        if ($idsEmpleadoCapacitacion->isNotEmpty()) {
            $idsIntentosEjercicio = $idsIntentosEjercicio->merge(
                $this->pluckIdsIn(
                    'ejercicio_intento',
                    'id_ejercicio_intento',
                    'id_empleado_capacitacion',
                    $idsEmpleadoCapacitacion
                )
            );
        }

        if ($idsEjercicios->isNotEmpty()) {
            $idsIntentosEjercicio = $idsIntentosEjercicio->merge(
                $this->pluckIdsIn(
                    'ejercicio_intento',
                    'id_ejercicio_intento',
                    'id_ejercicio',
                    $idsEjercicios
                )
            );
        }

        $idsIntentosEjercicio = $idsIntentosEjercicio->unique()->values();

        $this->deleteWhereIn(
            'evaluacion_intento_respuesta',
            'id_evaluacion_intento',
            $idsIntentosEvaluacion
        );

        $this->deleteWhereIn(
            'ejercicio_intento_respuesta',
            'id_ejercicio_intento',
            $idsIntentosEjercicio
        );

        $this->deleteWhereIn(
            'evaluacion_intento',
            'id_evaluacion_intento',
            $idsIntentosEvaluacion
        );

        $this->deleteWhereIn(
            'ejercicio_intento',
            'id_ejercicio_intento',
            $idsIntentosEjercicio
        );

        $this->limpiarAvanceContenido(
            idsEmpleadoCapacitacion: $idsEmpleadoCapacitacion,
            idsModulos: $idsModulos,
            idsRecursos: $idsRecursos,
            idsEjercicios: $idsEjercicios,
            idsEvaluaciones: $idsEvaluaciones,
            idsSecciones: $idsSecciones
        );

        $this->deleteWhereIn(
            'empleado_modulo_avance',
            'id_empleado_capacitacion',
            $idsEmpleadoCapacitacion
        );

        $this->deleteWhereIn(
            'empleado_modulo_avance',
            'id_capacitacion_modulo',
            $idsModulos
        );

        $this->deleteWhereIn(
            'aviso_correo',
            'id_empleado_capacitacion',
            $idsEmpleadoCapacitacion
        );

        $this->deleteWhereIn(
            'historial_capacitacion_empleado',
            'id_empleado_capacitacion',
            $idsEmpleadoCapacitacion
        );

        $this->deleteWhereIn(
            'empleado_capacitacion',
            'id_empleado_capacitacion',
            $idsEmpleadoCapacitacion
        );
    }

    private function eliminarContenidoDeModulos(Collection $idsModulos): void
    {
        $idsModulos = $this->normalizarIds($idsModulos);

        if ($idsModulos->isEmpty()) {
            return;
        }

        $idsEvaluaciones = $this->pluckIdsIn(
            'evaluacion',
            'id_evaluacion',
            'id_capacitacion_modulo',
            $idsModulos
        );

        $idsEjercicios = $this->pluckIdsIn(
            'ejercicio',
            'id_ejercicio',
            'id_capacitacion_modulo',
            $idsModulos
        );

        $idsRecursos = $this->pluckIdsIn(
            'capacitacion_recurso',
            'id_capacitacion_recurso',
            'id_capacitacion_modulo',
            $idsModulos
        );

        $this->eliminarEvaluacionesPorIds($idsEvaluaciones);
        $this->eliminarEjerciciosPorIds($idsEjercicios);
        $this->eliminarRecursosPorIds($idsRecursos);

        $this->deleteWhereIn(
            'capacitacion_modulo_seccion',
            'id_capacitacion_modulo',
            $idsModulos
        );

        $this->deleteWhereIn(
            'capacitacion_modulo',
            'id_capacitacion_modulo',
            $idsModulos
        );
    }

    private function eliminarEvaluacionesPorIds(Collection $idsEvaluaciones): void
    {
        $idsEvaluaciones = $this->normalizarIds($idsEvaluaciones);

        if ($idsEvaluaciones->isEmpty()) {
            return;
        }

        $idsPreguntas = $this->pluckIdsIn(
            'evaluacion_pregunta',
            'id_evaluacion_pregunta',
            'id_evaluacion',
            $idsEvaluaciones
        );

        $idsOpciones = $this->pluckIdsIn(
            'evaluacion_opcion',
            'id_evaluacion_opcion',
            'id_evaluacion_pregunta',
            $idsPreguntas
        );

        $idsIntentos = $this->pluckIdsIn(
            'evaluacion_intento',
            'id_evaluacion_intento',
            'id_evaluacion',
            $idsEvaluaciones
        );

        $this->deleteWhereIn(
            'evaluacion_intento_respuesta',
            'id_evaluacion_intento',
            $idsIntentos
        );

        $this->deleteWhereIn(
            'evaluacion_intento_respuesta',
            'id_evaluacion_pregunta',
            $idsPreguntas
        );

        $this->deleteWhereIn(
            'evaluacion_intento_respuesta',
            'id_evaluacion_opcion',
            $idsOpciones
        );

        $this->deleteWhereIn(
            'evaluacion_intento',
            'id_evaluacion_intento',
            $idsIntentos
        );

        $this->deleteWhereIn(
            'evaluacion_opcion',
            'id_evaluacion_opcion',
            $idsOpciones
        );

        $this->deleteWhereIn(
            'evaluacion_pregunta',
            'id_evaluacion_pregunta',
            $idsPreguntas
        );

        $this->deleteWhereIn(
            'empleado_contenido_avance',
            'id_evaluacion',
            $idsEvaluaciones
        );

        $this->deleteWhereIn(
            'evaluacion',
            'id_evaluacion',
            $idsEvaluaciones
        );
    }

    private function eliminarEjerciciosPorIds(Collection $idsEjercicios): void
    {
        $idsEjercicios = $this->normalizarIds($idsEjercicios);

        if ($idsEjercicios->isEmpty()) {
            return;
        }

        $idsPreguntas = $this->pluckIdsIn(
            'ejercicio_pregunta',
            'id_ejercicio_pregunta',
            'id_ejercicio',
            $idsEjercicios
        );

        $idsIntentos = $this->pluckIdsIn(
            'ejercicio_intento',
            'id_ejercicio_intento',
            'id_ejercicio',
            $idsEjercicios
        );

        $this->deleteWhereIn(
            'ejercicio_intento_respuesta',
            'id_ejercicio_intento',
            $idsIntentos
        );

        $this->deleteWhereIn(
            'ejercicio_intento_respuesta',
            'id_ejercicio_pregunta',
            $idsPreguntas
        );

        $this->deleteWhereIn(
            'ejercicio_intento',
            'id_ejercicio_intento',
            $idsIntentos
        );

        $this->deleteWhereIn(
            'ejercicio_opcion',
            'id_ejercicio_pregunta',
            $idsPreguntas
        );

        $this->deleteWhereIn(
            'ejercicio_pregunta',
            'id_ejercicio_pregunta',
            $idsPreguntas
        );

        $this->deleteWhereIn(
            'empleado_contenido_avance',
            'id_ejercicio',
            $idsEjercicios
        );

        $this->deleteWhereIn(
            'ejercicio',
            'id_ejercicio',
            $idsEjercicios
        );
    }

    private function eliminarRecursosPorIds(Collection $idsRecursos): void
    {
        $idsRecursos = $this->normalizarIds($idsRecursos);

        if ($idsRecursos->isEmpty()) {
            return;
        }

        if ($this->columnExists('capacitacion_recurso', 'ruta_archivo')) {
            $rutasArchivos = DB::table('capacitacion_recurso')
                ->whereIn('id_capacitacion_recurso', $idsRecursos)
                ->whereNotNull('ruta_archivo')
                ->pluck('ruta_archivo');

            foreach ($rutasArchivos as $rutaArchivo) {
                $this->eliminarArchivoPublico($rutaArchivo);
            }
        }

        $this->deleteWhereIn(
            'empleado_contenido_avance',
            'id_capacitacion_recurso',
            $idsRecursos
        );

        $this->deleteWhereIn(
            'empleado_recurso_avance',
            'id_capacitacion_recurso',
            $idsRecursos
        );

        $this->deleteWhereIn(
            'capacitacion_recurso',
            'id_capacitacion_recurso',
            $idsRecursos
        );
    }

    private function eliminarPreguntasEjercicioPorIds(Collection $idsPreguntas): void
    {
        $idsPreguntas = $this->normalizarIds($idsPreguntas);

        if ($idsPreguntas->isEmpty()) {
            return;
        }

        $this->deleteWhereIn(
            'ejercicio_intento_respuesta',
            'id_ejercicio_pregunta',
            $idsPreguntas
        );

        $this->deleteWhereIn(
            'ejercicio_opcion',
            'id_ejercicio_pregunta',
            $idsPreguntas
        );

        $this->deleteWhereIn(
            'ejercicio_pregunta',
            'id_ejercicio_pregunta',
            $idsPreguntas
        );
    }

    private function eliminarOpcionesEjercicioPorIds(Collection $idsOpciones): void
    {
        $idsOpciones = $this->normalizarIds($idsOpciones);

        if ($idsOpciones->isEmpty()) {
            return;
        }

        $this->deleteWhereIn(
            'ejercicio_opcion',
            'id_ejercicio_opcion',
            $idsOpciones
        );
    }

    private function eliminarPreguntasEvaluacionPorIds(Collection $idsPreguntas): void
    {
        $idsPreguntas = $this->normalizarIds($idsPreguntas);

        if ($idsPreguntas->isEmpty()) {
            return;
        }

        $idsOpciones = $this->pluckIdsIn(
            'evaluacion_opcion',
            'id_evaluacion_opcion',
            'id_evaluacion_pregunta',
            $idsPreguntas
        );

        $this->deleteWhereIn(
            'evaluacion_intento_respuesta',
            'id_evaluacion_pregunta',
            $idsPreguntas
        );

        $this->deleteWhereIn(
            'evaluacion_intento_respuesta',
            'id_evaluacion_opcion',
            $idsOpciones
        );

        $this->deleteWhereIn(
            'evaluacion_opcion',
            'id_evaluacion_opcion',
            $idsOpciones
        );

        $this->deleteWhereIn(
            'evaluacion_pregunta',
            'id_evaluacion_pregunta',
            $idsPreguntas
        );
    }

    private function eliminarOpcionesEvaluacionPorIds(Collection $idsOpciones): void
    {
        $idsOpciones = $this->normalizarIds($idsOpciones);

        if ($idsOpciones->isEmpty()) {
            return;
        }

        $this->deleteWhereIn(
            'evaluacion_intento_respuesta',
            'id_evaluacion_opcion',
            $idsOpciones
        );

        $this->deleteWhereIn(
            'evaluacion_opcion',
            'id_evaluacion_opcion',
            $idsOpciones
        );
    }

    private function expandirIdsSeccionesConHijas(Collection|array|null $idsSecciones): Collection
    {
        $ids = $this->normalizarIds($idsSecciones);

        if ($ids->isEmpty()) {
            return collect();
        }

        do {
            $cantidadAntes = $ids->count();

            $idsHijas = $this->pluckIdsIn(
                'capacitacion_modulo_seccion',
                'id_capacitacion_modulo_seccion',
                'id_seccion_padre',
                $ids
            );

            $ids = $ids
                ->merge($idsHijas)
                ->filter()
                ->unique()
                ->values();
        } while ($ids->count() > $cantidadAntes);

        return $ids;
    }

    private function limpiarAvanceContenido(
        Collection|array|null $idsEmpleadoCapacitacion = null,
        Collection|array|null $idsModulos = null,
        Collection|array|null $idsRecursos = null,
        Collection|array|null $idsEjercicios = null,
        Collection|array|null $idsEvaluaciones = null,
        Collection|array|null $idsSecciones = null
    ): void {
        $this->deleteWhereIn(
            'empleado_contenido_avance',
            'id_empleado_capacitacion',
            $this->normalizarIds($idsEmpleadoCapacitacion)
        );

        $this->deleteWhereIn(
            'empleado_contenido_avance',
            'id_capacitacion_modulo',
            $this->normalizarIds($idsModulos)
        );

        $this->deleteWhereIn(
            'empleado_contenido_avance',
            'id_capacitacion_recurso',
            $this->normalizarIds($idsRecursos)
        );

        $this->deleteWhereIn(
            'empleado_contenido_avance',
            'id_ejercicio',
            $this->normalizarIds($idsEjercicios)
        );

        $this->deleteWhereIn(
            'empleado_contenido_avance',
            'id_evaluacion',
            $this->normalizarIds($idsEvaluaciones)
        );

        $this->deleteWhereIn(
            'empleado_contenido_avance',
            'id_capacitacion_modulo_seccion',
            $this->normalizarIds($idsSecciones)
        );
    }

    private function pluckIds(
        string $table,
        string $selectColumn,
        string $whereColumn,
        mixed $value
    ): Collection {
        if (!$this->columnExists($table, $selectColumn) || !$this->columnExists($table, $whereColumn)) {
            return collect();
        }

        return DB::table($table)
            ->where($whereColumn, $value)
            ->pluck($selectColumn)
            ->filter()
            ->unique()
            ->values();
    }

    private function pluckIdsIn(
        string $table,
        string $selectColumn,
        string $whereColumn,
        Collection|array|null $ids
    ): Collection {
        $ids = $this->normalizarIds($ids);

        if ($ids->isEmpty()) {
            return collect();
        }

        if (!$this->columnExists($table, $selectColumn) || !$this->columnExists($table, $whereColumn)) {
            return collect();
        }

        return DB::table($table)
            ->whereIn($whereColumn, $ids)
            ->pluck($selectColumn)
            ->filter()
            ->unique()
            ->values();
    }

    private function deleteWhere(string $table, string $column, mixed $value): void
    {
        if (!$this->columnExists($table, $column)) {
            return;
        }

        DB::table($table)
            ->where($column, $value)
            ->delete();
    }

    private function deleteWhereIn(
        string $table,
        string $column,
        Collection|array|null $ids
    ): void {
        $ids = $this->normalizarIds($ids);

        if ($ids->isEmpty()) {
            return;
        }

        if (!$this->columnExists($table, $column)) {
            return;
        }

        DB::table($table)
            ->whereIn($column, $ids)
            ->delete();
    }

    private function normalizarIds(Collection|array|null $ids): Collection
    {
        if (is_null($ids)) {
            return collect();
        }

        return collect($ids)
            ->filter(fn ($id) => !is_null($id) && $id !== '')
            ->unique()
            ->values();
    }

    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    private function columnExists(string $table, string $column): bool
    {
        return $this->tableExists($table) && Schema::hasColumn($table, $column);
    }

    private function eliminarArchivoPublico(?string $ruta): void
    {
        if (!$ruta) {
            return;
        }

        if (Storage::disk('public')->exists($ruta)) {
            Storage::disk('public')->delete($ruta);
        }
    }
}