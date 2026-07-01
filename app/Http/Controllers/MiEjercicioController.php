<?php

namespace App\Http\Controllers;

use App\Models\EmpleadoCapacitacion;
use App\Models\EmpleadoModuloAvance;
use App\Models\Ejercicio;
use App\Models\EjercicioIntento;
use App\Models\EjercicioIntentoRespuesta;
use App\Services\ResumenCapacitacionEmpleadoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\EjercicioPregunta;
use App\Services\AvanceModuloContenidoService;

class MiEjercicioController extends Controller
{
    public function show(int $id_empleado_capacitacion, int $id_ejercicio)
    {
        $usuario = Auth::user();
        $empleadoId = optional($usuario->empleadoUser)->id_empleado;

        $miCapacitacion = EmpleadoCapacitacion::with('capacitacion')
            ->where('id_empleado_capacitacion', $id_empleado_capacitacion)
            ->where('id_empleado', $empleadoId)
            ->firstOrFail();

        $ejercicio = Ejercicio::with([
            'modulo.capacitacion',
            'preguntas' => function ($query) {
                $query->where('activa', 1)->orderBy('orden');
            },
            'preguntas.opciones' => function ($query) {
                $query->orderBy('orden');
            },
        ])
            ->where('id_ejercicio', $id_ejercicio)
            ->where('estado', 1)
            ->firstOrFail();

        if ($ejercicio->modulo->id_capacitacion !== $miCapacitacion->id_capacitacion) {
            abort(403);
        }

        app(ResumenCapacitacionEmpleadoService::class)->recalcular($miCapacitacion);
            $miCapacitacion->refresh();

            if ($this->capacitacionBloqueadaPorEstado($miCapacitacion)) {
                return redirect()->route('mis_modulos.show', [
                    $miCapacitacion->id_empleado_capacitacion,
                    $ejercicio->modulo->id_capacitacion_modulo,
                ])->withErrors([
                    'ejercicio' => 'Esta capacitación ya finalizó. Solo puedes consultar el contenido del módulo.',
                ]);
            }

        $intentos = EjercicioIntento::where('id_ejercicio', $ejercicio->id_ejercicio)
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->orderByDesc('numero_intento')
            ->get();

        $intentosRealizados = $intentos->count();
        $intentosMaximos = $ejercicio->intentos_maximos ? (int) $ejercicio->intentos_maximos : null;
        $intentosRestantes = is_null($intentosMaximos)
            ? null
            : max($intentosMaximos - $intentosRealizados, 0);

        $aprobadoEjercicio = $intentos->where('aprobado', 1)->isNotEmpty();

        $puedeVerRevisionUsuario = $this->puedeUsuarioVerRevision(
            $intentosRealizados,
            $intentosMaximos,
            $aprobadoEjercicio
        );

        $ultimoIntento = $intentos->first();

        $tienePendienteRevision = $intentos->where('estado', 'pendiente_revision')->isNotEmpty();
        $maximoIntentosAlcanzado = !is_null($intentosMaximos) && $intentosRealizados >= $intentosMaximos;

        $puedeResolver = !$tienePendienteRevision && !$maximoIntentosAlcanzado;

        $tiempoLimiteMinutos = $ejercicio->tiempo_limite_minutos
            ? (int) $ejercicio->tiempo_limite_minutos
            : null;

        $tiempoLimiteSegundos = $tiempoLimiteMinutos
            ? $tiempoLimiteMinutos * 60
            : null;

        $sessionKeyTiempo = 'ejercicio_inicio_' . $miCapacitacion->id_empleado_capacitacion . '_' . $ejercicio->id_ejercicio;

        if (!$puedeResolver) {
            session()->forget($sessionKeyTiempo);
        }

        if ($puedeResolver && !is_null($tiempoLimiteSegundos) && !session()->has($sessionKeyTiempo)) {
            session([$sessionKeyTiempo => now()->timestamp]);
        }

        $segundosRestantes = null;

        if ($puedeResolver && !is_null($tiempoLimiteSegundos)) {
            $inicioTimestamp = (int) session($sessionKeyTiempo, now()->timestamp);
            $segundosTranscurridos = max(now()->timestamp - $inicioTimestamp, 0);
            $segundosRestantes = max($tiempoLimiteSegundos - $segundosTranscurridos, 0);
        }

        $ejercicio->preguntas->each(function ($pregunta) {
            if ($pregunta->tipo_pregunta === 'relacionar') {
                $pregunta->setRelation(
                    'opcionesIzquierdaTemp',
                    $pregunta->opciones->where('lado', 'izquierda')->sortBy('orden')->values()
                );

                $pregunta->setRelation(
                    'opcionesDerechaTemp',
                    $pregunta->opciones->where('lado', 'derecha')->shuffle()->values()
                );
            }
        });

        return view('mis_ejercicios.show', compact(
            'miCapacitacion',
            'ejercicio',
            'intentos',
            'intentosRealizados',
            'intentosMaximos',
            'intentosRestantes',
            'ultimoIntento',
            'tienePendienteRevision',
            'maximoIntentosAlcanzado',
            'puedeVerRevisionUsuario',
            'puedeResolver',
            'tiempoLimiteMinutos',
            'segundosRestantes'
        ));
    }

    public function submit(Request $request, int $id_empleado_capacitacion, int $id_ejercicio)
    {
        $usuario = Auth::user();
        $empleadoId = optional($usuario->empleadoUser)->id_empleado;

        $miCapacitacion = EmpleadoCapacitacion::with('capacitacion')
            ->where('id_empleado_capacitacion', $id_empleado_capacitacion)
            ->where('id_empleado', $empleadoId)
            ->firstOrFail();

        $ejercicio = Ejercicio::with([
            'modulo.capacitacion',
            'preguntas' => function ($query) {
                $query->where('activa', 1)->orderBy('orden');
            },
            'preguntas.opciones' => function ($query) {
                $query->orderBy('orden');
            },
        ])
            ->where('id_ejercicio', $id_ejercicio)
            ->where('estado', 1)
            ->firstOrFail();

        if ($ejercicio->modulo->id_capacitacion !== $miCapacitacion->id_capacitacion) {
            abort(403);
        }

        app(ResumenCapacitacionEmpleadoService::class)->recalcular($miCapacitacion);
            $miCapacitacion->refresh();

            if ($this->capacitacionBloqueadaPorEstado($miCapacitacion)) {
                return redirect()->route('mis_modulos.show', [
                    $miCapacitacion->id_empleado_capacitacion,
                    $ejercicio->modulo->id_capacitacion_modulo,
                ])->withErrors([
                    'ejercicio' => 'Esta capacitación ya finalizó. No puedes enviar ejercicios.',
                ]);
            }

        $intentosExistentes = EjercicioIntento::where('id_ejercicio', $ejercicio->id_ejercicio)
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->orderByDesc('numero_intento')
            ->get();

        if ($intentosExistentes->where('estado', 'pendiente_revision')->isNotEmpty()) {
            return back()->withErrors([
                'ejercicio' => 'Ya tienes un intento pendiente de revisión manual para este ejercicio.'
            ])->withInput();
        }

        if ($intentosExistentes->where('aprobado', 1)->isNotEmpty()) {
            return back()->withErrors([
                'ejercicio' => 'Ya aprobaste este ejercicio. No es necesario realizar más intentos.'
            ]);
        }

        $ultimoNumeroIntento = $intentosExistentes->max('numero_intento') ?? 0;
        $nuevoNumeroIntento = $ultimoNumeroIntento + 1;

        if ($ejercicio->intentos_maximos && $nuevoNumeroIntento > (int) $ejercicio->intentos_maximos) {
            return back()->withErrors([
                'ejercicio' => 'Ya alcanzaste el máximo de intentos permitidos para este ejercicio.'
            ]);
        }

        $respuestas = $request->input('respuestas', []);

        foreach ($ejercicio->preguntas as $pregunta) {
            $respuestaPregunta = $respuestas[$pregunta->id_ejercicio_pregunta] ?? [];

            if ($this->preguntaFueRespondida($pregunta, $respuestaPregunta)) {
                $errorTexto = $this->validarRestriccionesTexto($pregunta, $respuestaPregunta);

                if ($errorTexto) {
                    return back()->withErrors([
                        'ejercicio' => $errorTexto,
                    ])->withInput();
                }
            }
        }

        $sessionKeyTiempo = 'ejercicio_inicio_' . $miCapacitacion->id_empleado_capacitacion . '_' . $ejercicio->id_ejercicio;
        $inicioTimestamp = session($sessionKeyTiempo);

        if ($ejercicio->tiempo_limite_minutos && $inicioTimestamp) {
            $segundosPermitidos = ((int) $ejercicio->tiempo_limite_minutos) * 60;
            $segundosUsados = now()->timestamp - (int) $inicioTimestamp;

            if ($segundosUsados > ($segundosPermitidos + 10)) {
                session()->forget($sessionKeyTiempo);

                return redirect()->to(
                    route('mis_modulos.show', [
                        'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
                        'id_capacitacion_modulo' => $ejercicio->modulo->id_capacitacion_modulo,
                    ]) . '?ejercicio_integrado=' . $ejercicio->id_ejercicio . '#contenido-ejercicio-' . $ejercicio->id_ejercicio
                )->withErrors([
                    'ejercicio' => 'El tiempo límite del ejercicio se agotó. Debes iniciar un nuevo intento si aún tienes intentos disponibles.'
                ])->with('id_ejercicio_aviso', $ejercicio->id_ejercicio);
            }
        }

        $fechaInicioSql = $inicioTimestamp
            ? Carbon::createFromTimestamp((int) $inicioTimestamp)->format('Ymd H:i:s')
            : now()->format('Ymd H:i:s');

        DB::beginTransaction();

        try {
            $fechaAhoraSql = now()->format('Ymd H:i:s');

            $intento = EjercicioIntento::create([
                'id_ejercicio' => $ejercicio->id_ejercicio,
                'id_empleado' => $empleadoId,
                'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
                'numero_intento' => $nuevoNumeroIntento,
                'fecha_inicio' => $fechaInicioSql,
                'fecha_fin' => $fechaAhoraSql,
                'puntaje_obtenido' => null,
                'porcentaje_obtenido' => null,
                'aprobado' => null,
                'estado' => 'en_proceso',
                'comentario_revision' => null,
            ]);

            $puntajeTotal = 0;
            $puntajeObtenido = 0;
            $requiereRevisionManual = false;

            foreach ($ejercicio->preguntas as $pregunta) {
                $puntajeTotal += (float) $pregunta->puntaje;

                $resultado = $this->resolverRespuestaPregunta(
                    $pregunta,
                    $respuestas[$pregunta->id_ejercicio_pregunta] ?? [],
                    (int) $ejercicio->requiere_revision_manual === 1
                );

                if ($resultado['manual']) {
                    $requiereRevisionManual = true;
                }

                $puntajeObtenido += (float) ($resultado['puntaje_obtenido'] ?? 0);

                EjercicioIntentoRespuesta::create([
                    'id_ejercicio_intento' => $intento->id_ejercicio_intento,
                    'id_ejercicio_pregunta' => $pregunta->id_ejercicio_pregunta,
                    'respuesta_texto' => $resultado['respuesta_texto'],
                    'respuesta_json' => $resultado['respuesta_json'],
                    'es_correcta' => $resultado['es_correcta'],
                    'puntaje_obtenido' => $resultado['puntaje_obtenido'],
                    'comentario_revision' => null,
                ]);
            }

            $porcentajeObtenido = $puntajeTotal > 0
                ? round(($puntajeObtenido / $puntajeTotal) * 100, 2)
                : 0;

            $porcentajeAprobacionEjercicio = !is_null($ejercicio->porcentaje_aprobacion)
                ? (float) $ejercicio->porcentaje_aprobacion
                : 70;

            $aprobadoAutomatico = $requiereRevisionManual
                ? null
                : ($porcentajeObtenido >= $porcentajeAprobacionEjercicio ? 1 : 0);

            $intento->update([
                'fecha_fin' => $fechaAhoraSql,
                'puntaje_obtenido' => $requiereRevisionManual ? null : $puntajeObtenido,
                'porcentaje_obtenido' => $requiereRevisionManual ? null : $porcentajeObtenido,
                'aprobado' => $aprobadoAutomatico,
                'estado' => $requiereRevisionManual ? 'pendiente_revision' : 'finalizado',
            ]);

            $this->actualizarAvanceModuloDesdeEjercicios($miCapacitacion, $ejercicio->modulo->id_capacitacion_modulo);

            app(ResumenCapacitacionEmpleadoService::class)->recalcular($miCapacitacion);

            $intentosRealizadosDespues = EjercicioIntento::where('id_ejercicio', $ejercicio->id_ejercicio)
                ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
                ->count();

            $intentosMaximosEjercicio = $ejercicio->intentos_maximos
                ? (int) $ejercicio->intentos_maximos
                : null;

            $intentosRestantes = is_null($intentosMaximosEjercicio)
                ? null
                : max($intentosMaximosEjercicio - $intentosRealizadosDespues, 0);

            $estadoResumen = $requiereRevisionManual
                ? 'pendiente_revision'
                : ($aprobadoAutomatico === 1 ? 'aprobado' : 'reprobado');

            DB::commit();

            session()->forget($sessionKeyTiempo);

            return redirect()->to(
                route('mis_modulos.show', [
                    'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
                    'id_capacitacion_modulo' => $ejercicio->modulo->id_capacitacion_modulo,
                ]) . '#contenido-ejercicio-' . $ejercicio->id_ejercicio
            )->with('resultado_ejercicio', [
                'id_ejercicio' => $ejercicio->id_ejercicio,
                'titulo' => $ejercicio->titulo,
                'estado' => $estadoResumen,
                'porcentaje' => $requiereRevisionManual ? null : $porcentajeObtenido,
                'intentos_realizados' => $intentosRealizadosDespues,
                'intentos_maximos' => $intentosMaximosEjercicio,
                'intentos_restantes' => $intentosRestantes,
            ])->with('id_ejercicio_aviso', $ejercicio->id_ejercicio);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;

        }
    }

    public function resultado(int $id_empleado_capacitacion, int $id_intento)
    {
        $usuario = Auth::user();
        $empleadoId = optional($usuario->empleadoUser)->id_empleado;

        $miCapacitacion = EmpleadoCapacitacion::where('id_empleado_capacitacion', $id_empleado_capacitacion)
            ->where('id_empleado', $empleadoId)
            ->firstOrFail();

        $intento = EjercicioIntento::with([
            'ejercicio.modulo.capacitacion',
            'respuestas' => function ($query) {
                $query->with([
                    'pregunta' => function ($preguntaQuery) {
                        $preguntaQuery->with([
                            'opciones' => function ($opcionesQuery) {
                                $opcionesQuery->orderBy('orden');
                            }
                        ]);
                    }
                ]);
            }
        ])
            ->where('id_ejercicio_intento', $id_intento)
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->firstOrFail();

        $intentosRealizados = EjercicioIntento::where('id_ejercicio', $intento->id_ejercicio)
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->count();

        $intentosMaximos = $intento->ejercicio?->intentos_maximos
            ? (int) $intento->ejercicio->intentos_maximos
            : null;

        $aprobadoEjercicio = EjercicioIntento::where('id_ejercicio', $intento->id_ejercicio)
            ->where('id_empleado_capacitacion', $miCapacitacion->id_empleado_capacitacion)
            ->where('aprobado', 1)
            ->exists();

        $puedeVerRevisionUsuario = $this->puedeUsuarioVerRevision(
            $intentosRealizados,
            $intentosMaximos,
            $aprobadoEjercicio
        );

        if (!$puedeVerRevisionUsuario) {
            return redirect()->to(
                route('mis_modulos.show', [
                    'id_empleado_capacitacion' => $miCapacitacion->id_empleado_capacitacion,
                    'id_capacitacion_modulo' => $intento->ejercicio->id_capacitacion_modulo,
                ]) . '#contenido-ejercicio-' . $intento->id_ejercicio
            )->withErrors([
                'ejercicio' => 'La revisión detallada se habilitará cuando hayas agotado todos los intentos disponibles.'
            ])->with('id_ejercicio_aviso', $intento->id_ejercicio);
        }

        $respuestas = $intento->respuestas
            ->sortBy(function ($respuesta) {
                return optional($respuesta->pregunta)->orden ?? 9999;
            })
            ->values();

        $totalPreguntas = $respuestas->count();
        $totalCorrectas = $respuestas->where('es_correcta', 1)->count();
        $totalIncorrectas = $respuestas->where('es_correcta', 0)->count();
        $totalPendientesRevision = $respuestas->whereNull('es_correcta')->count();

        return view('mis_ejercicios.resultado', compact(
            'miCapacitacion',
            'intento',
            'respuestas',
            'totalPreguntas',
            'totalCorrectas',
            'totalIncorrectas',
            'totalPendientesRevision'
        ));
    }

    private function preguntaFueRespondida(EjercicioPregunta $pregunta, array $respuesta): bool
    {
        return match ($pregunta->tipo_pregunta) {
            'opcion_unica', 'verdadero_falso'
                => !empty($respuesta['opcion']),

            'actividad_visual_identificacion'
                => trim((string) ($respuesta['texto'] ?? '')) !== '',

            'checklist_guiado', 'opcion_multiple'
                => (
                    isset($respuesta['items'])
                    && is_array($respuesta['items'])
                    && count(array_filter($respuesta['items'])) > 0
                ) || (
                    isset($respuesta['opciones'])
                    && is_array($respuesta['opciones'])
                    && count(array_filter($respuesta['opciones'])) > 0
                ),

            'seleccionar_posicion_imagen'
                => isset($respuesta['posiciones'])
                    && is_array($respuesta['posiciones'])
                    && count(array_filter($respuesta['posiciones'])) === $pregunta->opciones->count(),

            'ordenar'
                => isset($respuesta['ordenes'])
                    && is_array($respuesta['ordenes'])
                    && count(array_filter($respuesta['ordenes'])) === $pregunta->opciones->count(),

            'relacionar'
                => isset($respuesta['relaciones'])
                    && is_array($respuesta['relaciones'])
                    && count(array_filter($respuesta['relaciones'])) === $pregunta->opciones->where('lado', 'izquierda')->count(),

            default
                => trim((string) ($respuesta['texto'] ?? '')) !== '',
        };
    }

    private function validarRestriccionesTexto(EjercicioPregunta $pregunta, array $respuesta): ?string
    {
        if (!in_array($pregunta->tipo_pregunta, ['respuesta_corta', 'caso_practico'], true)) {
            return null;
        }

        $texto = trim((string) ($respuesta['texto'] ?? ''));
        $config = json_decode($pregunta->configuracion_json ?? '{}', true);

        $min = isset($config['min_caracteres']) && $config['min_caracteres'] !== null
            ? (int) $config['min_caracteres']
            : null;

        $max = isset($config['max_caracteres']) && $config['max_caracteres'] !== null
            ? (int) $config['max_caracteres']
            : null;

        $longitud = mb_strlen($texto);

        if (!is_null($min) && $longitud < $min) {
            return 'La respuesta para "' . $pregunta->enunciado . '" debe tener al menos ' . $min . ' caracteres.';
        }

        if (!is_null($max) && $longitud > $max) {
            return 'La respuesta para "' . $pregunta->enunciado . '" no debe superar ' . $max . ' caracteres.';
        }

        return null;
    }

    private function resolverRespuestaPregunta(EjercicioPregunta $pregunta, array $respuesta, bool $ejercicioManual): array
    {
        $tipo = $pregunta->tipo_pregunta;
        $puntajePregunta = (float) $pregunta->puntaje;
        $preguntaManual = $ejercicioManual || (int) $pregunta->requiere_revision_manual === 1;

        if (!$this->preguntaFueRespondida($pregunta, $respuesta)) {
            return [
                'respuesta_texto' => null,
                'respuesta_json' => json_encode([
                    'sin_respuesta' => true,
                ], JSON_UNESCAPED_UNICODE),
                'es_correcta' => 0,
                'puntaje_obtenido' => 0,
                'manual' => false,
            ];
        }

        if ($tipo === 'opcion_unica' || $tipo === 'verdadero_falso') {
            $opcionSeleccionada = (int) ($respuesta['opcion'] ?? 0);
            $opcionCorrecta = $pregunta->opciones->firstWhere('es_correcta', 1);

            $esCorrecta = $opcionCorrecta && (int) $opcionCorrecta->id_ejercicio_opcion === $opcionSeleccionada;

            return [
                'respuesta_texto' => null,
                'respuesta_json' => json_encode([
                    'opcion' => $opcionSeleccionada,
                ], JSON_UNESCAPED_UNICODE),
                'es_correcta' => $esCorrecta ? 1 : 0,
                'puntaje_obtenido' => $esCorrecta ? $puntajePregunta : 0,
                'manual' => false,
            ];
        }

        if (in_array($tipo, ['checklist_guiado', 'opcion_multiple'], true)) {
            $itemsMarcados = collect($respuesta['items'] ?? $respuesta['opciones'] ?? [])
                ->filter(fn ($valor) => $valor !== null && $valor !== '')
                ->map(fn ($valor) => (int) $valor)
                ->unique()
                ->values();

            $opciones = $pregunta->opciones->values();
            $totalPartes = $opciones->count();

            $correctas = $opciones->filter(function ($opcion) use ($itemsMarcados) {
                $seleccionada = $itemsMarcados->contains((int) $opcion->id_ejercicio_opcion);
                $debeSeleccionarse = (int) $opcion->es_correcta === 1;

                return $seleccionada === $debeSeleccionarse;
            })->count();

            $esCorrecta = $totalPartes > 0 && $correctas === $totalPartes;

            $puntajeObtenido = $totalPartes > 0
                ? round(($correctas / $totalPartes) * $puntajePregunta, 2)
                : 0;

            return [
                'respuesta_texto' => null,
                'respuesta_json' => json_encode([
                    'items' => $itemsMarcados->all(),
                    'correctas' => $correctas,
                    'total_partes' => $totalPartes,
                ], JSON_UNESCAPED_UNICODE),
                'es_correcta' => $esCorrecta ? 1 : 0,
                'puntaje_obtenido' => $puntajeObtenido,
                'manual' => false,
            ];
        }
        if ($tipo === 'seleccionar_posicion_imagen') {
            $posicionesUsuario = collect($respuesta['posiciones'] ?? [])
                ->mapWithKeys(function ($valor, $clave) {
                    return [(int) $clave => (int) $valor];
                });

            $totalPartes = $pregunta->opciones->count();

            $correctas = $pregunta->opciones->filter(function ($opcion) use ($posicionesUsuario) {
                return (int) ($posicionesUsuario->get((int) $opcion->id_ejercicio_opcion) ?? 0) === (int) $opcion->orden;
            })->count();

            $esCorrecta = $totalPartes > 0 && $correctas === $totalPartes;

            $puntajeObtenido = $totalPartes > 0
                ? round(($correctas / $totalPartes) * $puntajePregunta, 2)
                : 0;

            return [
                'respuesta_texto' => null,
                'respuesta_json' => json_encode([
                    'posiciones' => $posicionesUsuario->all(),
                    'correctas' => $correctas,
                    'total_partes' => $totalPartes,
                ], JSON_UNESCAPED_UNICODE),
                'es_correcta' => $esCorrecta ? 1 : 0,
                'puntaje_obtenido' => $puntajeObtenido,
                'manual' => false,
            ];
        }

        if ($tipo === 'ordenar') {
            $ordenes = collect($respuesta['ordenes'] ?? [])
                ->mapWithKeys(function ($valor, $clave) {
                    return [(int) $clave => (int) $valor];
                });

            $esperado = $pregunta->opciones
                ->mapWithKeys(function ($opcion) {
                    return [(int) $opcion->id_ejercicio_opcion => (int) $opcion->orden];
                });

            $totalPartes = $esperado->count();

            $correctas = $esperado->filter(function ($ordenCorrecto, $idOpcion) use ($ordenes) {
                return (int) ($ordenes->get($idOpcion) ?? 0) === (int) $ordenCorrecto;
            })->count();

            $esCorrecta = $totalPartes > 0 && $correctas === $totalPartes;

            $puntajeObtenido = $totalPartes > 0
                ? round(($correctas / $totalPartes) * $puntajePregunta, 2)
                : 0;

            return [
                'respuesta_texto' => null,
                'respuesta_json' => json_encode([
                    'ordenes' => $ordenes->all(),
                    'correctas' => $correctas,
                    'total_partes' => $totalPartes,
                ], JSON_UNESCAPED_UNICODE),
                'es_correcta' => $esCorrecta ? 1 : 0,
                'puntaje_obtenido' => $puntajeObtenido,
                'manual' => false,
            ];
        }

        if ($tipo === 'relacionar') {
            $relaciones = collect($respuesta['relaciones'] ?? [])
                ->mapWithKeys(function ($valor, $clave) {
                    return [(int) $clave => (int) $valor];
                });

            $izquierdas = $pregunta->opciones->where('lado', 'izquierda')->values();
            $derechas = $pregunta->opciones->where('lado', 'derecha')->keyBy('id_ejercicio_opcion');

            $totalRelaciones = $izquierdas->count();

            $correctas = $izquierdas->filter(function ($izquierda) use ($relaciones, $derechas) {
                $idDerecha = (int) ($relaciones->get((int) $izquierda->id_ejercicio_opcion) ?? 0);
                $opcionDerecha = $derechas->get($idDerecha);

                return $opcionDerecha
                    && (string) $opcionDerecha->clave_relacion === (string) $izquierda->clave_relacion;
            })->count();

            $esCorrecta = $totalRelaciones > 0 && $correctas === $totalRelaciones;

            $puntajeObtenido = $totalRelaciones > 0
                ? round(($correctas / $totalRelaciones) * $puntajePregunta, 2)
                : 0;

            return [
                'respuesta_texto' => null,
                'respuesta_json' => json_encode([
                    'relaciones' => $relaciones->all(),
                    'correctas' => $correctas,
                    'total_partes' => $totalRelaciones,
                ], JSON_UNESCAPED_UNICODE),
                'es_correcta' => $esCorrecta ? 1 : 0,
                'puntaje_obtenido' => $puntajeObtenido,
                'manual' => false,
            ];
        }

        if ($tipo === 'actividad_visual_identificacion') {
            $texto = trim((string) ($respuesta['texto'] ?? ''));

            return [
                'respuesta_texto' => $texto,
                'respuesta_json' => null,
                'es_correcta' => null,
                'puntaje_obtenido' => 0,
                'manual' => true,
            ];
        }

        $texto = trim((string) ($respuesta['texto'] ?? ''));

        if ($tipo === 'completar' && !$preguntaManual) {
            $respuestaTexto = trim((string) ($respuesta['texto'] ?? ''));

            $respuestasValidas = collect(preg_split('/\r\n|\r|\n|\|/', (string) $pregunta->respuesta_correcta_texto))
                ->map(fn ($valor) => trim((string) $valor))
                ->filter(fn ($valor) => $valor !== '')
                ->values();

            $respuestaNormalizada = $this->normalizarTexto($respuestaTexto);

            $esCorrecta = $respuestasValidas->contains(function ($respuestaValida) use ($respuestaNormalizada) {
                return $respuestaNormalizada === $this->normalizarTexto($respuestaValida);
            });

            return [
                'respuesta_texto' => $respuestaTexto,
                'respuesta_json' => json_encode([
                    'respuestas_validas' => $respuestasValidas->all(),
                ], JSON_UNESCAPED_UNICODE),
                'es_correcta' => $esCorrecta ? 1 : 0,
                'puntaje_obtenido' => $esCorrecta ? (float) $pregunta->puntaje : 0,
                'manual' => false,
            ];
        }

        return [
            'respuesta_texto' => $texto,
            'respuesta_json' => null,
            'es_correcta' => null,
            'puntaje_obtenido' => 0,
            'manual' => true,
        ];
    }

    private function normalizarTexto(string $texto): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $texto)));
    }

    private function actualizarAvanceModuloDesdeEjercicios(EmpleadoCapacitacion $miCapacitacion, int $idCapacitacionModulo): void
    {
        app(AvanceModuloContenidoService::class)->sincronizar(
            $miCapacitacion,
            $idCapacitacionModulo
        );
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

    private function intentoEjercicioCuentaComoCompletado(EjercicioIntento $intento): bool
    {
        return in_array($intento->estado, ['finalizado', 'revisado'], true)
            && (int) ($intento->aprobado ?? 0) === 1;
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