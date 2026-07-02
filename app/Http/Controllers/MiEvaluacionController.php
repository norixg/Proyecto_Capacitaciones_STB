<?php

namespace App\Http\Controllers;

use App\Models\EmpleadoCapacitacion;
use App\Models\EmpleadoModuloAvance;
use App\Models\Evaluacion;
use App\Models\EvaluacionIntento;
use App\Models\EvaluacionIntentoRespuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ResumenCapacitacionEmpleadoService;
use App\Models\CapacitacionModulo;
use App\Models\EjercicioIntento;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use App\Services\AvanceModuloContenidoService;


class MiEvaluacionController extends Controller
{
    public function show($id_empleado_capacitacion, $id_evaluacion)
    {
        $usuario = Auth::user();
        $empleadoId = optional($usuario->empleadoUser)->id_empleado;

        $miCapacitacion = EmpleadoCapacitacion::where('id_empleado_capacitacion', $id_empleado_capacitacion)
            ->where('id_empleado', $empleadoId)
            ->firstOrFail();

        $evaluacion = Evaluacion::with([
            'capacitacionModulo',
            'preguntas' => function ($query) {
                $query->where('activa', 1)->orderBy('orden');
            },
            'preguntas.opciones' => function ($query) {
                $query->orderBy('orden');
            }
        ])
        ->where('id_evaluacion', $id_evaluacion)
        ->where('activa', 1)
        ->firstOrFail();

        if ($evaluacion->capacitacionModulo->id_capacitacion !== $miCapacitacion->id_capacitacion) {
            abort(403);
        }

        app(ResumenCapacitacionEmpleadoService::class)->recalcular($miCapacitacion);
            $miCapacitacion->refresh();

            if ($this->capacitacionBloqueadaPorEstado($miCapacitacion)) {
                return redirect()->route('mis_modulos.show', [
                    $miCapacitacion->id_empleado_capacitacion,
                    $evaluacion->id_capacitacion_modulo,
                ])->withErrors([
                    'evaluacion' => 'Esta capacitación ya finalizó. Solo puedes consultar el contenido del módulo.',
                ]);
            }

        $intentos = EvaluacionIntento::where('id_evaluacion', $evaluacion->id_evaluacion)
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->orderByDesc('numero_intento')
            ->get();

        $intentosRealizados = $intentos->count();
        $aprobadoEvaluacion = $intentos->where('aprobado', 1)->isNotEmpty();

        $intentosMaximos = $evaluacion->intentos_maximos
            ? (int) $evaluacion->intentos_maximos
            : null;

        $maximoIntentosAlcanzado = !is_null($intentosMaximos)
            ? $intentosRealizados >= $intentosMaximos
            : false;

        $intentosRestantes = is_null($intentosMaximos)
            ? null
            : max($intentosMaximos - $intentosRealizados, 0);

        $ultimoIntento = $intentos->first();

        $puedePresentar = !$maximoIntentosAlcanzado;

        $puedeVerRevisionUsuario = $this->puedeUsuarioVerRevision(
            $intentosRealizados,
            $intentosMaximos,
            $aprobadoEvaluacion
        );

        $tiempoLimiteMinutos = $evaluacion->tiempo_limite_minutos
            ? (int) $evaluacion->tiempo_limite_minutos
            : null;

        $tiempoLimiteSegundos = $tiempoLimiteMinutos
            ? $tiempoLimiteMinutos * 60
            : null;

        $sessionKeyTiempo = 'evaluacion_inicio_' . $miCapacitacion->id_empleado_capacitacion . '_' . $evaluacion->id_evaluacion;

        if (!$puedePresentar) {
            Session::forget($sessionKeyTiempo);
        }

        if ($puedePresentar && !is_null($tiempoLimiteSegundos) && !Session::has($sessionKeyTiempo)) {
            Session::put($sessionKeyTiempo, now()->timestamp);
        }

        $segundosRestantes = null;

        if ($puedePresentar && !is_null($tiempoLimiteSegundos)) {
            $inicioTimestamp = (int) Session::get($sessionKeyTiempo, now()->timestamp);
            $segundosTranscurridos = max(now()->timestamp - $inicioTimestamp, 0);
            $segundosRestantes = max($tiempoLimiteSegundos - $segundosTranscurridos, 0);
        }

        return view('mis_evaluaciones.show', compact(
            'miCapacitacion',
            'evaluacion',
            'intentos',
            'intentosRealizados',
            'intentosMaximos',
            'intentosRestantes',
            'ultimoIntento',
            'aprobadoEvaluacion',
            'maximoIntentosAlcanzado',
            'puedePresentar',
            'puedeVerRevisionUsuario',
            'tiempoLimiteMinutos',
            'segundosRestantes'
        ));
    }

    public function submit(Request $request, $id_empleado_capacitacion, $id_evaluacion)
    {
        $usuario = Auth::user();
        $empleadoId = optional($usuario->empleadoUser)->id_empleado;

        $miCapacitacion = EmpleadoCapacitacion::where('id_empleado_capacitacion', $id_empleado_capacitacion)
            ->where('id_empleado', $empleadoId)
            ->firstOrFail();

        $evaluacion = Evaluacion::with([
            'capacitacionModulo',
            'preguntas' => function ($query) {
                $query->where('activa', 1)->orderBy('orden');
            },
            'preguntas.opciones' => function ($query) {
                $query->orderBy('orden');
            }
        ])
        ->where('id_evaluacion', $id_evaluacion)
        ->where('activa', 1)
        ->firstOrFail();

        if ($evaluacion->capacitacionModulo->id_capacitacion !== $miCapacitacion->id_capacitacion) {
            abort(403);
        }


        app(ResumenCapacitacionEmpleadoService::class)->recalcular($miCapacitacion);
            $miCapacitacion->refresh();

            if ($this->capacitacionBloqueadaPorEstado($miCapacitacion)) {
                return redirect()->route('mis_modulos.show', [
                    $miCapacitacion->id_empleado_capacitacion,
                    $evaluacion->id_capacitacion_modulo,
                ])->withErrors([
                    'evaluacion' => 'Esta capacitación ya finalizó. No puedes presentar evaluaciones.',
                ]);
            }


        $ultimoIntento = EvaluacionIntento::where('id_evaluacion', $evaluacion->id_evaluacion)
            ->where('id_empleado', $empleadoId)
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->max('numero_intento');

        $nuevoIntentoNumero = ($ultimoIntento ?? 0) + 1;


        if ($evaluacion->intentos_maximos && $nuevoIntentoNumero > $evaluacion->intentos_maximos) {
            return back()
                ->withErrors([
                    'evaluacion' => 'Ya alcanzaste el máximo de intentos permitidos para esta evaluación.'
                ]);
        }

        $sessionKeyTiempo = 'evaluacion_inicio_' . $miCapacitacion->id_empleado_capacitacion . '_' . $evaluacion->id_evaluacion;
        $inicioTimestamp = Session::get($sessionKeyTiempo);

        if ($evaluacion->tiempo_limite_minutos && $inicioTimestamp) {
            $segundosPermitidos = ((int) $evaluacion->tiempo_limite_minutos) * 60;
            $segundosUsados = now()->timestamp - (int) $inicioTimestamp;

            if ($segundosUsados > ($segundosPermitidos + 10)) {
                Session::forget($sessionKeyTiempo);

                return redirect()->to(
                    route('mis_modulos.show', [
                        'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
                        'id_capacitacion_modulo' => $evaluacion->id_capacitacion_modulo,
                    ]) . '?evaluacion_integrada=' . $evaluacion->id_evaluacion . '#examen-general-modulo'
                )->withErrors([
                    'evaluacion' => 'El tiempo límite de la evaluación se agotó. Debes iniciar un nuevo intento si aún tienes intentos disponibles.'
                ])->with('id_evaluacion_aviso', $evaluacion->id_evaluacion);
            }
        }

        $fechaInicioSql = $inicioTimestamp
            ? Carbon::createFromTimestamp((int) $inicioTimestamp)->format('Ymd H:i:s')
            : now()->format('Ymd H:i:s');

        $respuestas = $request->input('respuestas', []);

        DB::beginTransaction();

        try {
            $intento = EvaluacionIntento::create([
                'id_evaluacion' => $evaluacion->id_evaluacion,
                'id_empleado' => $empleadoId,
                'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
                'numero_intento' => $nuevoIntentoNumero,
                'fecha_inicio' => $fechaInicioSql,
                'fecha_fin' => now()->format('Ymd H:i:s'),
                'nota' => 0,
                'aprobado' => 0,
                'estado' => 'finalizado',
            ]);

            $puntajeTotal = 0;
            $puntajeObtenido = 0;

            foreach ($evaluacion->preguntas as $pregunta) {
                $puntajeTotal += (float) $pregunta->puntaje;

                $respuestaUsuario = $respuestas[$pregunta->id_evaluacion_pregunta] ?? null;

                $resultadoPregunta = $this->calificarPreguntaEvaluacion($pregunta, $respuestaUsuario);

                $puntajeObtenido += (float) $resultadoPregunta['puntaje_obtenido'];

                EvaluacionIntentoRespuesta::create([
                    'id_evaluacion_intento' => $intento->id_evaluacion_intento,
                    'id_evaluacion_pregunta' => $pregunta->id_evaluacion_pregunta,
                    'id_evaluacion_opcion' => $resultadoPregunta['id_evaluacion_opcion'],
                    'respuesta_texto' => $resultadoPregunta['respuesta_texto'],
                    'es_correcta' => $resultadoPregunta['es_correcta'],
                    'puntaje_obtenido' => $resultadoPregunta['puntaje_obtenido'],
                ]);
            }

            $nota = $puntajeTotal > 0 ? round(($puntajeObtenido / $puntajeTotal) * 100, 2) : 0;
            $aprobado = $nota >= (float) $evaluacion->porcentaje_aprobacion ? 1 : 0;

            $intento->update([
                'nota' => $nota,
                'aprobado' => $aprobado,
            ]);

                $intentosDelModulo = EvaluacionIntento::where('id_evaluacion', $evaluacion->id_evaluacion)
                ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
                ->orderByDesc('numero_intento')
                ->get();

            $intentosRealizadosModulo = $intentosDelModulo->count();

            $intentosMaximos = $evaluacion->intentos_maximos
                ? (int) $evaluacion->intentos_maximos
                : null;

            $maximoIntentosAlcanzado = !is_null($intentosMaximos)
                ? $intentosRealizadosModulo >= $intentosMaximos
                : false;


            $intentosDelModulo = EvaluacionIntento::where('id_evaluacion', $evaluacion->id_evaluacion)
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->orderByDesc('numero_intento')
            ->get();

            $intentosRealizadosModulo = $intentosDelModulo->count();

            $intentosMaximos = $evaluacion->intentos_maximos
                ? (int) $evaluacion->intentos_maximos
                : null;

            $maximoIntentosAlcanzado = !is_null($intentosMaximos)
                ? $intentosRealizadosModulo >= $intentosMaximos
                : false;

            $avanceModulo = EmpleadoModuloAvance::firstOrNew([
                'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
                'id_capacitacion_modulo' => $evaluacion->id_capacitacion_modulo,
            ]);

            $fechaAhoraSql = now()->format('Ymd H:i:s');

            if (!$avanceModulo->exists) {
                $avanceModulo->fecha_inicio = $fechaAhoraSql;
            }

            $avanceModulo->fecha_ultima_actividad = $fechaAhoraSql;
            $avanceModulo->nota = $nota;
            $avanceModulo->aprobado = $aprobado;

            if ($aprobado) {
                $avanceModulo->estado = 'completado';
                $avanceModulo->progreso = 100;
                $avanceModulo->fecha_finalizacion = $fechaAhoraSql;
            } elseif ($maximoIntentosAlcanzado) {
                $avanceModulo->estado = 'reprobado';
                $avanceModulo->progreso = 100;
                $avanceModulo->fecha_finalizacion = $fechaAhoraSql;
            } else {
                $avanceModulo->estado = 'en_proceso';
                $avanceModulo->progreso = max((float) ($avanceModulo->progreso ?? 0), 50);
                $avanceModulo->fecha_finalizacion = null;
            }

            $avanceModulo->save();

            if ($aprobado || $maximoIntentosAlcanzado) {
                $this->registrarAvanceContenidoEvaluacionFinalizada(
                    $miCapacitacion,
                    $evaluacion,
                    (int) $usuario->id
                );
            }

            app(AvanceModuloContenidoService::class)->sincronizar(
                $miCapacitacion,
                (int) $evaluacion->id_capacitacion_modulo,
                $avanceModulo
            );

            app(ResumenCapacitacionEmpleadoService::class)->recalcular($miCapacitacion);

            $intentosRealizadosDespues = EvaluacionIntento::where('id_evaluacion', $evaluacion->id_evaluacion)
                ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
                ->count();

            $intentosMaximosEvaluacion = $evaluacion->intentos_maximos
                ? (int) $evaluacion->intentos_maximos
                : null;

            $intentosRestantes = is_null($intentosMaximosEvaluacion)
                ? null
                : max($intentosMaximosEvaluacion - $intentosRealizadosDespues, 0);

            DB::commit();

            Session::forget($sessionKeyTiempo);

            return redirect()->to(
                route('mis_modulos.show', [
                    'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
                    'id_capacitacion_modulo' => $evaluacion->id_capacitacion_modulo,
                ]) . '#examen-general-modulo'
            )->with('resultado_evaluacion', [
                'id_evaluacion' => $evaluacion->id_evaluacion,
                'titulo' => $evaluacion->titulo,
                'estado' => $aprobado ? 'aprobado' : 'reprobado',
                'porcentaje' => $nota,
                'intentos_realizados' => $intentosRealizadosDespues,
                'intentos_maximos' => $intentosMaximosEvaluacion,
                'intentos_restantes' => $intentosRestantes,
            ])->with('id_evaluacion_aviso', $evaluacion->id_evaluacion);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function resultado($id_empleado_capacitacion, $id_intento)
    {
        $usuario = Auth::user();
        $empleadoId = optional($usuario->empleadoUser)->id_empleado;

        $miCapacitacion = EmpleadoCapacitacion::where('id_empleado_capacitacion', $id_empleado_capacitacion)
            ->where('id_empleado', $empleadoId)
            ->firstOrFail();

        $intento = EvaluacionIntento::with([
            'evaluacion.capacitacionModulo',
            'respuestas' => function ($query) {
                $query->with([
                    'pregunta' => function ($preguntaQuery) {
                        $preguntaQuery->with([
                            'opciones' => function ($opcionesQuery) {
                                $opcionesQuery->orderBy('orden');
                            }
                        ]);
                    },
                    'opcion'
                ]);
            }
        ])
        ->where('id_evaluacion_intento', $id_intento)
        ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
        ->firstOrFail();

        $respuestas = $intento->respuestas
            ->sortBy(function ($respuesta) {
                return optional($respuesta->pregunta)->orden ?? 9999;
            })
            ->values();

        $totalPreguntas = $respuestas->count();
        $totalCorrectas = $respuestas->where('es_correcta', 1)->count();
        $totalIncorrectas = $totalPreguntas - $totalCorrectas;

        $intentosRealizados = EvaluacionIntento::where('id_evaluacion', $intento->id_evaluacion)
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->count();

        $intentosMaximos = $intento->evaluacion?->intentos_maximos
            ? (int) $intento->evaluacion->intentos_maximos
            : null;

        $aprobadoEvaluacion = EvaluacionIntento::where('id_evaluacion', $intento->id_evaluacion)
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->where('aprobado', 1)
            ->exists();

        $puedeVerRevisionUsuario = $this->puedeUsuarioVerRevision(
            $intentosRealizados,
            $intentosMaximos,
            $aprobadoEvaluacion
        );

        if (!$puedeVerRevisionUsuario) {
            return redirect()->to(
                route('mis_modulos.show', [
                    'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
                    'id_capacitacion_modulo' => $intento->evaluacion->id_capacitacion_modulo,
                ]) . '#examen-general-modulo'
            )->withErrors([
                'evaluacion' => 'La revisión detallada se habilitará cuando hayas agotado todos los intentos disponibles.'
            ])->with('id_evaluacion_aviso', $intento->id_evaluacion);
        }

        return view('mis_evaluaciones.resultado', compact(
            'miCapacitacion',
            'intento',
            'respuestas',
            'totalPreguntas',
            'totalCorrectas',
            'totalIncorrectas'
        ));
    }

    private function registrarAvanceContenidoEvaluacionFinalizada(
        EmpleadoCapacitacion $miCapacitacion,
        Evaluacion $evaluacion,
        int $idUser
    ): void {
        if (!Schema::hasTable('empleado_contenido_avance')) {
            return;
        }

        $fechaActual = now()->format('Ymd H:i:s');

        $condicion = [
            'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
            'id_capacitacion_modulo' => $evaluacion->id_capacitacion_modulo,
            'tipo_contenido' => 'evaluacion',
            'id_capacitacion_modulo_seccion' => null,
            'id_capacitacion_recurso' => null,
            'id_ejercicio' => null,
            'id_evaluacion' => $evaluacion->id_evaluacion,
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

        if (!$yaExistia && Schema::hasTable('historial_capacitacion_empleado')) {
            DB::table('historial_capacitacion_empleado')->insert([
                'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
                'estado_anterior' => null,
                'estado_nuevo' => 'en_proceso',
                'observacion' => 'Contenido visto: Evaluación - ' . $evaluacion->titulo,
                'fecha_movimiento' => $fechaActual,
                'id_user' => $idUser,
            ]);
        }
    }

    private function respuestaEvaluacionFueRespondida($pregunta, $respuesta): bool
    {
        if (in_array($pregunta->tipo_pregunta, ['checklist_guiado', 'opcion_multiple', 'multiple'], true)) {
            return is_array($respuesta) && count(array_filter($respuesta)) > 0;
        }

        if ($pregunta->tipo_pregunta === 'seleccionar_posicion_imagen') {
            return is_array($respuesta) && count(array_filter($respuesta)) === $pregunta->opciones->count();
        }

        if (in_array($pregunta->tipo_pregunta, ['completar', 'respuesta_corta'], true)) {
            return trim((string) $respuesta) !== '';
        }

        return !empty($respuesta);
    }

    private function calificarPreguntaEvaluacion($pregunta, $respuestaUsuario): array
    {
        $tipo = $pregunta->tipo_pregunta;
        $puntajePregunta = (float) $pregunta->puntaje;

        if (in_array($tipo, ['checklist_guiado', 'opcion_multiple', 'multiple'], true)) {
            $idsSeleccionadas = collect((array) $respuestaUsuario)
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            $opciones = $pregunta->opciones->values();
            $totalPartes = $opciones->count();

            $correctas = $opciones->filter(function ($opcion) use ($idsSeleccionadas) {
                $seleccionada = $idsSeleccionadas->contains((int) $opcion->id_evaluacion_opcion);
                $debeSeleccionarse = (int) $opcion->es_correcta === 1;

                return $seleccionada === $debeSeleccionarse;
            })->count();

            $esCompletamenteCorrecta = $totalPartes > 0 && $correctas === $totalPartes;

            $puntajeObtenido = $totalPartes > 0
                ? round(($correctas / $totalPartes) * $puntajePregunta, 2)
                : 0;

            return [
                'id_evaluacion_opcion' => null,
                'respuesta_texto' => json_encode([
                    'opciones' => $idsSeleccionadas->all(),
                    'correctas' => $correctas,
                    'total_partes' => $totalPartes,
                ], JSON_UNESCAPED_UNICODE),
                'es_correcta' => $esCompletamenteCorrecta ? 1 : 0,
                'puntaje_obtenido' => $puntajeObtenido,
            ];
        }

        if ($tipo === 'seleccionar_posicion_imagen') {
            $posicionesUsuario = collect((array) $respuestaUsuario)
                ->mapWithKeys(function ($valor, $clave) {
                    return [(int) $clave => (int) $valor];
                });

            $totalPartes = $pregunta->opciones->count();

            $correctas = $pregunta->opciones->filter(function ($opcion) use ($posicionesUsuario) {
                return (int) ($posicionesUsuario->get((int) $opcion->id_evaluacion_opcion) ?? 0) === (int) $opcion->orden;
            })->count();

            $esCorrecta = $totalPartes > 0 && $correctas === $totalPartes;
            $puntajeObtenido = $totalPartes > 0
                ? round(($correctas / $totalPartes) * $puntajePregunta, 2)
                : 0;

            return [
                'id_evaluacion_opcion' => null,
                'respuesta_texto' => json_encode([
                    'posiciones' => $posicionesUsuario->all(),
                    'correctas' => $correctas,
                    'total_partes' => $totalPartes,
                ], JSON_UNESCAPED_UNICODE),
                'es_correcta' => $esCorrecta ? 1 : 0,
                'puntaje_obtenido' => $puntajeObtenido,
            ];
        }

        if ($tipo === 'completar') {
            $respuestaTexto = trim((string) $respuestaUsuario);

            $normalizar = function ($valor) {
                $valor = mb_strtolower(trim((string) $valor));
                $valor = preg_replace('/\s+/', ' ', $valor);
                return $valor;
            };

            $respuestasValidas = collect(preg_split('/\r\n|\r|\n|\|/', (string) $pregunta->respuesta_correcta_texto))
                ->map(fn ($valor) => trim((string) $valor))
                ->filter(fn ($valor) => $valor !== '')
                ->values();

            $respuestaNormalizada = $normalizar($respuestaTexto);

            $esCorrecta = $respuestasValidas->contains(function ($respuestaValida) use ($normalizar, $respuestaNormalizada) {
                return $respuestaNormalizada === $normalizar($respuestaValida);
            });

            return [
                'id_evaluacion_opcion' => null,
                'respuesta_texto' => json_encode([
                    'respuesta_usuario' => $respuestaTexto,
                    'respuestas_validas' => $respuestasValidas->all(),
                ], JSON_UNESCAPED_UNICODE),
                'es_correcta' => $esCorrecta ? 1 : 0,
                'puntaje_obtenido' => $esCorrecta ? $puntajePregunta : 0,
            ];
        }

        if ($tipo === 'respuesta_corta') {
            return [
                'id_evaluacion_opcion' => null,
                'respuesta_texto' => trim((string) $respuestaUsuario),
                'es_correcta' => 0,
                'puntaje_obtenido' => 0,
            ];
        }

        $idSeleccionada = $respuestaUsuario !== null && $respuestaUsuario !== ''
            ? (int) $respuestaUsuario
            : null;

        $opcionSeleccionada = $idSeleccionada
            ? $pregunta->opciones->firstWhere('id_evaluacion_opcion', $idSeleccionada)
            : null;

        $opcionCorrecta = $pregunta->opciones->firstWhere('es_correcta', 1);

        $esCorrecta = $opcionSeleccionada
            && $opcionCorrecta
            && (int) $opcionCorrecta->id_evaluacion_opcion === (int) $opcionSeleccionada->id_evaluacion_opcion;

        return [
            'id_evaluacion_opcion' => $opcionSeleccionada ? $opcionSeleccionada->id_evaluacion_opcion : null,
            'respuesta_texto' => null,
            'es_correcta' => $esCorrecta ? 1 : 0,
            'puntaje_obtenido' => $esCorrecta ? $puntajePregunta : 0,
        ];
    }

    private function obtenerResumenEjerciciosObligatorios(EmpleadoCapacitacion $miCapacitacion, int $idCapacitacionModulo): array
    {
        $modulo = CapacitacionModulo::with([
            'ejercicios' => function ($query) {
                $query->where('estado', 1)->orderBy('orden');
            }
        ])->find($idCapacitacionModulo);

        if (!$modulo) {
            return [0, 0, true];
        }

        $ejerciciosObligatorios = $modulo->ejercicios
            ->where('obligatorio', 1)
            ->values();

        if ($ejerciciosObligatorios->isEmpty()) {
            return [0, 0, true];
        }

        $idsEjerciciosObligatorios = $ejerciciosObligatorios->pluck('id_ejercicio')->values();

        $intentosEjercicios = EjercicioIntento::whereIn('id_ejercicio', $idsEjerciciosObligatorios)
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->get()
            ->groupBy('id_ejercicio');

        $totalObligatorios = $ejerciciosObligatorios->count();

        $completados = $ejerciciosObligatorios
            ->filter(function ($ejercicio) use ($intentosEjercicios) {
                $intentos = $intentosEjercicios->get($ejercicio->id_ejercicio, collect());

                return $this->ejercicioCuentaComoCompletado($ejercicio, $intentos);
            })
            ->count();

        $completos = $completados >= $totalObligatorios;

        return [$totalObligatorios, $completados, $completos];
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

    private function puedeUsuarioVerRevision(int $intentosRealizados, ?int $intentosMaximos, bool $aprobado = false): bool
    {
        if ($aprobado) {
            return true;
        }

        if (is_null($intentosMaximos)) {
            return false;
        }

        if ($intentosMaximos <= 1) {
            return $intentosRealizados >= 1;
        }

        return $intentosRealizados >= $intentosMaximos;
    }

    private function capacitacionBloqueadaPorEstado(EmpleadoCapacitacion $miCapacitacion): bool
    {
        return in_array($miCapacitacion->estado, ['vencida', 'reprobada', 'aprobada', 'cancelada'], true);
    }

}