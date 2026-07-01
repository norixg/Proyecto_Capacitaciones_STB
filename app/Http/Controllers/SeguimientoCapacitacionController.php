<?php

namespace App\Http\Controllers;

use App\Models\Capacitacion;
use App\Models\Departamento;
use App\Models\EmpleadoCapacitacion;
use App\Models\EvaluacionIntento;
use App\Models\PuestoTrabajoMatriz;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\EjercicioIntento;
use App\Models\EmpleadoModuloAvance;
use Illuminate\Support\Facades\DB;
use App\Services\ResumenCapacitacionEmpleadoService;
use App\Models\Empleado;
use App\Models\HistorialCapacitacionEmpleado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Instructor;
use App\Services\AvanceModuloContenidoService;

class SeguimientoCapacitacionController extends Controller
{
    private const ESTADOS = [
        'pendiente',
        'en_proceso',
        'aprobada',
        'reprobada',
        'vencida',
        'cancelada',
    ];

    private function usuarioAutenticado(): ?User
    {
        $usuario = Auth::user();

        return $usuario instanceof User ? $usuario : null;
    }

    private function usuarioEsAdmin(): bool
    {
        return $this->usuarioAutenticado()?->esAdminSistema() === true;
    }

    private function instructorActual(): ?Instructor
    {
        if ($this->usuarioEsAdmin()) {
            return null;
        }

        return $this->usuarioAutenticado()?->instructorInternoActual();
    }

    private function consultaSeguimientoAutorizada()
    {
        $query = EmpleadoCapacitacion::query();

        if ($this->usuarioEsAdmin()) {
            return $query;
        }

        $instructor = $this->instructorActual();

        if (!$instructor) {
            abort(403, 'Tu usuario instructor debe estar vinculado a un empleado interno y a un registro de instructor activo.');
        }

        $idUsuario = Auth::id();

        return $query->whereHas('capacitacion', function ($capacitacionQuery) use ($instructor, $idUsuario) {
            $capacitacionQuery->where(function ($subQuery) use ($instructor, $idUsuario) {
                $subQuery->where('id_instructor', $instructor->id_instructor);

                if ($idUsuario) {
                    $subQuery->orWhere('created_by', $idUsuario);
                }
            });
        });
    }

    private function consultaCapacitacionesAutorizadas()
    {
        $query = Capacitacion::query();

        if ($this->usuarioEsAdmin()) {
            return $query;
        }

        $instructor = $this->instructorActual();

        if (!$instructor) {
            abort(403, 'Tu usuario instructor debe estar vinculado a un empleado interno y a un registro de instructor activo.');
        }

        $idUsuario = Auth::id();

        return $query->where(function ($subQuery) use ($instructor, $idUsuario) {
            $subQuery->where('id_instructor', $instructor->id_instructor);

            if ($idUsuario) {
                $subQuery->orWhere('created_by', $idUsuario);
            }
        });
    }

    private function asegurarAccesoSeguimiento(EmpleadoCapacitacion $seguimiento): void
    {
        if ($this->usuarioEsAdmin()) {
            return;
        }

        $instructor = $this->instructorActual();

        if (!$instructor) {
            abort(403, 'Tu usuario instructor debe estar vinculado a un empleado interno y a un registro de instructor activo.');
        }

        $seguimiento->loadMissing('capacitacion');

        $idUsuario = Auth::id();

        $perteneceAlInstructor = (int) $seguimiento->capacitacion?->id_instructor === (int) $instructor->id_instructor;
        $fueCreadaPorInstructor = $idUsuario && (int) $seguimiento->capacitacion?->created_by === (int) $idUsuario;

        if (!$perteneceAlInstructor && !$fueCreadaPorInstructor) {
            abort(403, 'Solo puedes ver seguimiento de tus capacitaciones como instructor.');
        }
    }

    private function asegurarAccesoExpedienteEmpleado(int $idEmpleado): void
    {
        if ($this->usuarioEsAdmin()) {
            return;
        }

        $tieneCapacitacionesAutorizadas = $this->consultaSeguimientoAutorizada()
            ->where('id_empleado', $idEmpleado)
            ->exists();

        if (!$tieneCapacitacionesAutorizadas) {
            abort(403, 'Solo puedes ver el expediente de empleados asignados a tus capacitaciones como instructor.');
        }
    }

    private function marcarAsignacionesSinIngresoVencidasPorFechaLimite(): void
    {
        $hoy = Carbon::today();

        $this->consultaSeguimientoAutorizada()
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->whereNotNull('fecha_limite')
            ->whereDate('fecha_limite', '<', $hoy)
            ->whereNull('fecha_inicio')
            ->where(function ($query) {
                $query->whereNull('progreso')
                    ->orWhere('progreso', '<=', 0);
            })
            ->doesntHave('modulosAvance')
            ->doesntHave('intentosEvaluacion')
            ->doesntHave('intentosEjercicio')
            ->update([
                'estado' => 'vencida',
                'aprobado' => 0,
                'fecha_finalizacion' => null,
                'updated_at' => now()->format('Ymd H:i:s'),
            ]);
    }

    private function sincronizarSeguimientoAutorizado(): void
    {
        $this->consultaSeguimientoAutorizada()
            ->whereNotIn('estado', ['cancelada'])
            ->orderBy('id_empleado_capacitacion')
            ->chunkById(100, function ($asignaciones) {
                foreach ($asignaciones as $asignacion) {
                    app(ResumenCapacitacionEmpleadoService::class)->recalcular($asignacion);
                }
            }, 'id_empleado_capacitacion');
    }

    private function aplicarFiltros(Request $request)
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $estado = $request->query('estado');
        $seguimiento = $request->query('seguimiento');
        $idCapacitacion = $request->query('id_capacitacion');
        $idDepartamento = $request->query('id_departamento');
        $idPuestoTrabajoMatriz = $request->query('id_puesto_trabajo_matriz');
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');
        $vencimiento = $request->query('vencimiento');

        $query = $this->consultaSeguimientoAutorizada();

        if ($buscar !== '') {
            $query->where(function ($subQuery) use ($buscar) {
                $subQuery->whereHas('empleado', function ($empleadoQuery) use ($buscar) {
                    $empleadoQuery->where('nombre_completo', 'like', '%' . $buscar . '%')
                        ->orWhere('codigo_empleado', 'like', '%' . $buscar . '%')
                        ->orWhere('identidad', 'like', '%' . $buscar . '%')
                        ->orWhere('correo', 'like', '%' . $buscar . '%');
                })->orWhereHas('capacitacion', function ($capacitacionQuery) use ($buscar) {
                    $capacitacionQuery->where('capacitacion', 'like', '%' . $buscar . '%')
                        ->orWhere('codigo', 'like', '%' . $buscar . '%');
                });
            });
        }

        if (in_array($estado, self::ESTADOS, true)) {
            $query->where('estado', $estado);
        }

        if ($seguimiento === 'con_avance') {
            $query->where(function ($subQuery) {
                $subQuery->where('progreso', '>', 0)
                    ->orWhereNotNull('fecha_inicio')
                    ->orWhereHas('modulosAvance')
                    ->orWhereHas('intentosEvaluacion');
            });
        }

        if ($seguimiento === 'sin_avance') {
            $query->where('progreso', '<=', 0)
                ->whereNull('fecha_inicio')
                ->doesntHave('modulosAvance')
                ->doesntHave('intentosEvaluacion');
        }

        if (!empty($idCapacitacion)) {
            $query->where('id_capacitacion', $idCapacitacion);
        }

        if (!empty($idDepartamento)) {
            $query->whereHas('empleado.puestoTrabajo', function ($puestoQuery) use ($idDepartamento) {
                $puestoQuery->where('id_departamento', $idDepartamento);
            });
        }

        if (!empty($idPuestoTrabajoMatriz)) {
            $query->whereHas('empleado', function ($empleadoQuery) use ($idPuestoTrabajoMatriz) {
                $empleadoQuery->where('id_puesto_trabajo_matriz', $idPuestoTrabajoMatriz);
            });
        }

        if (!empty($fechaDesde)) {
            $query->whereDate('fecha_asignacion', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('fecha_asignacion', '<=', $fechaHasta);
        }

        $hoy = Carbon::today();
        $limiteProximo = Carbon::today()->addDays(30);

        if ($vencimiento === 'vencidas') {
            $query->where(function ($subQuery) use ($hoy) {
                $subQuery->where('estado', 'vencida')
                    ->orWhere(function ($fechaQuery) use ($hoy) {
                        $fechaQuery->whereNotNull('fecha_vencimiento')
                            ->whereDate('fecha_vencimiento', '<', $hoy)
                            ->whereNotIn('estado', ['aprobada', 'cancelada']);
                    });
            });
        }

            if ($vencimiento === 'por_vencer') {
                $query->whereNotNull('fecha_vencimiento')
                    ->whereDate('fecha_vencimiento', '>=', $hoy)
                    ->whereDate('fecha_vencimiento', '<=', $limiteProximo)
                    ->whereNotIn('estado', ['aprobada', 'cancelada', 'vencida']);
            }

            if ($vencimiento === 'sin_fecha') {
                $query->whereNull('fecha_vencimiento');
            }

            return $query;
    }

    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $estado = $request->query('estado');
        $seguimiento = $request->query('seguimiento');
        $idCapacitacion = $request->query('id_capacitacion');
        $idDepartamento = $request->query('id_departamento');
        $idPuestoTrabajoMatriz = $request->query('id_puesto_trabajo_matriz');
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');
        $vencimiento = $request->query('vencimiento');

        $this->sincronizarSeguimientoAutorizado();

        $baseQuery = $this->aplicarFiltros($request);

        $totalRegistros = (clone $baseQuery)->count();
        $totalPendientes = (clone $baseQuery)->where('estado', 'pendiente')->count();
        $totalEnProceso = (clone $baseQuery)->where('estado', 'en_proceso')->count();
        $totalAprobadas = (clone $baseQuery)->where('estado', 'aprobada')->count();
        $totalReprobadas = (clone $baseQuery)->whereIn('estado', ['reprobada', 'vencida'])->count();

        $hoy = Carbon::today();
        $limiteProximo = Carbon::today()->addDays(30);

        $totalVencidas = (clone $baseQuery)
            ->where(function ($subQuery) use ($hoy) {
                $subQuery->where('estado', 'vencida')
                    ->orWhere(function ($fechaQuery) use ($hoy) {
                        $fechaQuery->whereNotNull('fecha_vencimiento')
                            ->whereDate('fecha_vencimiento', '<', $hoy)
                            ->whereNotIn('estado', ['aprobada', 'cancelada']);
                    });
            })
            ->count();

        $totalPorVencer = (clone $baseQuery)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '>=', $hoy)
            ->whereDate('fecha_vencimiento', '<=', $limiteProximo)
            ->whereNotIn('estado', ['aprobada', 'cancelada', 'vencida'])
            ->count();

        $totalPendientesRevision = (clone $baseQuery)
            ->whereHas('intentosEjercicio', function ($query) {
                $query->where('estado', 'pendiente_revision');
            })
            ->count();

        $totalConAvance = (clone $baseQuery)->where(function ($subQuery) {
            $subQuery->where('progreso', '>', 0)
                ->orWhereNotNull('fecha_inicio')
                ->orWhereHas('modulosAvance')
                ->orWhereHas('intentosEvaluacion');
        })->count();
        $totalSinAvance = (clone $baseQuery)->where('progreso', '<=', 0)
            ->whereNull('fecha_inicio')
            ->doesntHave('modulosAvance')
            ->doesntHave('intentosEvaluacion')
            ->count();

        $seguimientos = $baseQuery
        ->with([
            'empleado.puestoTrabajo.departamento',
            'capacitacion',
            'capacitacion.capacitacionModulos' => function ($query) {
                $query->where('estado', 1)->orderBy('orden');
            },
            'capacitacion.capacitacionModulos.ejercicios' => function ($query) {
                $query->where('estado', 1)->orderBy('orden');
            },
            'modulosAvance',
            'intentosEvaluacion',
            'intentosEjercicio' => function ($query) {
                $query->with('ejercicio.modulo')->orderByDesc('fecha_fin')->orderByDesc('numero_intento');
            },
        ])
        ->orderByDesc('id_empleado_capacitacion')
        ->paginate(15)
        ->withQueryString();

        $seguimientos->getCollection()->transform(function ($seguimientoItem) {
        $modulos = $seguimientoItem->capacitacion?->capacitacionModulos ?? collect();

        $ejercicios = $modulos->flatMap(function ($modulo) {
            return $modulo->ejercicios;
        })->unique('id_ejercicio')->values();

        $idsEjercicios = $ejercicios->pluck('id_ejercicio');

        $intentosEjercicio = $seguimientoItem->intentosEjercicio
            ->whereIn('id_ejercicio', $idsEjercicios)
            ->values();

        $idsEjerciciosCompletados = $intentosEjercicio
            ->filter(function ($intento) {
                return $this->intentoEjercicioCuentaComoCompletado($intento);
            })
            ->pluck('id_ejercicio')
            ->unique();

        $ultimoIntentoEjercicio = $intentosEjercicio->sortByDesc(function ($intento) {
            return $intento->fecha_fin ?? $intento->fecha_inicio ?? null;
        })->first();

        $fechas = collect([
            $seguimientoItem->fecha_inicio,
            $seguimientoItem->modulosAvance->max('fecha_ultima_actividad'),
            $seguimientoItem->intentosEvaluacion->max('fecha_fin'),
            $ultimoIntentoEjercicio?->fecha_fin,
            $ultimoIntentoEjercicio?->fecha_inicio,
        ])->filter();

        $seguimientoItem->ultima_actividad_admin = $fechas->isNotEmpty()
            ? $fechas->map(function ($fecha) {
                return $fecha instanceof Carbon ? $fecha : Carbon::parse($fecha);
            })->sortDesc()->first()
            : null;

        $seguimientoItem->ha_continuado_admin =
            (float) $seguimientoItem->progreso > 0 ||
            !is_null($seguimientoItem->fecha_inicio) ||
            $seguimientoItem->modulosAvance->isNotEmpty() ||
            $seguimientoItem->intentosEvaluacion->isNotEmpty() ||
            $intentosEjercicio->isNotEmpty();

        $seguimientoItem->resumen_ejercicios = [
            'total' => $ejercicios->count(),
            'completados' => $idsEjerciciosCompletados->count(),
            'pendientes' => max($ejercicios->count() - $idsEjerciciosCompletados->count(), 0),
            'pendientes_revision' => $intentosEjercicio->where('estado', 'pendiente_revision')->count(),
            'intentos_totales' => $intentosEjercicio->count(),
            'ultima_actividad' => $ultimoIntentoEjercicio?->fecha_fin ?? $ultimoIntentoEjercicio?->fecha_inicio ?? null,
        ];

        $fechaVencimiento = $seguimientoItem->fecha_vencimiento
            ? Carbon::parse($seguimientoItem->fecha_vencimiento)
            : null;

        $seguimientoItem->dias_vencimiento_admin = $fechaVencimiento
            ? Carbon::today()->diffInDays($fechaVencimiento, false)
            : null;

        $seguimientoItem->vencimiento_visual_admin = 'sin_fecha';

        if ($fechaVencimiento && !in_array($seguimientoItem->estado, ['aprobada', 'cancelada'], true)) {
            if ($fechaVencimiento->lt(Carbon::today())) {
                $seguimientoItem->vencimiento_visual_admin = 'vencida';
            } elseif ($fechaVencimiento->between(Carbon::today(), Carbon::today()->addDays(30), true)) {
                $seguimientoItem->vencimiento_visual_admin = 'por_vencer';
            } else {
                $seguimientoItem->vencimiento_visual_admin = 'vigente';
            }
        }

        $seguimientoItem->estado_visual_admin = $seguimientoItem->estado;

        if (($seguimientoItem->resumen_ejercicios['pendientes_revision'] ?? 0) > 0) {
            $seguimientoItem->estado_visual_admin = 'pendiente_revision';
        }

        return $seguimientoItem;
    });

        $esInstructorSeguimiento = !$this->usuarioEsAdmin();

        $capacitaciones = $this->consultaCapacitacionesAutorizadas()
            ->orderBy('capacitacion')
            ->get();

        if ($this->usuarioEsAdmin()) {
            $departamentos = Departamento::orderBy('departamento')->get();

            $puestos = PuestoTrabajoMatriz::with('departamento')
                ->orderBy('puesto_trabajo_matriz')
                ->get();
        } else {
            $idsEmpleadosAutorizados = (clone $this->consultaSeguimientoAutorizada())
                ->pluck('id_empleado')
                ->filter()
                ->unique()
                ->values();

            $idsPuestosAutorizados = Empleado::whereIn('id_empleado', $idsEmpleadosAutorizados)
                ->pluck('id_puesto_trabajo_matriz')
                ->filter()
                ->unique()
                ->values();

            $puestos = PuestoTrabajoMatriz::with('departamento')
                ->whereIn('id_puesto_trabajo_matriz', $idsPuestosAutorizados)
                ->orderBy('puesto_trabajo_matriz')
                ->get();

            $idsDepartamentosAutorizados = $puestos
                ->pluck('id_departamento')
                ->filter()
                ->unique()
                ->values();

            $departamentos = Departamento::whereIn('id_departamento', $idsDepartamentosAutorizados)
                ->orderBy('departamento')
                ->get();
        }

        $estados = self::ESTADOS;

        return view('seguimiento_capacitaciones.index', compact(
            'seguimientos',
            'capacitaciones',
            'departamentos',
            'puestos',
            'estados',
            'esInstructorSeguimiento',
            'buscar',
            'estado',
            'seguimiento',
            'idCapacitacion',
            'idDepartamento',
            'idPuestoTrabajoMatriz',
            'fechaDesde',
            'fechaHasta',
            'totalRegistros',
            'totalPendientes',
            'totalEnProceso',
            'totalAprobadas',
            'totalReprobadas',
            'totalVencidas',
            'totalPorVencer',
            'totalPendientesRevision',
            'totalConAvance',
            'totalSinAvance',
            'vencimiento'
        ));
    }

    public function show(int $id)
    {
        $seguimiento = EmpleadoCapacitacion::with([
            'empleado.puestoTrabajo.departamento',
            'capacitacion.capacitacionModulos' => function ($query) {
                $query->where('estado', 1)->orderBy('orden');
            },
            'capacitacion.capacitacionModulos.evaluaciones' => function ($query) {
                $query->where('activa', 1)->orderByDesc('id_evaluacion');
            },
            'capacitacion.capacitacionModulos.ejercicios' => function ($query) {
                $query->where('estado', 1)->orderBy('orden');
            },
            'modulosAvance.capacitacionModulo',
            'intentosEvaluacion' => function ($query) {
                $query->orderByDesc('id_evaluacion_intento');
            },
            'intentosEvaluacion.evaluacion.capacitacionModulo',
            'intentosEjercicio' => function ($query) {
                $query->with('ejercicio.modulo')->orderByDesc('numero_intento');
            },
            'historial.usuario',
        ])->findOrFail($id);

        $this->asegurarAccesoSeguimiento($seguimiento);

        app(ResumenCapacitacionEmpleadoService::class)->recalcular($seguimiento);

        $seguimiento->refresh();

        $modulos = $seguimiento->capacitacion?->capacitacionModulos ?? collect();
        $avancesPorModulo = $seguimiento->modulosAvance->keyBy('id_capacitacion_modulo');
        $intentosPorModulo = $seguimiento->intentosEvaluacion->groupBy(function ($intento) {
            return optional($intento->evaluacion)->id_capacitacion_modulo;
        });
        $intentosEjercicioPorEjercicio = $seguimiento->intentosEjercicio->groupBy('id_ejercicio');

        $avancesContenido = collect();

        if (Schema::hasTable('empleado_contenido_avance')) {
            $avancesContenido = DB::table('empleado_contenido_avance')
                ->where('id_empleado_capacitacion', $seguimiento->id_empleado_capacitacion)
                ->orderByDesc('fecha_ultima_actividad')
                ->get();
        }

        $avancesContenidoPorModulo = $avancesContenido->groupBy('id_capacitacion_modulo');

        $detalleModulos = $modulos->map(function ($modulo) use ($avancesPorModulo, $intentosPorModulo, $intentosEjercicioPorEjercicio) {
            $avance = $avancesPorModulo->get($modulo->id_capacitacion_modulo);
            $intentosModulo = $intentosPorModulo->get($modulo->id_capacitacion_modulo, collect());
            $evaluacion = $modulo->evaluaciones->first();
            $ultimoIntento = $intentosModulo->first();

            $detalleEjercicios = ($modulo->ejercicios ?? collect())->map(function ($ejercicio) use ($intentosEjercicioPorEjercicio) {
                $intentos = $intentosEjercicioPorEjercicio->get($ejercicio->id_ejercicio, collect())->values();
                $ultimoIntentoEjercicio = $intentos->sortByDesc('numero_intento')->first();

                return [
                    'ejercicio' => $ejercicio,
                    'intentos' => $intentos,
                    'ultimo_intento' => $ultimoIntentoEjercicio,
                    'intentos_realizados' => $intentos->count(),
                    'completado' => $intentos->contains(function ($intento) {
                        return $this->intentoEjercicioCuentaComoCompletado($intento);
                    }),
                    'pendiente_revision' => $intentos->where('estado', 'pendiente_revision')->isNotEmpty(),
                ];
            });

            $totalEjercicios = $detalleEjercicios->count();
            $ejerciciosCompletados = $detalleEjercicios->where('completado', true)->count();
            $pendientesRevisionEjercicio = $detalleEjercicios->where('pendiente_revision', true)->count();

            $intentosMaximos = $evaluacion?->intentos_maximos ? (int) $evaluacion->intentos_maximos : null;
            $evaluacionAprobada = $intentosModulo->where('aprobado', 1)->isNotEmpty();
            $evaluacionCerradaPorMax = $evaluacion
                && !is_null($intentosMaximos)
                && $intentosModulo->count() >= $intentosMaximos
                && !$evaluacionAprobada;

            if ((int) $modulo->requiere_evaluacion === 1 && $evaluacion) {
                $parteEjercicios = $totalEjercicios > 0
                    ? round(($ejerciciosCompletados / $totalEjercicios) * 50, 2)
                    : 50;

                $parteEvaluacion = 0;

                if ($evaluacionAprobada || $evaluacionCerradaPorMax) {
                    $parteEvaluacion = 50;
                } elseif ($intentosModulo->isNotEmpty()) {
                    $parteEvaluacion = 25;
                }

                $progresoReal = round(min($parteEjercicios + $parteEvaluacion, 100), 2);

                if ($evaluacionAprobada) {
                    $estadoReal = 'completado';
                } elseif ($evaluacionCerradaPorMax) {
                    $estadoReal = 'reprobado';
                } elseif ($pendientesRevisionEjercicio > 0) {
                    $estadoReal = 'pendiente_revision';
                } elseif ($avance || $intentosModulo->isNotEmpty() || $detalleEjercicios->sum('intentos_realizados') > 0) {
                    $estadoReal = 'en_proceso';
                } else {
                    $estadoReal = 'pendiente';
                }
            } elseif ($totalEjercicios > 0) {
                $progresoReal = round(($ejerciciosCompletados / $totalEjercicios) * 100, 2);

                if ($ejerciciosCompletados >= $totalEjercicios) {
                    $estadoReal = 'completado';
                } elseif ($pendientesRevisionEjercicio > 0) {
                    $estadoReal = 'en_proceso';
                } elseif ($avance || $detalleEjercicios->sum('intentos_realizados') > 0) {
                    $estadoReal = 'en_proceso';
                } else {
                    $estadoReal = 'pendiente';
                }
            } else {
                $progresoReal = round((float) ($avance->progreso ?? 0), 2);
                $estadoReal = $avance->estado ?? 'pendiente';
            }

            return [
                'modulo' => $modulo,
                'avance' => $avance,
                'evaluacion' => $evaluacion,
                'intentos' => $intentosModulo,
                'ultimo_intento' => $ultimoIntento,
                'intentos_realizados' => $intentosModulo->count(),
                'aprobado_evaluacion' => $intentosModulo->where('aprobado', 1)->isNotEmpty(),
                'ejercicios' => $detalleEjercicios,
                'progreso_real' => $progresoReal,
                'estado_real' => $estadoReal,
            ];
        });

        $progresoGlobalReal = $detalleModulos->count() > 0
            ? round($detalleModulos->avg('progreso_real'), 2)
            : 0;

        $estadoGlobalReal = $seguimiento->estado ?? 'pendiente';
        $tienePendientesRevision = $detalleModulos->contains(fn($detalle) => $detalle['estado_real'] === 'pendiente_revision');

        if ($tienePendientesRevision) {
            $estadoGlobalReal = 'pendiente_revision';
        } elseif (in_array($seguimiento->estado, ['vencida', 'reprobada', 'aprobada', 'cancelada'], true)) {
            $estadoGlobalReal = $seguimiento->estado;
        } elseif ($detalleModulos->contains(fn($detalle) => $detalle['estado_real'] === 'reprobado')) {
            $estadoGlobalReal = 'reprobada';
        } elseif ($detalleModulos->count() > 0 && $detalleModulos->every(fn($detalle) => $detalle['estado_real'] === 'completado')) {
            $estadoGlobalReal = 'aprobada';
        } elseif ($detalleModulos->contains(fn($detalle) => in_array($detalle['estado_real'], ['en_proceso', 'completado'], true) || (float) $detalle['progreso_real'] > 0)) {
            $estadoGlobalReal = 'en_proceso';
        }

        $fechas = collect([
            $seguimiento->fecha_inicio,
            $seguimiento->modulosAvance->max('fecha_ultima_actividad'),
            $seguimiento->intentosEvaluacion->max('fecha_fin'),
            $seguimiento->intentosEjercicio->max('fecha_fin'),
            $seguimiento->intentosEjercicio->max('fecha_inicio'),
            $avancesContenido->max('fecha_ultima_actividad'),
        ])->filter();

        $ultimaActividad = $fechas->isNotEmpty()
            ? $fechas->map(function ($fecha) {
                return $fecha instanceof Carbon ? $fecha : Carbon::parse($fecha);
            })->sortDesc()->first()
            : null;

        $haContinuado =
            (float) $progresoGlobalReal > 0 ||
            !is_null($seguimiento->fecha_inicio) ||
            $seguimiento->modulosAvance->isNotEmpty() ||
            $seguimiento->intentosEvaluacion->isNotEmpty() ||
            $seguimiento->intentosEjercicio->isNotEmpty() ||
            $avancesContenido->isNotEmpty();

        return view('seguimiento_capacitaciones.show', compact(
            'seguimiento',
            'detalleModulos',
            'ultimaActividad',
            'haContinuado',
            'progresoGlobalReal',
            'estadoGlobalReal',
            'tienePendientesRevision',
            'avancesContenido',
            'avancesContenidoPorModulo'
        ));
    }

    public function expedienteEmpleado(int $id_empleado)
    {
        $this->asegurarAccesoExpedienteEmpleado($id_empleado);

        $empleado = Empleado::with('puestoTrabajo.departamento')
            ->findOrFail($id_empleado);

        $capacitaciones = $this->consultaSeguimientoAutorizada()
            ->with([
                'capacitacion.capacitacionModulos' => function ($query) {
                    $query->where('estado', 1)->orderBy('orden');
                },
                'modulosAvance.capacitacionModulo',
                'intentosEvaluacion.evaluacion.capacitacionModulo',
                'intentosEjercicio.ejercicio.modulo',
                'historial.usuario',
            ])
            ->where('id_empleado', $empleado->id_empleado)
            ->orderByDesc('id_empleado_capacitacion')
            ->get();

        if (!$this->usuarioEsAdmin() && $capacitaciones->isEmpty()) {
            abort(403, 'Solo puedes ver el expediente de empleados asignados a tus capacitaciones como instructor.');
        }

        $hoy = Carbon::today();
        $limiteProximo = Carbon::today()->addDays(30);

        $intentosEvaluacion = $capacitaciones->flatMap(function ($item) {
            return $item->intentosEvaluacion;
        });

        $intentosEjercicio = $capacitaciones->flatMap(function ($item) {
            return $item->intentosEjercicio;
        });

        $totales = [
            'asignadas' => $capacitaciones->count(),
            'pendientes' => $capacitaciones->where('estado', 'pendiente')->count(),
            'en_proceso' => $capacitaciones->where('estado', 'en_proceso')->count(),
            'aprobadas' => $capacitaciones->where('estado', 'aprobada')->count(),
            'reprobadas' => $capacitaciones->whereIn('estado', ['reprobada', 'vencida'])->count(),
            'vencidas' => $capacitaciones->filter(function ($item) use ($hoy) {
                if ($item->estado === 'vencida') {
                    return true;
                }

                if (!$item->fecha_vencimiento) {
                    return false;
                }

                return Carbon::parse($item->fecha_vencimiento)->lt($hoy);
            })->count(),
            'por_vencer' => $capacitaciones->filter(function ($item) use ($hoy, $limiteProximo) {
                if (!$item->fecha_vencimiento) {
                    return false;
                }

                $fechaVencimiento = Carbon::parse($item->fecha_vencimiento);

                return $fechaVencimiento->between($hoy, $limiteProximo, true);
            })->count(),
            'intentos_evaluacion' => $intentosEvaluacion->count(),
            'intentos_ejercicio' => $intentosEjercicio->count(),
            'pendientes_revision' => $intentosEjercicio->where('estado', 'pendiente_revision')->count(),
            'nota_promedio' => $capacitaciones->whereNotNull('nota_final')->avg('nota_final'),
        ];

        return view('seguimiento_capacitaciones.expediente_empleado', compact(
            'empleado',
            'capacitaciones',
            'totales'
        ));
    }

    public function ejercicioIntento(int $id, int $id_intento)
    {
        $seguimiento = EmpleadoCapacitacion::with([
            'empleado.puestoTrabajo.departamento',
            'capacitacion',
        ])->findOrFail($id);

        $this->asegurarAccesoSeguimiento($seguimiento);

        $intento = EjercicioIntento::with([
            'ejercicio.modulo',
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
            ->where('id_empleado_capacitacion', $seguimiento->id_empleado_capacitacion)
            ->firstOrFail();

        $respuestas = $intento->respuestas
            ->sortBy(function ($respuesta) {
                return optional($respuesta->pregunta)->orden ?? 9999;
            })
            ->values();

        return view('seguimiento_capacitaciones.ejercicio_intento', compact(
            'seguimiento',
            'intento',
            'respuestas'
        ));
    }

    public function revisarEjercicioIntento(Request $request, int $id, int $id_intento)
    {
        $seguimiento = EmpleadoCapacitacion::with([
            'capacitacion.capacitacionModulos' => function ($query) {
                $query->where('estado', 1)->orderBy('orden');
            },
            'capacitacion.capacitacionModulos.ejercicios' => function ($query) {
                $query->where('estado', 1)->orderBy('orden');
            },
            'modulosAvance',
        ])->findOrFail($id);

        $this->asegurarAccesoSeguimiento($seguimiento);

        $intento = EjercicioIntento::with([
            'ejercicio.modulo',
            'ejercicio.preguntas',
            'respuestas.pregunta',
        ])
            ->where('id_ejercicio_intento', $id_intento)
            ->where('id_empleado_capacitacion', $seguimiento->id_empleado_capacitacion)
            ->firstOrFail();

        $request->validate([
            'respuestas' => ['required', 'array'],
            'respuestas.*.puntaje_obtenido' => ['required', 'numeric', 'min:0'],
            'respuestas.*.comentario_revision' => ['nullable', 'string', 'max:2000'],
            'comentario_revision' => ['nullable', 'string', 'max:2000'],
        ]);

        $estadoAnteriorSeguimiento = $seguimiento->estado;

        DB::beginTransaction();

        try {
            $puntajeTotal = 0;
            $puntajeObtenido = 0;

            foreach ($intento->respuestas as $respuesta) {
                $pregunta = $respuesta->pregunta;

                if (!$pregunta) {
                    continue;
                }

                $puntajePregunta = (float) ($pregunta->puntaje ?? 0);
                $puntajeTotal += $puntajePregunta;

                $datosRespuesta = $request->input('respuestas.' . $respuesta->id_ejercicio_intento_respuesta, []);

                $puntajeIngresado = (float) ($datosRespuesta['puntaje_obtenido'] ?? 0);

                if ($puntajeIngresado > $puntajePregunta) {
                    return back()->withErrors([
                        'ejercicio' => 'El puntaje obtenido no puede ser mayor al puntaje máximo de la pregunta.'
                    ])->withInput();
                }

                $comentarioPregunta = trim((string) ($datosRespuesta['comentario_revision'] ?? ''));

                $respuesta->update([
                    'puntaje_obtenido' => $puntajeIngresado,
                    'es_correcta' => $puntajePregunta > 0 && $puntajeIngresado >= $puntajePregunta ? 1 : 0,
                    'comentario_revision' => $comentarioPregunta ?: null,
                ]);

                $puntajeObtenido += $puntajeIngresado;
            }

            $porcentaje = $puntajeTotal > 0
                ? round(($puntajeObtenido / $puntajeTotal) * 100, 2)
                : 0;

            $porcentajeAprobacion = !is_null($intento->ejercicio?->porcentaje_aprobacion)
                ? (float) $intento->ejercicio->porcentaje_aprobacion
                : 70;

            $aprobado = $porcentaje >= $porcentajeAprobacion ? 1 : 0;

            $comentarioGeneral = trim((string) $request->comentario_revision) ?: null;

            $intento->update([
                'puntaje_obtenido' => $puntajeObtenido,
                'porcentaje_obtenido' => $porcentaje,
                'aprobado' => $aprobado,
                'estado' => 'revisado',
                'comentario_revision' => $comentarioGeneral,
            ]);

            $this->recalcularEstadoModuloPorEjercicios(
                $seguimiento,
                $intento->ejercicio->id_capacitacion_modulo
            );

            app(ResumenCapacitacionEmpleadoService::class)->recalcular($seguimiento);

            $seguimiento->refresh();

            $this->registrarHistorialCapacitacion(
                $seguimiento,
                $estadoAnteriorSeguimiento,
                $seguimiento->estado,
                'Revisión manual de ejercicio realizada desde seguimiento administrativo. Ejercicio: '
                    . ($intento->ejercicio?->titulo ?? 'Sin título')
                    . '. Resultado: '
                    . number_format((float) ($intento->porcentaje_obtenido ?? 0), 2)
                    . '%.'
            );

            DB::commit();

            return redirect()
                ->route('seguimiento_capacitaciones.ejercicio_intento.show', [
                    'id' => $seguimiento->id_empleado_capacitacion,
                    'id_intento' => $intento->id_ejercicio_intento,
                ])
                ->with('success', 'La calificación del ejercicio fue actualizada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function intento(int $id, int $id_intento)
    {
        $seguimiento = EmpleadoCapacitacion::with([
            'empleado.puestoTrabajo.departamento',
            'capacitacion',
        ])->findOrFail($id);

        $this->asegurarAccesoSeguimiento($seguimiento);

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
                    'opcion',
                ]);
            }
        ])
            ->where('id_evaluacion_intento', $id_intento)
            ->where('id_empleado_capacitacion', $seguimiento->id_empleado_capacitacion)
            ->firstOrFail();

        $respuestas = $intento->respuestas
            ->sortBy(function ($respuesta) {
                return optional($respuesta->pregunta)->orden ?? 9999;
            })
            ->values();

        $totalPreguntas = $respuestas->count();
        $totalCorrectas = $respuestas->where('es_correcta', 1)->count();
        $totalIncorrectas = $totalPreguntas - $totalCorrectas;

        return view('seguimiento_capacitaciones.intento', compact(
            'seguimiento',
            'intento',
            'respuestas',
            'totalPreguntas',
            'totalCorrectas',
            'totalIncorrectas'
        ));
    }

    public function revisarIntentoEvaluacion(Request $request, int $id, int $id_intento)
    {
        $seguimiento = EmpleadoCapacitacion::with([
            'capacitacion.capacitacionModulos' => function ($query) {
                $query->where('estado', 1)->orderBy('orden');
            },
            'modulosAvance',
        ])->findOrFail($id);

        $this->asegurarAccesoSeguimiento($seguimiento);

        $intento = EvaluacionIntento::with([
            'evaluacion.capacitacionModulo',
            'respuestas.pregunta',
        ])
            ->where('id_evaluacion_intento', $id_intento)
            ->where('id_empleado_capacitacion', $seguimiento->id_empleado_capacitacion)
            ->firstOrFail();

        $request->validate([
            'respuestas' => ['required', 'array'],
            'respuestas.*.puntaje_obtenido' => ['required', 'numeric', 'min:0'],
            'respuestas.*.comentario_revision' => ['nullable', 'string', 'max:2000'],
        ]);

        $estadoAnteriorSeguimiento = $seguimiento->estado;

        DB::beginTransaction();

        try {
            $puntajeTotal = 0;
            $puntajeObtenido = 0;

            foreach ($intento->respuestas as $respuesta) {
                $pregunta = $respuesta->pregunta;

                if (!$pregunta) {
                    continue;
                }

                $puntajePregunta = (float) ($pregunta->puntaje ?? 0);
                $puntajeTotal += $puntajePregunta;

                $datosRespuesta = $request->input('respuestas.' . $respuesta->id_evaluacion_intento_respuesta, []);

                $puntajeIngresado = (float) ($datosRespuesta['puntaje_obtenido'] ?? 0);

                if ($puntajeIngresado > $puntajePregunta) {
                    return back()->withErrors([
                        'evaluacion' => 'El puntaje obtenido no puede ser mayor al puntaje máximo de la pregunta.'
                    ])->withInput();
                }

                $comentarioPregunta = trim((string) ($datosRespuesta['comentario_revision'] ?? ''));

                $respuesta->update([
                    'puntaje_obtenido' => $puntajeIngresado,
                    'es_correcta' => $puntajePregunta > 0 && $puntajeIngresado >= $puntajePregunta ? 1 : 0,
                    'comentario_revision' => $comentarioPregunta ?: null,
                ]);

                $puntajeObtenido += $puntajeIngresado;
            }

            $nota = $puntajeTotal > 0
                ? round(($puntajeObtenido / $puntajeTotal) * 100, 2)
                : 0;

            $porcentajeAprobacion = !is_null($intento->evaluacion?->porcentaje_aprobacion)
                ? (float) $intento->evaluacion->porcentaje_aprobacion
                : 70;

            $aprobado = $nota >= $porcentajeAprobacion ? 1 : 0;

            $intento->update([
                'nota' => $nota,
                'aprobado' => $aprobado,
                'estado' => 'revisado',
            ]);

            $avanceModulo = EmpleadoModuloAvance::firstOrNew([
                'id_empleado_capacitacion' => $seguimiento->id_empleado_capacitacion,
                'id_capacitacion_modulo' => $intento->evaluacion->id_capacitacion_modulo,
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
            } else {
                $avanceModulo->estado = 'en_proceso';
                $avanceModulo->progreso = max((float) ($avanceModulo->progreso ?? 0), 50);
                $avanceModulo->fecha_finalizacion = null;
            }

            $avanceModulo->save();

            app(AvanceModuloContenidoService::class)->sincronizar(
                $seguimiento,
                (int) $intento->evaluacion->id_capacitacion_modulo,
                $avanceModulo
            );

            app(ResumenCapacitacionEmpleadoService::class)->recalcular($seguimiento);

            $seguimiento->refresh();

            $this->registrarHistorialCapacitacion(
                $seguimiento,
                $estadoAnteriorSeguimiento,
                $seguimiento->estado,
                'Revisión manual de evaluación realizada desde seguimiento administrativo. Evaluación: '
                    . ($intento->evaluacion?->titulo ?? 'Sin título')
                    . '. Resultado: '
                    . number_format((float) ($intento->nota ?? 0), 2)
                    . '%.'
            );

            DB::commit();

            return redirect()
                ->route('seguimiento_capacitaciones.intentos.show', [
                    'id' => $seguimiento->id_empleado_capacitacion,
                    'id_intento' => $intento->id_evaluacion_intento,
                ])
                ->with('success', 'La calificación de la evaluación fue actualizada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function registrarHistorialCapacitacion(
        EmpleadoCapacitacion $seguimiento,
        ?string $estadoAnterior,
        ?string $estadoNuevo,
        string $observacion
    ): void {
        HistorialCapacitacionEmpleado::create([
            'id_empleado_capacitacion' => $seguimiento->id_empleado_capacitacion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo ?? $seguimiento->estado ?? 'en_proceso',
            'observacion' => $observacion,
            'fecha_movimiento' => now()->format('Ymd H:i:s'),
            'id_user' => Auth::check() ? Auth::id() : null,
        ]);
    }

    private function recalcularEstadoModuloPorEjercicios(EmpleadoCapacitacion $seguimiento, int $idCapacitacionModulo): void
    {
        app(AvanceModuloContenidoService::class)->sincronizar(
            $seguimiento,
            $idCapacitacionModulo
        );
    }

    private function intentoEjercicioCuentaComoCompletado(EjercicioIntento $intento): bool
    {
        return $intento->estado === 'finalizado'
            || ($intento->estado === 'revisado' && (int) ($intento->aprobado ?? 0) === 1);
    }

}