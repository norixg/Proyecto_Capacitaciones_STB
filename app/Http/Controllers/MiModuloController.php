<?php

namespace App\Http\Controllers;

use App\Models\CapacitacionModulo;
use App\Models\EmpleadoCapacitacion;
use App\Models\EmpleadoModuloAvance;
use App\Models\EjercicioIntento;
use App\Models\EvaluacionIntento;
use App\Services\ResumenCapacitacionEmpleadoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Services\AvanceModuloContenidoService;

class MiModuloController extends Controller
{

    private static ?bool $existeTablaContenidoAvance = null;

    private static ?bool $existeTablaRecursoAvance = null;

    public function show($id_empleado_capacitacion, $id_capacitacion_modulo)
    {
        $usuario = Auth::user();
        $empleadoId = optional($usuario->empleadoUser)->id_empleado;

        $miCapacitacion = EmpleadoCapacitacion::with('capacitacion')
            ->where('id_empleado_capacitacion', $id_empleado_capacitacion)
            ->where('id_empleado', $empleadoId)
            ->firstOrFail();

        app(ResumenCapacitacionEmpleadoService::class)->recalcular($miCapacitacion);
            $miCapacitacion->refresh();

            $capacitacionFinalizadaParaUsuario = in_array(
                $miCapacitacion->estado,
                ['vencida', 'reprobada', 'aprobada', 'cancelada'],
                true
            );

        $modulo = CapacitacionModulo::with([
            'capacitacion',
            'secciones' => function ($query) {
                $query->where('estado', 1)
                    ->orderBy('id_capacitacion_modulo_seccion');
            },
            'recursos' => function ($query) {
                $query->where('estado', 1)
                    ->orderBy('orden')
                    ->orderBy('id_capacitacion_recurso');
            },
            'ejercicios' => function ($query) {
                $query->where('estado', 1)
                    ->orderBy('orden')
                    ->orderBy('id_ejercicio');
            },
            'evaluaciones' => function ($query) {
                $query->where('activa', 1)
                    ->orderBy('orden')
                    ->orderBy('id_evaluacion');
            }
        ])
            ->where('id_capacitacion_modulo', $id_capacitacion_modulo)
            ->where('id_capacitacion', $miCapacitacion->id_capacitacion)
            ->where('estado', 1)
            ->firstOrFail();

        $modulosCapacitacion = CapacitacionModulo::with([
            'secciones' => function ($query) {
                $query->reorder()
                    ->where('estado', 1)
                    ->orderBy('nivel')
                    ->orderBy('id_seccion_padre')
                    ->orderBy('orden')
                    ->orderBy('id_capacitacion_modulo_seccion');
            },
            'recursos' => function ($query) {
                $query->reorder()
                    ->where('estado', 1)
                    ->orderBy('orden')
                    ->orderBy('id_capacitacion_recurso');
            },
            'ejercicios' => function ($query) {
                $query->reorder()
                    ->where('estado', 1)
                    ->orderBy('orden')
                    ->orderBy('id_ejercicio');
            },
            'evaluaciones' => function ($query) {
                $query->reorder()
                    ->where('activa', 1)
                    ->orderBy('orden')
                    ->orderBy('id_evaluacion');
            },
        ])
            ->where('id_capacitacion', $miCapacitacion->id_capacitacion)
            ->where('estado', 1)
            ->orderBy('orden')
            ->get();

        $evaluacionesUsuario = collect();
        $evaluacion = null;
        $evaluacionesUsuarioPorId = collect();

        $avanceModulo = EmpleadoModuloAvance::where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
            ->first();

        $intentosEjercicioAgrupados = collect();

        $avancesContenidoUsuario = collect();

        if ($this->existeTablaContenidoAvance()) {
            $avancesContenidoUsuario = DB::table('empleado_contenido_avance')
                ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
                ->where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
                ->where('tipo_contenido', '!=', 'evaluacion')
                ->get()
                ->mapWithKeys(function ($avance) {
                    $idContenido = match ($avance->tipo_contenido) {
                        'seccion' => $avance->id_capacitacion_modulo_seccion,
                        'recurso' => $avance->id_capacitacion_recurso,
                        'ejercicio' => $avance->id_ejercicio,
                        default => null,
                    };

                    return [$avance->tipo_contenido . ':' . $idContenido => $avance];
                });
        }

        $recursosAbiertosIds = [];

        if ($this->existeTablaRecursoAvance()) {
            $recursosAbiertosIds = DB::table('empleado_recurso_avance')
                ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
                ->pluck('id_capacitacion_recurso')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        if ($modulo->ejercicios->count() > 0) {
            $intentosEjercicioAgrupados = EjercicioIntento::whereIn(
                    'id_ejercicio',
                    $modulo->ejercicios->pluck('id_ejercicio')
                )
                ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
                ->orderByDesc('numero_intento')
                ->get()
                ->groupBy('id_ejercicio');
        }

        $ejerciciosTotales = $modulo->ejercicios->count();
        $ejerciciosCompletados = 0;
        $ejerciciosObligatoriosTotal = $modulo->ejercicios->where('obligatorio', 1)->count();
        $ejerciciosObligatoriosCompletados = 0;

        foreach ($modulo->ejercicios as $ejercicio) {
            $misIntentos = $intentosEjercicioAgrupados->get($ejercicio->id_ejercicio, collect());
            $ultimoIntento = $misIntentos->first();

            $completadoUsuario = $this->ejercicioCuentaComoCompletado($ejercicio, $misIntentos);

            $ejercicio->mis_intentos = $misIntentos;
            $ejercicio->mi_ultimo_intento = $ultimoIntento;
            $ejercicio->completado_usuario = $completadoUsuario;

            if ($completadoUsuario) {
                $ejerciciosCompletados++;
            }

            if ((int) $ejercicio->obligatorio === 1 && $completadoUsuario) {
                $ejerciciosObligatoriosCompletados++;
            }
        }

        $ejerciciosObligatoriosCompletos = $ejerciciosObligatoriosTotal === 0
            ? true
            : $ejerciciosObligatoriosCompletados >= $ejerciciosObligatoriosTotal;

        $intentos = collect();
        $intentosRealizados = 0;
        $intentosMaximos = null;
        $intentosRestantes = null;
        $ultimoIntento = null;
        $aprobadoEvaluacion = false;
        $maximoIntentosAlcanzado = false;
        $puedePresentarEvaluacion = false;
        $puedeVerRevisionEvaluacionUsuario = false;

        if ($modulo->evaluaciones->count() > 0) {
            $evaluacionesUsuario = $modulo->evaluaciones->map(function ($evaluacionItem) use ($miCapacitacion, $ejerciciosObligatoriosCompletos) {
                $intentosEvaluacion = EvaluacionIntento::where('id_evaluacion', $evaluacionItem->id_evaluacion)
                    ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
                    ->orderByDesc('numero_intento')
                    ->get();

                $intentosRealizadosEvaluacion = $intentosEvaluacion->count();
                $ultimoIntentoEvaluacion = $intentosEvaluacion->first();
                $aprobadoEvaluacionUsuario = $intentosEvaluacion->where('aprobado', 1)->isNotEmpty();

                $intentosMaximosEvaluacion = $evaluacionItem->intentos_maximos
                    ? (int) $evaluacionItem->intentos_maximos
                    : null;

                $maximoIntentosAlcanzadoEvaluacion = !is_null($intentosMaximosEvaluacion)
                    ? $intentosRealizadosEvaluacion >= $intentosMaximosEvaluacion
                    : false;

                $intentosRestantesEvaluacion = is_null($intentosMaximosEvaluacion)
                    ? null
                    : max($intentosMaximosEvaluacion - $intentosRealizadosEvaluacion, 0);

                $puedePresentarEvaluacionUsuario = !$maximoIntentosAlcanzadoEvaluacion;

                $puedeVerRevisionUsuario = $this->puedeUsuarioVerRevision(
                    $intentosRealizadosEvaluacion,
                    $intentosMaximosEvaluacion
                );

                return [
                    'evaluacion' => $evaluacionItem,
                    'intentos' => $intentosEvaluacion,
                    'intentos_realizados' => $intentosRealizadosEvaluacion,
                    'intentos_maximos' => $intentosMaximosEvaluacion,
                    'intentos_restantes' => $intentosRestantesEvaluacion,
                    'ultimo_intento' => $ultimoIntentoEvaluacion,
                    'aprobado' => $aprobadoEvaluacionUsuario,
                    'maximo_intentos_alcanzado' => $maximoIntentosAlcanzadoEvaluacion,
                    'puede_presentar' => $puedePresentarEvaluacionUsuario,
                    'puede_ver_revision' => $puedeVerRevisionUsuario,
                ];
            });

            $primeraEvaluacionData = $evaluacionesUsuario->first();

            if ($primeraEvaluacionData) {
                $evaluacion = $primeraEvaluacionData['evaluacion'];
                $intentos = $primeraEvaluacionData['intentos'];
                $intentosRealizados = $primeraEvaluacionData['intentos_realizados'];
                $intentosMaximos = $primeraEvaluacionData['intentos_maximos'];
                $intentosRestantes = $primeraEvaluacionData['intentos_restantes'];
                $ultimoIntento = $primeraEvaluacionData['ultimo_intento'];
                $aprobadoEvaluacion = $primeraEvaluacionData['aprobado'];
                $maximoIntentosAlcanzado = $primeraEvaluacionData['maximo_intentos_alcanzado'];
                $puedePresentarEvaluacion = $primeraEvaluacionData['puede_presentar'];
                $puedeVerRevisionEvaluacionUsuario = $primeraEvaluacionData['puede_ver_revision'];
            }

            $evaluacionesUsuarioPorId = $evaluacionesUsuario->keyBy(function ($item) {
                return $item['evaluacion']->id_evaluacion;
            });
        }

        $evaluacionesFinalizadasUsuarioIds = collect();

        $idsEvaluacionesCapacitacion = $modulosCapacitacion
            ->flatMap(function ($moduloMenu) {
                return $moduloMenu->evaluaciones ?? collect();
            })
            ->pluck('id_evaluacion')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($idsEvaluacionesCapacitacion->isNotEmpty()) {
            $intentosEvaluacionSidebar = EvaluacionIntento::whereIn('id_evaluacion', $idsEvaluacionesCapacitacion)
                ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
                ->get()
                ->groupBy('id_evaluacion');

            $evaluacionesFinalizadasUsuarioIds = $modulosCapacitacion
                ->flatMap(function ($moduloMenu) {
                    return $moduloMenu->evaluaciones ?? collect();
                })
                ->filter(function ($evaluacionSidebar) use ($intentosEvaluacionSidebar) {
                    $intentosSidebar = collect($intentosEvaluacionSidebar->get($evaluacionSidebar->id_evaluacion, collect()));

                    if ($intentosSidebar->isEmpty()) {
                        return false;
                    }

                    $aprobadaSidebar = $intentosSidebar->contains(function ($intentoSidebar) {
                        return (int) ($intentoSidebar->aprobado ?? 0) === 1;
                    });

                    $intentosMaximosSidebar = $evaluacionSidebar->intentos_maximos
                        ? (int) $evaluacionSidebar->intentos_maximos
                        : null;

                    $cerradaPorIntentosSidebar = !is_null($intentosMaximosSidebar)
                        && $intentosSidebar->count() >= $intentosMaximosSidebar
                        && !$aprobadaSidebar;

                    return $aprobadaSidebar || $cerradaPorIntentosSidebar;
                })
                ->pluck('id_evaluacion')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();
        }

        if (!$capacitacionFinalizadaParaUsuario) {
            $avanceModulo = app(AvanceModuloContenidoService::class)->sincronizar(
                $miCapacitacion,
                $modulo,
                $avanceModulo
            ) ?? $avanceModulo;
        }

        app(ResumenCapacitacionEmpleadoService::class)->recalcular($miCapacitacion);
        $miCapacitacion->refresh();

        $capacitacionFinalizadaParaUsuario = in_array(
            $miCapacitacion->estado,
            ['vencida', 'reprobada', 'aprobada', 'cancelada'],
            true
        );

        return view('mis_modulos.show', compact(
            'miCapacitacion',
            'modulo',
            'modulosCapacitacion',
            'evaluacion',
            'evaluacionesUsuario',
            'avanceModulo',
            'intentos',
            'intentosRealizados',
            'intentosMaximos',
            'intentosRestantes',
            'ultimoIntento',
            'aprobadoEvaluacion',
            'maximoIntentosAlcanzado',
            'puedePresentarEvaluacion',
            'ejerciciosTotales',
            'ejerciciosCompletados',
            'ejerciciosObligatoriosTotal',
            'ejerciciosObligatoriosCompletados',
            'ejerciciosObligatoriosCompletos',
            'puedeVerRevisionEvaluacionUsuario',
            'avancesContenidoUsuario',
            'recursosAbiertosIds',
            'evaluacionesUsuarioPorId',
            'evaluacionesFinalizadasUsuarioIds',
            'capacitacionFinalizadaParaUsuario'
        ));
    }

    private function existeTablaContenidoAvance(): bool
    {
        if (self::$existeTablaContenidoAvance === null) {
            self::$existeTablaContenidoAvance = Schema::hasTable('empleado_contenido_avance');
        }

        return self::$existeTablaContenidoAvance;
    }

    private function existeTablaRecursoAvance(): bool
    {
        if (self::$existeTablaRecursoAvance === null) {
            self::$existeTablaRecursoAvance = Schema::hasTable('empleado_recurso_avance');
        }

        return self::$existeTablaRecursoAvance;
    }

    public function registrarAvanceContenido(Request $request, $id_empleado_capacitacion, $id_capacitacion_modulo)
    {
        $usuario = Auth::user();
        $empleadoId = optional($usuario->empleadoUser)->id_empleado;

        $miCapacitacion = EmpleadoCapacitacion::where('id_empleado_capacitacion', $id_empleado_capacitacion)
            ->where('id_empleado', $empleadoId)
            ->firstOrFail();

        app(ResumenCapacitacionEmpleadoService::class)->recalcular($miCapacitacion);
            $miCapacitacion->refresh();

            if (in_array($miCapacitacion->estado, ['vencida', 'reprobada', 'aprobada', 'cancelada'], true)) {
                $avanceModuloActual = EmpleadoModuloAvance::where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
                    ->where('id_capacitacion_modulo', $id_capacitacion_modulo)
                    ->first();

                return response()->json([
                    'ok' => true,
                    'bloqueada' => true,
                    'progreso_modulo' => (float) ($avanceModuloActual->progreso ?? 0),
                    'progreso_capacitacion' => (float) ($miCapacitacion->progreso ?? 0),
                ]);
            }

        $modulo = CapacitacionModulo::where('id_capacitacion_modulo', $id_capacitacion_modulo)
            ->where('id_capacitacion', $miCapacitacion->id_capacitacion)
            ->where('estado', 1)
            ->firstOrFail();

        $data = $request->validate([
            'tipo_contenido' => ['required', 'in:seccion,recurso,ejercicio'],
            'id_contenido' => ['required', 'integer'],
        ]);

        $tipo = $data['tipo_contenido'];
        $idContenido = (int) $data['id_contenido'];

        $columnas = [
            'id_capacitacion_modulo_seccion' => null,
            'id_capacitacion_recurso' => null,
            'id_ejercicio' => null,
            'id_evaluacion' => null,
        ];

        $tituloContenido = 'contenido';

        if ($tipo === 'seccion') {
            $contenido = DB::table('capacitacion_modulo_seccion')
                ->where('id_capacitacion_modulo_seccion', $idContenido)
                ->where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
                ->first();

            abort_if(!$contenido, 404);

            $columnas['id_capacitacion_modulo_seccion'] = $idContenido;
            $tituloContenido = $contenido->titulo;
        }

        if ($tipo === 'recurso') {
            $contenido = DB::table('capacitacion_recurso')
                ->where('id_capacitacion_recurso', $idContenido)
                ->where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
                ->first();

            abort_if(!$contenido, 404);

            $columnas['id_capacitacion_recurso'] = $idContenido;
            $tituloContenido = $contenido->titulo;
        }

        if ($tipo === 'ejercicio') {
            $contenido = DB::table('ejercicio')
                ->where('id_ejercicio', $idContenido)
                ->where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
                ->first();

            abort_if(!$contenido, 404);

            $columnas['id_ejercicio'] = $idContenido;
            $tituloContenido = $contenido->titulo;
        }

        if (!$this->existeTablaContenidoAvance()) {
            return response()->json(['ok' => true]);
        }

        $fechaActual = now()->format('Ymd H:i:s');

        $condicion = [
            'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
            'tipo_contenido' => $tipo,
            ...$columnas,
        ];

        $yaExistia = DB::table('empleado_contenido_avance')
            ->where($condicion)
            ->exists();

        DB::table('empleado_contenido_avance')->updateOrInsert(
            $condicion,
            [
                'estado' => 'completado',
                'fecha_inicio' => $yaExistia ? DB::raw('fecha_inicio') : $fechaActual,
                'fecha_ultima_actividad' => $fechaActual,
                'fecha_completado' => $fechaActual,
                'updated_at' => $fechaActual,
                'created_at' => $yaExistia ? DB::raw('created_at') : $fechaActual,
            ]
        );

        if ($yaExistia) {
            $avanceModuloActual = EmpleadoModuloAvance::query()
                ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
                ->where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
                ->first();

            return response()->json([
                'ok' => true,
                'ya_existia' => true,
                'progreso_modulo' => (float) ($avanceModuloActual?->porcentaje_avance ?? 0),
                'progreso_capacitacion' => (float) ($miCapacitacion->progreso ?? 0),
                'estado_capacitacion' => $miCapacitacion->estado,
            ]);
        }

        if (!$yaExistia) {
            DB::table('historial_capacitacion_empleado')->insert([
                'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
                'estado_anterior' => null,
                'estado_nuevo' => 'en_proceso',
                'observacion' => 'Contenido visto: ' . ucfirst($tipo) . ' - ' . $tituloContenido,
                'fecha_movimiento' => $fechaActual,
                'id_user' => $usuario->id,
            ]);
        }

        $avanceModuloActualizado = app(AvanceModuloContenidoService::class)->sincronizar(
            $miCapacitacion,
            $modulo
        );

        app(ResumenCapacitacionEmpleadoService::class)->recalcular($miCapacitacion);
        $miCapacitacion->refresh();

        return response()->json([
            'ok' => true,
            'tipo' => $tipo,
            'id' => $idContenido,
            'progreso_modulo' => (float) ($avanceModuloActualizado->progreso ?? 0),
            'progreso_capacitacion' => (float) ($miCapacitacion->progreso ?? 0),
        ]);
    }

    private function sincronizarAvanceModuloDesdeContenido(
        EmpleadoCapacitacion $miCapacitacion,
        CapacitacionModulo $modulo,
        ?EmpleadoModuloAvance $avanceModulo = null
    ): ?EmpleadoModuloAvance {
        if (!$this->existeTablaContenidoAvance()) {
            return $avanceModulo;
        }

        $idsSecciones = $modulo->secciones
            ->where('estado', 1)
            ->pluck('id_capacitacion_modulo_seccion')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $idsRecursos = $modulo->recursos
            ->where('estado', 1)
            ->pluck('id_capacitacion_recurso')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $idsEjercicios = $modulo->ejercicios
            ->where('estado', 1)
            ->pluck('id_ejercicio')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $idsEvaluaciones = $modulo->evaluaciones
            ->where('activa', 1)
            ->pluck('id_evaluacion')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $totalContenido = $idsSecciones->count()
            + $idsRecursos->count()
            + $idsEjercicios->count()
            + $idsEvaluaciones->count();

        if ($totalContenido === 0) {
            return $avanceModulo;
        }

        $contenidoCompletado = 0;

        $contenidoCompletado += $this->contarAvanceContenidoModulo(
            $miCapacitacion,
            $modulo,
            'seccion',
            'id_capacitacion_modulo_seccion',
            $idsSecciones
        );

        $contenidoCompletado += $this->contarAvanceContenidoModulo(
            $miCapacitacion,
            $modulo,
            'recurso',
            'id_capacitacion_recurso',
            $idsRecursos
        );

        $contenidoCompletado += $this->contarAvanceContenidoModulo(
            $miCapacitacion,
            $modulo,
            'ejercicio',
            'id_ejercicio',
            $idsEjercicios
        );

        $contenidoCompletado += $this->contarAvanceContenidoModulo(
            $miCapacitacion,
            $modulo,
            'evaluacion',
            'id_evaluacion',
            $idsEvaluaciones
        );

        $progresoCalculado = round(min(100, ($contenidoCompletado / $totalContenido) * 100), 2);
        $fechaActual = now()->format('Ymd H:i:s');

        $avanceModulo = $avanceModulo ?: EmpleadoModuloAvance::firstOrNew([
            'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
        ]);

        if (!$avanceModulo->exists) {
            $avanceModulo->fecha_inicio = $fechaActual;
        }

        if (!in_array($avanceModulo->estado, ['completado', 'reprobado', 'vencido'], true)) {
            $avanceModulo->fecha_ultima_actividad = $fechaActual;
            $avanceModulo->progreso = $progresoCalculado;

            if ($progresoCalculado >= 100) {
                $avanceModulo->estado = 'completado';
                $avanceModulo->aprobado = 1;
                $avanceModulo->fecha_finalizacion = $fechaActual;
            } elseif ($progresoCalculado > 0) {
                $avanceModulo->estado = 'en_proceso';
                $avanceModulo->aprobado = 0;
                $avanceModulo->fecha_finalizacion = null;
            } else {
                $avanceModulo->estado = 'pendiente';
                $avanceModulo->aprobado = 0;
                $avanceModulo->fecha_finalizacion = null;
            }

            $avanceModulo->save();
        }

        return $avanceModulo;
    }

    private function contarAvanceContenidoModulo(
        EmpleadoCapacitacion $miCapacitacion,
        CapacitacionModulo $modulo,
        string $tipo,
        string $columna,
        $ids
    ): int {
        $ids = collect($ids)->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return 0;
        }

        return DB::table('empleado_contenido_avance')
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->where('id_capacitacion_modulo', $modulo->id_capacitacion_modulo)
            ->where('tipo_contenido', $tipo)
            ->whereIn($columna, $ids)
            ->count();
    }

    public function completar($id_empleado_capacitacion, $id_capacitacion_modulo)
    {
        $usuario = Auth::user();
        $empleadoId = optional($usuario->empleadoUser)->id_empleado;

        $miCapacitacion = EmpleadoCapacitacion::with('capacitacion')
            ->where('id_empleado_capacitacion', $id_empleado_capacitacion)
            ->where('id_empleado', $empleadoId)
            ->firstOrFail();

        $modulo = CapacitacionModulo::with([
            'ejercicios' => function ($query) {
                $query->where('estado', 1)->orderBy('orden');
            }
        ])
            ->where('id_capacitacion_modulo', $id_capacitacion_modulo)
            ->where('id_capacitacion', $miCapacitacion->id_capacitacion)
            ->where('estado', 1)
            ->firstOrFail();

        if ((int) $modulo->requiere_evaluacion === 1) {
            return redirect()
                ->route('mis_modulos.show', [
                    $miCapacitacion->id_empleado_capacitacion,
                    $modulo->id_capacitacion_modulo
                ])
                ->withErrors([
                    'modulo' => 'Este módulo requiere evaluación y no puede marcarse manualmente como completado.'
                ]);
        }

        $ejerciciosObligatorios = $modulo->ejercicios->where('obligatorio', 1);

        if ($ejerciciosObligatorios->count() > 0) {
            $ejerciciosObligatoriosIds = $ejerciciosObligatorios->pluck('id_ejercicio');

            $intentosEjerciciosObligatorios = EjercicioIntento::whereIn('id_ejercicio', $ejerciciosObligatoriosIds)
                ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
                ->get();

            $intentosPorEjercicio = $intentosEjerciciosObligatorios->groupBy('id_ejercicio');

            $ejerciciosObligatoriosCompletados = $ejerciciosObligatorios
                ->filter(function ($ejercicio) use ($intentosPorEjercicio) {
                    $intentos = $intentosPorEjercicio->get($ejercicio->id_ejercicio, collect());

                    return $this->ejercicioCuentaComoCompletado($ejercicio, $intentos);
                })
                ->count();

            if ($ejerciciosObligatoriosCompletados < $ejerciciosObligatorios->count()) {
                return redirect()
                    ->route('mis_modulos.show', [
                        $miCapacitacion->id_empleado_capacitacion,
                        $modulo->id_capacitacion_modulo
                    ])
                    ->withErrors([
                        'modulo' => 'Antes de completar este módulo debes resolver todos los ejercicios obligatorios.'
                    ]);
            }
        }

        $avanceModulo = EmpleadoModuloAvance::firstOrNew([
            'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
            'id_capacitacion_modulo' => $modulo->id_capacitacion_modulo,
        ]);

        $fechaAhoraSql = now()->format('Ymd H:i:s');

        if (!$avanceModulo->exists) {
            $avanceModulo->fecha_inicio = $fechaAhoraSql;
        }

        $avanceModulo->fecha_ultima_actividad = $fechaAhoraSql;
        $avanceModulo->fecha_finalizacion = $fechaAhoraSql;
        $avanceModulo->estado = 'completado';
        $avanceModulo->progreso = 100;
        $avanceModulo->nota = null;
        $avanceModulo->aprobado = 1;
        $avanceModulo->save();

        app(ResumenCapacitacionEmpleadoService::class)->recalcular($miCapacitacion);

        return redirect()
            ->route('mis_modulos.show', [
                $miCapacitacion->id_empleado_capacitacion,
                $modulo->id_capacitacion_modulo
            ])
            ->with('success', 'Módulo marcado como completado correctamente.');
    }

    private function intentoEjercicioCuentaComoCompletado($intento): bool
    {
        return in_array($intento->estado, ['finalizado', 'revisado'], true)
            && (int) ($intento->aprobado ?? 0) === 1;
    }

    private function ejercicioCuentaComoCompletado($ejercicio, $intentos): bool
    {
        $intentos = collect($intentos);

        if ($intentos->isEmpty()) {
            return false;
        }

        $tieneIntentoAprobado = $intentos->contains(function ($intento) {
            return in_array($intento->estado, ['finalizado', 'revisado'], true)
                && (int) ($intento->aprobado ?? 0) === 1;
        });

        if ($tieneIntentoAprobado) {
            return true;
        }

        $tienePendienteRevision = $intentos->contains(function ($intento) {
            return $intento->estado === 'pendiente_revision';
        });

        if ($tienePendienteRevision) {
            return false;
        }

        $intentosMaximos = $ejercicio->intentos_maximos
            ? (int) $ejercicio->intentos_maximos
            : null;

        if (is_null($intentosMaximos)) {
            return false;
        }

        return $intentos->count() >= $intentosMaximos;
    }

    private function puedeUsuarioVerRevision(int $intentosRealizados, ?int $intentosMaximos): bool
    {
        if (is_null($intentosMaximos)) {
            return false;
        }

        if ($intentosMaximos <= 1) {
            return $intentosRealizados >= 1;
        }

        return $intentosRealizados >= $intentosMaximos;
    }
}